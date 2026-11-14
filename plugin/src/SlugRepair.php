<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

use Generator;

/**
 * Read-only audit and explicit repair of stored percent-encoded slugs
 * (PRD 4.9).
 */
final class SlugRepair
{
    public const HIERARCHICAL_REASON = 'hierarchical post type: core old-slug redirects do not cover it, so the old URL would break';

    public function __construct(private readonly Plugin $plugin, private readonly PostSource $source)
    {
    }

    /**
     * Lists posts with encoded slugs and the slug the plugin would give
     * now. Writes nothing.
     *
     * @return list<RepairRow>
     */
    public function audit(RepairQuery $query): array
    {
        $rows = [];
        foreach ($this->posts($query) as $post) {
            $rows[] = new RepairRow(
                $post->id,
                $post->postType,
                $post->status,
                $post->slug,
                $this->proposal($post),
                RepairRow::AUDIT
            );
        }

        return $rows;
    }

    /**
     * Repairs encoded slugs. Without $write it is a dry run and writes
     * nothing. With $write each post is saved through wp_update_post(), so
     * core records the old slug and redirects it.
     */
    public function repair(RepairQuery $query, bool $write): RepairReport
    {
        $rows = [];
        foreach ($this->posts($query) as $post) {
            $rows[] = $this->repairOne($post, $write);
        }

        return new RepairReport($rows);
    }

    private function repairOne(EncodedPost $post, bool $write): RepairRow
    {
        $row = static fn (string $new, string $outcome, string $message = ''): RepairRow
            => new RepairRow($post->id, $post->postType, $post->status, $post->slug, $new, $outcome, $message);

        if (is_post_type_hierarchical($post->postType)) {
            return $row('', RepairRow::SKIPPED, self::HIERARCHICAL_REASON);
        }
        if (trim($post->title) === '') {
            return $row('', RepairRow::SKIPPED, 'empty title: nothing to build a slug from');
        }

        $new = $this->proposal($post);
        if (!$write) {
            return $row($new, RepairRow::DRY_RUN);
        }

        $result = $this->updateSlug($post->id, $new);
        if (is_wp_error($result)) {
            return $row($new, RepairRow::FAILED, $result->get_error_message());
        }

        $saved = get_post($post->id);
        $stored = $saved instanceof \WP_Post ? $saved->post_name : $new;
        do_action('ascii_slugs_repaired', $post->id, $post->slug, $stored);

        return $row($stored, RepairRow::REPAIRED);
    }

    /**
     * Saves the new slug through core's update path.
     *
     * wp_update_post() saves the whole post again. WP-CLI usually runs with
     * no user, and then core's kses filters are active and would strip
     * iframes, scripts and embeds from the stored content. Only the slug
     * should change, so the content filters are lifted for this one save
     * and put back after it.
     *
     * @return int|\WP_Error
     */
    private function updateSlug(int $id, string $slug): mixed
    {
        $kses = has_filter('content_save_pre', 'wp_filter_post_kses') !== false;
        if ($kses) {
            kses_remove_filters();
        }
        try {
            return wp_update_post(['ID' => $id, 'post_name' => $slug], true);
        } finally {
            if ($kses) {
                kses_init_filters();
            }
        }
    }

    private function proposal(EncodedPost $post): string
    {
        $title = trim($post->title) !== '' ? $post->title : rawurldecode($post->slug);

        return $this->plugin->proposeSlug($title, Plugin::postDate($post->dateGmt));
    }

    /**
     * @return Generator<int, EncodedPost>
     */
    private function posts(RepairQuery $query): Generator
    {
        $seen = 0;
        $after = 0;
        while ($seen < $query->limit) {
            $size = min($query->batch, $query->limit - $seen);
            $batch = $this->source->encodedPosts($query->postTypes, $query->statuses, $query->ids, $after, $size);
            if ($batch === []) {
                return;
            }
            foreach ($batch as $post) {
                $after = max($after, $post->id);
                $seen++;
                yield $post;
                if ($seen >= $query->limit) {
                    return;
                }
            }
            if (function_exists('wp_cache_flush_runtime')) {
                wp_cache_flush_runtime();
            }
        }
    }
}
