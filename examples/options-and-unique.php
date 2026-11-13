<?php

/**
 * Run: php examples/options-and-unique.php
 *
 * Custom options, a fixed clock and the uniqueness helper, as a static
 * site generator or importer would use them.
 */

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;

$options = new SlugOptions(
    maxLength: 60,
    fallbackPrefix: 'story',
    intlMode: 'never',
);
$slugger = new Slugger($options, FixedClock::at('2026-11-12'));

// Slugs already used on the site.
$taken = ['bharat' => true, 'bharat-2' => true];

$slug = $slugger->slug('भारत')->slug;
$unique = $slugger->unique($slug, static fn (string $candidate): bool => isset($taken[$candidate]));
echo $unique, PHP_EOL; // bharat-3

// A title in a script with no built-in table, and intl turned off.
echo $slugger->slug('ข่าวด่วนวันนี้')->slug, PHP_EOL; // story-20261112-...
