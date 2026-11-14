<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use IndianOpinion\AsciiSlugs\Exception\InvalidInputException;
use IndianOpinion\AsciiSlugs\Exception\InvalidOptionException;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\SlugResult;
use IndianOpinion\AsciiSlugs\WordPress\Cli\Command;
use Throwable;

/**
 * The WordPress layer: two core filters, a Site Health section and WP-CLI.
 */
final class Plugin
{
    public const VERSION = '0.1.0';

    /** Priority on `sanitize_title`, before core's sanitiser at 10. */
    public const SANITIZE_PRIORITY = 9;

    /** Priority on `wp_unique_post_slug`. */
    public const UNIQUE_PRIORITY = 20;

    /** Post types the safety net never touches by default (PRD 4.7.2). */
    public const EXCLUDED_POST_TYPES = [
        'revision', 'nav_menu_item', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block',
    ];

    private static ?self $instance = null;

    /** @var callable(SlugOptions): Slugger */
    private $sluggerFactory;

    private Logger $logger;

    private ?Slugger $slugger = null;

    private string $sluggerKey = '';

    private bool $inSafetyNet = false;

    /**
     * @param callable(SlugOptions): Slugger|null $sluggerFactory
     */
    public function __construct(?callable $sluggerFactory = null, ?Logger $logger = null)
    {
        $this->sluggerFactory = $sluggerFactory ?? static fn (SlugOptions $options): Slugger => new Slugger($options);
        $this->logger = $logger ?? new Logger();
    }

    /**
     * Creates the shared instance and attaches it to WordPress. Safe to
     * call twice.
     */
    public static function boot(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->register();
        }

        return self::$instance;
    }

    public static function instance(): ?self
    {
        return self::$instance;
    }

    public function register(): void
    {
        add_filter('sanitize_title', [$this, 'filterSanitizeTitle'], self::SANITIZE_PRIORITY, 3);
        add_filter('wp_unique_post_slug', [$this, 'filterUniquePostSlug'], self::UNIQUE_PRIORITY, 6);
        add_filter('pre_term_slug', [$this, 'filterPreTermSlug'], self::SANITIZE_PRIORITY, 2);
        (new SiteHealth($this))->register();

        if (defined('WP_CLI') && constant('WP_CLI') === true && class_exists('WP_CLI')) {
            \WP_CLI::add_command('ascii-slugs', new Command($this));
        }
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    /**
     * Kill switch: the ASCII_SLUGS_DISABLED constant and `ascii_slugs_enabled`.
     */
    public function isEnabled(): bool
    {
        $default = !(defined('ASCII_SLUGS_DISABLED') && constant('ASCII_SLUGS_DISABLED') === true);
        $value = apply_filters('ascii_slugs_enabled', $default);
        if (!is_bool($value)) {
            $this->logger->notice('ascii_slugs_enabled returned a non-boolean; ignored');

            return $default;
        }

        return $value;
    }

    /**
     * Main path on `sanitize_title` (PRD 4.6).
     */
    public function filterSanitizeTitle(mixed $title, mixed $rawTitle = '', mixed $context = 'display'): mixed
    {
        if ($context !== 'save' || !is_string($title)) {
            return $title;
        }
        $raw = is_string($rawTitle) && $rawTitle !== '' ? $rawTitle : $title;
        if (Encoding::isAscii($raw)) {
            return $title;
        }
        if (!$this->isEnabled()) {
            return $title;
        }

        $brokenUtf8 = preg_match('//u', $raw) !== 1;
        if (!$brokenUtf8 && !Encoding::hasEncodedHighByte(sanitize_title_with_dashes($title, $raw, 'save'))) {
            return $title;
        }

        $handle = apply_filters('ascii_slugs_should_handle', true, $raw, $context);
        if (!is_bool($handle)) {
            $this->logger->notice('ascii_slugs_should_handle returned a non-boolean; ignored');
            $handle = true;
        }
        if (!$handle) {
            return $title;
        }

        if (!$brokenUtf8) {
            try {
                if ($this->slugger()->prepare($raw) === '') {
                    return $title;
                }
            } catch (InvalidInputException) {
                // Handled by makeSlug() below.
            }
        }

        return $this->makeSlug($raw);
    }

    /**
     * Keeps a stored percent-encoded term slug when it comes back decoded.
     *
     * The term edit screen and Quick Edit show a stored slug through the
     * core `editable_slug` filter, which URL-decodes it, and send it back on
     * every save. Core would encode it again to the same bytes, but the main
     * path would transliterate it and move the term's live URL, which has no
     * old-slug redirect. When the encoded form is the stored slug of a term
     * in the same taxonomy, this returns that encoded slug, so the main path
     * sees ASCII and leaves it alone. Posts need no such guard: core checks
     * the same round trip itself in the `old-save` context.
     */
    public function filterPreTermSlug(mixed $slug, mixed $taxonomy = ''): mixed
    {
        if (!is_string($slug) || Encoding::isAscii($slug) || !is_string($taxonomy) || $taxonomy === '') {
            return $slug;
        }
        if (preg_match('//u', $slug) !== 1 || !$this->isEnabled()) {
            return $slug;
        }
        $encoded = sanitize_title_with_dashes($slug, $slug, 'save');
        if (!Encoding::hasEncodedHighByte($encoded)) {
            return $slug;
        }

        return get_term_by('slug', $encoded, $taxonomy) instanceof \WP_Term ? $encoded : $slug;
    }

    /**
     * Safety net on `wp_unique_post_slug` (PRD 4.7).
     */
    public function filterUniquePostSlug(
        mixed $slug,
        mixed $postId = 0,
        mixed $status = '',
        mixed $postType = '',
        mixed $parent = 0,
        mixed $originalSlug = ''
    ): mixed {
        if ($this->inSafetyNet || !is_string($slug) || !Encoding::hasEncodedHighByte($slug)) {
            return $slug;
        }
        if (!$this->isEnabled() || !is_string($postType) || !in_array($postType, $this->postTypes(), true)) {
            return $slug;
        }

        $postId = is_numeric($postId) ? (int) $postId : 0;
        $original = is_string($originalSlug) && $originalSlug !== '' ? $originalSlug : $slug;
        $post = $postId > 0 ? get_post($postId) : null;
        if ($post instanceof \WP_Post && $post->post_name !== '' && ($post->post_name === $original || $post->post_name === $slug)) {
            return $slug;
        }

        $source = $post instanceof \WP_Post && trim($post->post_title) !== ''
            ? $post->post_title
            : rawurldecode($original);
        $date = $post instanceof \WP_Post ? self::postDate($post->post_date_gmt) : null;

        $new = $this->makeSlug($source, $date);

        $this->inSafetyNet = true;
        try {
            $unique = wp_unique_post_slug(
                $new,
                $postId,
                is_string($status) ? $status : '',
                $postType,
                is_numeric($parent) ? (int) $parent : 0
            );
        } finally {
            $this->inSafetyNet = false;
        }

        return $unique;
    }

    /**
     * Builds the slug, runs `ascii_slugs_slug`, fires `ascii_slugs_generated`
     * and logs. Never throws: any library error gives the hash fallback.
     */
    public function makeSlug(string $raw, ?DateTimeInterface $date = null): string
    {
        return $this->build($raw, $date, true);
    }

    /**
     * The slug the plugin would make, with the `ascii_slugs_slug` filter
     * applied but no actions fired and nothing logged. Used by audit and
     * dry runs.
     */
    public function proposeSlug(string $raw, ?DateTimeInterface $date = null): string
    {
        return $this->build($raw, $date, false);
    }

    /**
     * The library slugger with the options in force.
     */
    public function slugger(): Slugger
    {
        $options = $this->options();
        $key = serialize($options->toArray());
        if ($this->slugger === null || $key !== $this->sluggerKey) {
            $this->slugger = ($this->sluggerFactory)($options);
            $this->sluggerKey = $key;
        }

        return $this->slugger;
    }

    /**
     * Library options after `ascii_slugs_options`. Bad values fall back to
     * the defaults with one log line.
     */
    public function options(): SlugOptions
    {
        $defaults = new SlugOptions();
        $values = apply_filters('ascii_slugs_options', $defaults->toArray());
        if (!is_array($values)) {
            $this->logger->notice('ascii_slugs_options returned a non-array; defaults used');

            return $defaults;
        }
        try {
            return SlugOptions::fromArray($values);
        } catch (InvalidOptionException $e) {
            $this->logger->error($e);

            return $defaults;
        }
    }

    /**
     * Post types the safety net and the repair command cover.
     *
     * @return list<string>
     */
    public function postTypes(): array
    {
        $registered = get_post_types([], 'names');
        $default = array_values(array_diff(array_map('strval', array_values($registered)), self::EXCLUDED_POST_TYPES));
        $value = apply_filters('ascii_slugs_post_types', $default);
        if (!is_array($value)) {
            $this->logger->notice('ascii_slugs_post_types returned a non-array; ignored');

            return $default;
        }
        $types = [];
        foreach ($value as $type) {
            if (!is_string($type)) {
                $this->logger->notice('ascii_slugs_post_types returned a non-string entry; ignored');

                return $default;
            }
            $types[] = $type;
        }

        return $types;
    }

    /**
     * A post's GMT date, or null when WordPress has not set one yet.
     */
    public static function postDate(mixed $gmt): ?DateTimeImmutable
    {
        if (!is_string($gmt) || $gmt === '' || str_starts_with($gmt, '0000-00-00')) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $gmt, new DateTimeZone('UTC'));

        return $date === false ? null : $date;
    }

    private function build(string $raw, ?DateTimeInterface $date, bool $announce): string
    {
        $slugger = $this->slugger();
        try {
            $result = $slugger->slug($raw, $date);
        } catch (Throwable $error) {
            $fallback = $slugger->fallbackForBytes($raw, $date);
            if ($announce) {
                do_action('ascii_slugs_error', $error, $raw);
                $this->logger->error($error);
            }

            return $fallback;
        }

        $filtered = apply_filters('ascii_slugs_slug', $result->slug, $raw, $result);
        if (!is_string($filtered)) {
            $this->logger->notice('ascii_slugs_slug returned a non-string; ignored');
            $slug = $result->slug;
        } else {
            $slug = $slugger->normalise($filtered);
            if ($slug === '') {
                $slug = $slugger->fallback($raw, $date);
            }
        }

        if ($announce) {
            do_action('ascii_slugs_generated', $slug, $raw, $result);
            $this->logResult($result, $slug);
        }

        return $slug;
    }

    private function logResult(SlugResult $result, string $slug): void
    {
        if ($result->method !== SlugResult::METHOD_ASCII) {
            $this->logger->generated($result->method, $result->coverage, $slug);
        }
    }
}
