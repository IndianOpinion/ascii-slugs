<?php

/**
 * Must-use loader. Copy this file to wp-content/mu-plugins/ascii-slugs.php
 * and put the plugin folder at wp-content/mu-plugins/ascii-slugs/.
 *
 * Plugin Name: ASCII Slugs (must-use loader)
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/ascii-slugs/ascii-slugs.php';
