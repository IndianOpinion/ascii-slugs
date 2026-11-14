<?php

/**
 * Run: php examples/custom-table.php
 *
 * Adds a small table for a script the library does not cover, so those
 * titles stop falling back to the hash.
 */

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use IndianOpinion\AsciiSlugs\ArrayScriptTable;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\Transliterator;

$armenian = new ArrayScriptTable('armenian', 'alphabet', [[0x0530, 0x058F]], [
    'letter' => [
        "\u{0535}" => 'Ye', "\u{0580}" => 'r', "\u{0587}" => 'ev', "\u{0561}" => 'a', "\u{0576}" => 'n',
    ],
]);

$transliterator = (new Transliterator('never'))->withTable($armenian);
$slugger = new Slugger(new SlugOptions(intlMode: 'never'), null, $transliterator);

echo $slugger->slug('Երևան')->slug, PHP_EOL; // yerevan
