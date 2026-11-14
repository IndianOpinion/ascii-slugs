<?php

/**
 * Plugin Name:       ASCII Slugs
 * Plugin URI:        https://github.com/IndianOpinion/ascii-slugs
 * Description:       Readable ASCII slugs for titles in Indian scripts, Urdu and other non-Latin scripts.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Indian Opinion
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       ascii-slugs
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(\IndianOpinion\AsciiSlugs\WordPress\Plugin::class)) {
    require_once __DIR__ . '/bootstrap.php';
}

\IndianOpinion\AsciiSlugs\WordPress\Plugin::boot();
