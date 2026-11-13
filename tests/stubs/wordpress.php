<?php

/**
 * A small stand-in for the parts of WordPress the plugin uses.
 *
 * It keeps hooks, posts, terms and post meta in memory, and copies the
 * behaviour of the core functions that matter for slugs closely enough to
 * test the plugin without a WordPress install. It is test code only.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

final class WpStub
{
    /** @var array<string, array<int, list<array{0: callable, 1: int}>>> */
    public static array $filters = [];

    /** @var list<array{0: string, 1: list<mixed>}> */
    public static array $actions = [];

    /** @var array<int, WP_Post> */
    public static array $posts = [];

    /** @var array<int, array<string, list<string>>> */
    public static array $meta = [];

    /** @var array<int, array{name: string, slug: string, taxonomy: string}> */
    public static array $terms = [];

    /** @var array<string, bool> post type => hierarchical */
    public static array $postTypes = [];

    /** Number of writes to posts, meta and terms. */
    public static int $writes = 0;

    /** @var array<int, string> post ID => error message for wp_update_post */
    public static array $failUpdates = [];

    public static int $cacheFlushes = 0;

    public static function reset(): void
    {
        self::$filters = [];
        self::$actions = [];
        self::$posts = [];
        self::$meta = [];
        self::$terms = [];
        self::$writes = 0;
        self::$failUpdates = [];
        self::$cacheFlushes = 0;
        self::$postTypes = [
            'post' => false, 'page' => true, 'attachment' => false, 'revision' => false,
            'nav_menu_item' => false, 'customize_changeset' => false, 'oembed_cache' => false,
            'user_request' => false, 'wp_block' => false, 'news' => false,
        ];
    }

    /**
     * Adds a post straight to the store, as if it was already saved.
     *
     * @param array<string, mixed> $fields
     */
    public static function seedPost(array $fields): WP_Post
    {
        $post = new WP_Post();
        foreach ($fields as $key => $value) {
            $post->$key = $value;
        }
        if ($post->ID === 0) {
            $post->ID = self::$posts === [] ? 1 : max(array_keys(self::$posts)) + 1;
        }
        self::$posts[$post->ID] = $post;

        return $post;
    }

    /**
     * @return list<array{0: string, 1: list<mixed>}>
     */
    public static function actionsNamed(string $name): array
    {
        return array_values(array_filter(self::$actions, static fn (array $a): bool => $a[0] === $name));
    }
}

class WP_Post
{
    public int $ID = 0;
    public string $post_name = '';
    public string $post_title = '';
    public string $post_type = 'post';
    public string $post_status = 'publish';
    public string $post_date_gmt = '0000-00-00 00:00:00';
    public int $post_parent = 0;
}

class WP_Error
{
    public function __construct(private string $code = '', private string $message = '')
    {
    }

    public function get_error_message(): string
    {
        return $this->message;
    }

    public function get_error_code(): string
    {
        return $this->code;
    }
}

function is_wp_error(mixed $thing): bool
{
    return $thing instanceof WP_Error;
}

function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function add_filter(string $hook, callable $callback, int $priority = 10, int $args = 1): bool
{
    WpStub::$filters[$hook][$priority][] = [$callback, $args];

    return true;
}

function add_action(string $hook, callable $callback, int $priority = 10, int $args = 1): bool
{
    return add_filter($hook, $callback, $priority, $args);
}

function remove_all_filters(string $hook): bool
{
    unset(WpStub::$filters[$hook]);

    return true;
}

function remove_filter(string $hook, callable $callback, int $priority = 10): bool
{
    foreach (WpStub::$filters[$hook][$priority] ?? [] as $i => [$registered]) {
        if ($registered == $callback) {
            unset(WpStub::$filters[$hook][$priority][$i]);

            return true;
        }
    }

    return false;
}

function has_filter(string $hook): bool
{
    return !empty(WpStub::$filters[$hook]);
}

function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
{
    $callbacks = WpStub::$filters[$hook] ?? [];
    ksort($callbacks);
    foreach ($callbacks as $list) {
        foreach ($list as [$callback, $accepted]) {
            $value = $callback(...array_slice([$value, ...$args], 0, $accepted));
        }
    }

    return $value;
}

function do_action(string $hook, mixed ...$args): void
{
    WpStub::$actions[] = [$hook, $args];
    $callbacks = WpStub::$filters[$hook] ?? [];
    ksort($callbacks);
    foreach ($callbacks as $list) {
        foreach ($list as [$callback, $accepted]) {
            $callback(...array_slice($args, 0, $accepted));
        }
    }
}

function seems_utf8(string $text): bool
{
    return preg_match('//u', $text) === 1;
}

function remove_accents(string $text): string
{
    return $text;
}

function utf8_uri_encode(string $utf8, int $length = 0): string
{
    $out = '';
    foreach (preg_split('//u', $utf8, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
        $encoded = strlen($char) === 1 ? $char : implode('', array_map(
            static fn (string $byte): string => '%' . dechex(ord($byte)),
            str_split($char)
        ));
        if ($length > 0 && strlen($out) + strlen($encoded) > $length) {
            break;
        }
        $out .= $encoded;
    }

    return $out;
}

function sanitize_title(mixed $title, string $fallback = '', string $context = 'save'): string
{
    $raw = $title;
    if ($context === 'save') {
        $title = remove_accents((string) $title);
    }
    $title = apply_filters('sanitize_title', $title, $raw, $context);
    if ($title === '' || $title === false) {
        $title = $fallback;
    }

    return (string) $title;
}

/**
 * Close to core's sanitize_title_with_dashes(): keeps escaped octets,
 * percent-encodes UTF-8, strips curly quotes and turns dashes to hyphens
 * in the save context.
 */
function sanitize_title_with_dashes(mixed $title, mixed $raw = '', string $context = 'display'): string
{
    $title = strip_tags((string) $title);
    $title = (string) preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    $title = str_replace('%', '', $title);
    $title = (string) preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
    if (seems_utf8($title)) {
        $title = utf8_uri_encode(strtolower($title), 200);
    }
    $title = strtolower($title);
    if ($context === 'save') {
        $title = str_replace(['%c2%a0', '%e2%80%93', '%e2%80%94'], '-', $title);
        $title = str_replace('/', '-', $title);
        $title = str_replace([
            '%c2%ad', '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d', '%e2%80%8b', '%e2%80%8c',
            '%e2%80%8d', '%e2%80%8e', '%e2%80%8f', '%ef%bb%bf', '%e2%81%a0',
        ], '', $title);
    }
    $title = (string) preg_replace('/&.+?;/', '', $title);
    $title = str_replace('.', '-', $title);
    $title = (string) preg_replace('/[^%a-z0-9 _-]/', '', $title);
    $title = (string) preg_replace('/\s+/', '-', $title);
    $title = (string) preg_replace('|-+|', '-', $title);

    return trim($title, '-');
}

function get_post_types(array $args = [], string $output = 'names'): array
{
    $names = array_keys(WpStub::$postTypes);

    return array_combine($names, $names);
}

function is_post_type_hierarchical(string $type): bool
{
    return WpStub::$postTypes[$type] ?? false;
}

function get_post(mixed $id): ?WP_Post
{
    $id = (int) $id;

    return isset(WpStub::$posts[$id]) ? clone WpStub::$posts[$id] : null;
}

function wp_unique_post_slug(string $slug, int $postId, string $status, string $type, int $parent, string $original = ''): string
{
    if (in_array($status, ['draft', 'pending', 'auto-draft'], true)) {
        return $slug;
    }
    $original = $original === '' ? $slug : $original;
    $taken = static function (string $candidate) use ($postId, $type): bool {
        foreach (WpStub::$posts as $post) {
            if ($post->ID !== $postId && $post->post_type === $type && $post->post_name === $candidate) {
                return true;
            }
        }

        return false;
    };
    $unique = $slug;
    for ($n = 2; $taken($unique); $n++) {
        $unique = substr($slug, 0, 200 - strlen('-' . $n)) . '-' . $n;
    }

    return (string) apply_filters('wp_unique_post_slug', $unique, $postId, $status, $type, $parent, $original);
}

/**
 * Core's insert and update path, cut down to what matters for slugs.
 *
 * @param array<string, mixed> $data
 */
function wp_insert_post(array $data, bool $wpError = false): int|WP_Error
{
    $id = (int) ($data['ID'] ?? 0);
    $update = $id > 0;
    $previous = $update ? get_post($id) : null;
    if ($update && $previous === null) {
        return new WP_Error('invalid_post', 'Invalid post ID.');
    }
    if ($update && isset(WpStub::$failUpdates[$id])) {
        return new WP_Error('update_failed', WpStub::$failUpdates[$id]);
    }

    $post = $previous !== null ? clone $previous : new WP_Post();
    foreach (['post_title', 'post_type', 'post_status', 'post_date_gmt', 'post_parent'] as $field) {
        if (array_key_exists($field, $data)) {
            $post->$field = $data[$field];
        }
    }

    $name = array_key_exists('post_name', $data) ? (string) $data['post_name'] : ($previous->post_name ?? '');
    if ($name === '') {
        $name = in_array($post->post_status, ['draft', 'pending', 'auto-draft'], true)
            ? ''
            : sanitize_title($post->post_title);
    } else {
        $name = sanitize_title($name);
    }
    $name = wp_unique_post_slug($name, $id, $post->post_status, $post->post_type, $post->post_parent);

    if (!$update) {
        $post->ID = WpStub::$posts === [] ? 1 : max(array_keys(WpStub::$posts)) + 1;
    }
    $post->post_name = $name;
    WpStub::$posts[$post->ID] = $post;
    WpStub::$writes++;

    // Core's wp_check_for_changed_slugs() on post_updated.
    if (
        $previous !== null && $previous->post_name !== $name && $post->post_status === 'publish'
        && !is_post_type_hierarchical($post->post_type) && $previous->post_name !== ''
    ) {
        WpStub::$meta[$post->ID]['_wp_old_slug'][] = $previous->post_name;
    }

    return $post->ID;
}

/**
 * @param array<string, mixed> $data
 */
function wp_update_post(array $data, bool $wpError = false): int|WP_Error
{
    return wp_insert_post($data, $wpError);
}

/**
 * @return array{term_id: int}
 */
function wp_insert_term(string $name, string $taxonomy): array
{
    $id = count(WpStub::$terms) + 1;
    WpStub::$terms[$id] = ['name' => $name, 'slug' => sanitize_title($name), 'taxonomy' => $taxonomy];
    WpStub::$writes++;

    return ['term_id' => $id];
}

/**
 * What core's old-slug redirect would answer for a request to $slug:
 * the post ID it redirects to, or null.
 */
function stub_old_slug_redirect(string $slug): ?int
{
    foreach (WpStub::$meta as $postId => $meta) {
        if (in_array($slug, $meta['_wp_old_slug'] ?? [], true)) {
            return $postId;
        }
    }

    return null;
}

function wp_cache_flush_runtime(): bool
{
    WpStub::$cacheFlushes++;

    return true;
}

class wpdb
{
    public string $posts = 'wp_posts';

    /** @var list<array{0: string, 1: list<mixed>}> */
    public array $prepared = [];

    /** @var list<array<string, mixed>> */
    public array $rows = [];

    public int $count = 0;

    /**
     * @param list<mixed> $args
     */
    public function prepare(string $query, mixed ...$args): string
    {
        if (count($args) === 1 && is_array($args[0])) {
            $args = $args[0];
        }
        $this->prepared[] = [$query, $args];

        return 'prepared-' . (count($this->prepared) - 1);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function get_results(string $query, string $output = 'OBJECT'): array
    {
        return $this->rows;
    }

    public function get_var(string $query): string
    {
        return (string) $this->count;
    }
}

WpStub::reset();

function __return_false_stub(): bool
{
    return false;
}
