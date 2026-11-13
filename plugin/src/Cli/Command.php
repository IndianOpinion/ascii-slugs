<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress\Cli;

use IndianOpinion\AsciiSlugs\Transliterator;
use IndianOpinion\AsciiSlugs\WordPress\Plugin;
use IndianOpinion\AsciiSlugs\WordPress\PostSource;
use IndianOpinion\AsciiSlugs\WordPress\RepairQuery;
use IndianOpinion\AsciiSlugs\WordPress\RepairRow;
use IndianOpinion\AsciiSlugs\WordPress\SlugRepair;
use IndianOpinion\AsciiSlugs\WordPress\WpdbPostSource;
use InvalidArgumentException;
use WP_CLI;

/**
 * Previews, audits and repairs ASCII slugs.
 */
final class Command
{
    private const FORMATS = ['table', 'csv', 'json', 'count'];

    private ?PostSource $source;

    public function __construct(private readonly Plugin $plugin, ?PostSource $source = null)
    {
        $this->source = $source;
    }

    /**
     * Shows the slug a title would get, and why.
     *
     * ## OPTIONS
     *
     * <title>
     * : The title to turn into a slug.
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp ascii-slugs preview "ISRO का चंद्रयान-3 मिशन"
     *
     * @param list<string> $args
     * @param array<string, mixed> $assoc
     */
    public function preview(array $args, array $assoc): void
    {
        $title = $args[0] ?? '';
        $format = $this->format($assoc, ['table', 'json']);
        $slugger = $this->plugin->slugger();
        try {
            $result = $slugger->slug($title);
        } catch (InvalidArgumentException $error) {
            WP_CLI::error($error->getMessage());

            return;
        }
        $item = [
            'slug' => $this->plugin->proposeSlug($title),
            'method' => $result->method,
            'coverage' => sprintf('%.2f', $result->coverage),
            'scripts' => implode(',', $result->scripts),
            'intl' => $result->method === 'intl' ? 'yes' : 'no',
        ];
        \WP_CLI\Utils\format_items($format, [$item], array_keys($item));
    }

    /**
     * Shows the version, the kill switch, intl, the options and the post
     * types the safety net covers.
     *
     * ## EXAMPLES
     *
     *     wp ascii-slugs status
     *
     * @param list<string> $args
     * @param array<string, mixed> $assoc
     */
    public function status(array $args, array $assoc): void
    {
        $items = [
            ['setting' => 'version', 'value' => Plugin::VERSION],
            ['setting' => 'enabled', 'value' => $this->plugin->isEnabled() ? 'yes' : 'no'],
            ['setting' => 'intl', 'value' => Transliterator::intlAvailable() ? 'yes (ICU ' . Transliterator::icuVersion() . ')' : 'no'],
            ['setting' => 'built-in tables', 'value' => implode(', ', Transliterator::builtinScripts())],
        ];
        foreach ($this->plugin->options()->toArray() as $key => $value) {
            $items[] = ['setting' => $key, 'value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value];
        }
        $items[] = ['setting' => 'post types', 'value' => implode(', ', $this->plugin->postTypes())];
        \WP_CLI\Utils\format_items('table', $items, ['setting', 'value']);
    }

    /**
     * Lists posts whose stored slug is percent-encoded. Read-only.
     *
     * Shows the ID, post type, status, byte length, whether the slug is at
     * the 200-byte limit (likely cut), and the slug the plugin would give.
     *
     * ## OPTIONS
     *
     * [--post_type=<list>]
     * : Comma-separated post types. Defaults to the types the plugin covers.
     *
     * [--status=<list>]
     * : Comma-separated post statuses.
     * ---
     * default: publish
     * ---
     *
     * [--limit=<n>]
     * : At most this many posts.
     * ---
     * default: 100
     * ---
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     wp ascii-slugs audit --format=count
     *     wp ascii-slugs audit --post_type=post --limit=20
     *
     * @param list<string> $args
     * @param array<string, mixed> $assoc
     */
    public function audit(array $args, array $assoc): void
    {
        $format = $this->format($assoc, self::FORMATS);
        $query = $this->query($assoc, false);
        $rows = $this->repairer()->audit($query);
        $items = array_map(static fn (RepairRow $row): array => $row->toArray(false), $rows);
        \WP_CLI\Utils\format_items($format, $items, ['ID', 'post_type', 'status', 'bytes', 'likely_cut', 'new_slug']);
    }

    /**
     * Replaces percent-encoded post slugs with ASCII slugs. Dry run unless
     * --yes is given.
     *
     * The new slug is built from the stored post title, not from the cut
     * slug. Each post is saved through core's normal update, so core keeps
     * the old slug and its old-slug redirect sends the old URL to the new
     * one with a 301. The post's modified date moves to now: that is core
     * behaviour. Hierarchical post types such as pages are skipped, because
     * core does not redirect their old slugs. Edge caches and sitemaps are
     * not touched; purge them as usual.
     *
     * ## OPTIONS
     *
     * [--post_type=<list>]
     * : Comma-separated post types. Defaults to the types the plugin covers.
     *
     * [--status=<list>]
     * : Comma-separated post statuses.
     * ---
     * default: publish
     * ---
     *
     * [--ids=<list>]
     * : Comma-separated post IDs.
     *
     * [--limit=<n>]
     * : At most this many posts.
     * ---
     * default: 100
     * ---
     *
     * [--batch=<n>]
     * : Posts per database query. The runtime object cache is flushed
     * between batches.
     * ---
     * default: 50
     * ---
     *
     * [--yes]
     * : Write the changes. Without it nothing is written.
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp ascii-slugs repair
     *     wp ascii-slugs repair --ids=12,15 --yes
     *
     * @param list<string> $args
     * @param array<string, mixed> $assoc
     */
    public function repair(array $args, array $assoc): void
    {
        $format = $this->format($assoc, ['table', 'csv', 'json']);
        $write = isset($assoc['yes']) && $assoc['yes'] !== false;
        $query = $this->query($assoc, true);
        $report = $this->repairer()->repair($query, $write);

        $items = array_map(static fn (RepairRow $row): array => $row->toArray(true), $report->rows);
        \WP_CLI\Utils\format_items($format, $items, ['ID', 'post_type', 'status', 'bytes', 'likely_cut', 'change', 'result', 'message']);

        foreach ($report->rows as $row) {
            if ($row->outcome === RepairRow::FAILED) {
                WP_CLI::warning(sprintf('Post %d failed: %s', $row->id, $row->message));
            }
        }

        if (!$write) {
            WP_CLI::log(sprintf('Dry run: %d posts would change. Nothing was written. Add --yes to write.', $report->count(RepairRow::DRY_RUN)));
        }
        $summary = sprintf(
            'Repaired %d, skipped %d, failed %d.',
            $report->count(RepairRow::REPAIRED),
            $report->count(RepairRow::SKIPPED),
            $report->count(RepairRow::FAILED)
        );
        if ($report->hasFailures()) {
            WP_CLI::error($summary);
        }
        WP_CLI::success($summary);
    }

    private function repairer(): SlugRepair
    {
        return new SlugRepair($this->plugin, $this->source ?? new WpdbPostSource());
    }

    /**
     * @param array<string, mixed> $assoc
     * @param list<string> $allowed
     */
    private function format(array $assoc, array $allowed): string
    {
        $format = $assoc['format'] ?? 'table';
        if (!is_string($format) || !in_array($format, $allowed, true)) {
            WP_CLI::error(sprintf('--format must be one of: %s.', implode(', ', $allowed)));
        }

        return is_string($format) ? $format : 'table';
    }

    /**
     * @param array<string, mixed> $assoc
     */
    private function query(array $assoc, bool $allowIds): RepairQuery
    {
        try {
            $types = isset($assoc['post_type']) ? self::list($assoc['post_type']) : $this->plugin->postTypes();
            $statuses = isset($assoc['status']) ? self::list($assoc['status']) : ['publish'];
            $ids = null;
            if ($allowIds && isset($assoc['ids'])) {
                $ids = [];
                foreach (self::list($assoc['ids']) as $id) {
                    if (!ctype_digit($id)) {
                        throw new InvalidArgumentException('--ids must list positive post IDs.');
                    }
                    $ids[] = (int) $id;
                }
            }

            return new RepairQuery(
                $types,
                $statuses,
                $ids,
                self::positiveInt($assoc['limit'] ?? '100', '--limit'),
                self::positiveInt($assoc['batch'] ?? '50', '--batch')
            );
        } catch (InvalidArgumentException $error) {
            WP_CLI::error($error->getMessage());

            throw $error;
        }
    }

    /**
     * @return list<string>
     */
    private static function list(mixed $value): array
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Expected a comma-separated list.');
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $part): bool => $part !== ''));
    }

    private static function positiveInt(mixed $value, string $name): int
    {
        if (is_int($value)) {
            $value = (string) $value;
        }
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
            throw new InvalidArgumentException(sprintf('%s must be a positive whole number.', $name));
        }

        return (int) $value;
    }
}
