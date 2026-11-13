<?php

/**
 * Uninstall handler.
 *
 * ASCII Slugs stores nothing of its own: no options, tables, post meta,
 * transients or files. Slugs it made belong to the site and stay as they
 * are. This file exists so that is explicit; it removes nothing and never
 * rewrites slugs back.
 */

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}
