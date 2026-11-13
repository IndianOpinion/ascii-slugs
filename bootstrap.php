<?php

/**
 * Autoloader for use without Composer:
 *
 *     require '/path/to/ascii-slugs/bootstrap.php';
 *
 * It loads the library (IndianOpinion\AsciiSlugs) and, for the WordPress
 * plugin, IndianOpinion\AsciiSlugs\WordPress. Neither part is loaded until
 * a class is used.
 */

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'IndianOpinion\\AsciiSlugs\\WordPress\\' => __DIR__ . '/plugin/src/',
        'IndianOpinion\\AsciiSlugs\\' => __DIR__ . '/src/',
    ];
    foreach ($prefixes as $prefix => $directory) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        if (preg_match('/^[A-Za-z0-9_\\\\]+$/', $relative) !== 1) {
            return;
        }
        $file = $directory . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }

        return;
    }
});
