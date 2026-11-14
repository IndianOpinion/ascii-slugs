<?php

/**
 * Run: php examples/basic.php
 *
 * Turns a few titles into slugs with the default options. Works without
 * Composer: bootstrap.php is the only file it loads.
 */

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use IndianOpinion\AsciiSlugs\Slugger;

$slugger = new Slugger();

$titles = [
    'ISRO का चंद्रयान-3 मिशन',
    'சென்னையில் கனமழை எச்சரிக்கை',
    'لاہور میں بارش',
    'Москва и Αθήνα',
    'ข่าวด่วนวันนี้',
];

foreach ($titles as $title) {
    $result = $slugger->slug($title);
    printf("%-15s %-40s coverage %.2f\n", $result->method, $result->slug, $result->coverage);
}
