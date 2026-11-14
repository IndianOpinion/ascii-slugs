<?php

/**
 * Example filters for a theme's functions.php or a small mu-plugin.
 * Each block is independent; copy only what you need.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Longer slugs, a different fallback prefix, and no ICU.
add_filter('ascii_slugs_options', static function (array $options): array {
    $options['maxLength'] = 120;
    $options['fallbackPrefix'] = 'story';
    $options['intlMode'] = 'never';

    return $options;
});

// Keep slugs that editors type by hand, even in a non-Latin script. A raw
// title that looks like a slug (no spaces) is treated as hand-typed here.
add_filter('ascii_slugs_should_handle', static function (bool $handle, string $raw): bool {
    return $handle && str_contains(trim($raw), ' ');
}, 10, 2);

// Count hashed slugs in your own metrics.
add_action('ascii_slugs_generated', static function (string $slug, string $raw, $result): void {
    if ($result->method === 'hashed') {
        do_action('my_metrics_increment', 'ascii_slugs.hashed');
    }
}, 10, 3);

// Purge the old URL at the edge after a repair.
add_action('ascii_slugs_repaired', static function (int $postId, string $oldSlug, string $newSlug): void {
    do_action('my_cache_purge_post', $postId);
}, 10, 3);
