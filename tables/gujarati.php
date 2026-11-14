<?php

/**
 * Transliteration table: Gujarati.
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'gujarati',
    'type' => 'brahmic',
    'ranges' => [
        [0x0A80, 0x0AFF],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => true,
        'anusvara' => 'assimilate',
    ],
    'classes' => [
        'consonant' => [
            "\u{0A95}" => 'k', // GUJARATI LETTER KA
            "\u{0A96}" => 'kh', // GUJARATI LETTER KHA
            "\u{0A97}" => 'g', // GUJARATI LETTER GA
            "\u{0A98}" => 'gh', // GUJARATI LETTER GHA
            "\u{0A99}" => 'n', // GUJARATI LETTER NGA
            "\u{0A9A}" => 'ch', // GUJARATI LETTER CA
            "\u{0A9B}" => 'chh', // GUJARATI LETTER CHA
            "\u{0A9C}" => 'j', // GUJARATI LETTER JA
            "\u{0A9C}\u{0ACD}\u{0A9E}" => 'gy', // GUJARATI LETTER JA + GUJARATI SIGN VIRAMA + GUJARATI LETTER NYA
            "\u{0A9D}" => 'jh', // GUJARATI LETTER JHA
            "\u{0A9E}" => 'n', // GUJARATI LETTER NYA
            "\u{0A9F}" => 't', // GUJARATI LETTER TTA
            "\u{0AA0}" => 'th', // GUJARATI LETTER TTHA
            "\u{0AA1}" => 'd', // GUJARATI LETTER DDA
            "\u{0AA2}" => 'dh', // GUJARATI LETTER DDHA
            "\u{0AA3}" => 'n', // GUJARATI LETTER NNA
            "\u{0AA4}" => 't', // GUJARATI LETTER TA
            "\u{0AA5}" => 'th', // GUJARATI LETTER THA
            "\u{0AA6}" => 'd', // GUJARATI LETTER DA
            "\u{0AA7}" => 'dh', // GUJARATI LETTER DHA
            "\u{0AA8}" => 'n', // GUJARATI LETTER NA
            "\u{0AAA}" => 'p', // GUJARATI LETTER PA
            "\u{0AAB}" => 'ph', // GUJARATI LETTER PHA
            "\u{0AAC}" => 'b', // GUJARATI LETTER BA
            "\u{0AAD}" => 'bh', // GUJARATI LETTER BHA
            "\u{0AAE}" => 'm', // GUJARATI LETTER MA
            "\u{0AAF}" => 'y', // GUJARATI LETTER YA
            "\u{0AB0}" => 'r', // GUJARATI LETTER RA
            "\u{0AB2}" => 'l', // GUJARATI LETTER LA
            "\u{0AB3}" => 'l', // GUJARATI LETTER LLA
            "\u{0AB5}" => 'v', // GUJARATI LETTER VA
            "\u{0AB6}" => 'sh', // GUJARATI LETTER SHA
            "\u{0AB7}" => 'sh', // GUJARATI LETTER SSA
            "\u{0AB8}" => 's', // GUJARATI LETTER SA
            "\u{0AB9}" => 'h', // GUJARATI LETTER HA
        ],
        'vowel' => [
            "\u{0A85}" => 'a', // GUJARATI LETTER A
            "\u{0A86}" => 'a', // GUJARATI LETTER AA
            "\u{0A87}" => 'i', // GUJARATI LETTER I
            "\u{0A88}" => 'i', // GUJARATI LETTER II
            "\u{0A89}" => 'u', // GUJARATI LETTER U
            "\u{0A8A}" => 'u', // GUJARATI LETTER UU
            "\u{0A8B}" => 'ri', // GUJARATI LETTER VOCALIC R
            "\u{0A8C}" => 'li', // GUJARATI LETTER VOCALIC L
            "\u{0A8D}" => 'e', // GUJARATI VOWEL CANDRA E
            "\u{0A8F}" => 'e', // GUJARATI LETTER E
            "\u{0A90}" => 'ai', // GUJARATI LETTER AI
            "\u{0A91}" => 'o', // GUJARATI VOWEL CANDRA O
            "\u{0A93}" => 'o', // GUJARATI LETTER O
            "\u{0A94}" => 'au', // GUJARATI LETTER AU
            "\u{0AE0}" => 'ri', // GUJARATI LETTER VOCALIC RR
            "\u{0AE1}" => 'li', // GUJARATI LETTER VOCALIC LL
        ],
        'sign' => [
            "\u{0ABE}" => 'a', // GUJARATI VOWEL SIGN AA
            "\u{0ABF}" => 'i', // GUJARATI VOWEL SIGN I
            "\u{0AC0}" => 'i', // GUJARATI VOWEL SIGN II
            "\u{0AC1}" => 'u', // GUJARATI VOWEL SIGN U
            "\u{0AC2}" => 'u', // GUJARATI VOWEL SIGN UU
            "\u{0AC3}" => 'ri', // GUJARATI VOWEL SIGN VOCALIC R
            "\u{0AC4}" => 'ri', // GUJARATI VOWEL SIGN VOCALIC RR
            "\u{0AC5}" => 'e', // GUJARATI VOWEL SIGN CANDRA E
            "\u{0AC7}" => 'e', // GUJARATI VOWEL SIGN E
            "\u{0AC8}" => 'ai', // GUJARATI VOWEL SIGN AI
            "\u{0AC9}" => 'o', // GUJARATI VOWEL SIGN CANDRA O
            "\u{0ACB}" => 'o', // GUJARATI VOWEL SIGN O
            "\u{0ACC}" => 'au', // GUJARATI VOWEL SIGN AU
            "\u{0AE2}" => 'li', // GUJARATI VOWEL SIGN VOCALIC L
            "\u{0AE3}" => 'li', // GUJARATI VOWEL SIGN VOCALIC LL
        ],
        'virama' => [
            "\u{0ACD}" => '', // GUJARATI SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0A82}" => '', // GUJARATI SIGN ANUSVARA
        ],
        'chandrabindu' => [
            "\u{0A81}" => 'n', // GUJARATI SIGN CANDRABINDU
        ],
        'visarga' => [
            "\u{0A83}" => 'h', // GUJARATI SIGN VISARGA
        ],
        'mark' => [
            "\u{0ABC}" => '', // GUJARATI SIGN NUKTA
            "\u{0ABD}" => '', // GUJARATI SIGN AVAGRAHA
        ],
        'digit' => [
            "\u{0AE6}" => '0', // GUJARATI DIGIT ZERO
            "\u{0AE7}" => '1', // GUJARATI DIGIT ONE
            "\u{0AE8}" => '2', // GUJARATI DIGIT TWO
            "\u{0AE9}" => '3', // GUJARATI DIGIT THREE
            "\u{0AEA}" => '4', // GUJARATI DIGIT FOUR
            "\u{0AEB}" => '5', // GUJARATI DIGIT FIVE
            "\u{0AEC}" => '6', // GUJARATI DIGIT SIX
            "\u{0AED}" => '7', // GUJARATI DIGIT SEVEN
            "\u{0AEE}" => '8', // GUJARATI DIGIT EIGHT
            "\u{0AEF}" => '9', // GUJARATI DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
            "\u{0AF0}" => '', // GUJARATI ABBREVIATION SIGN
        ],
        'other' => [
            "\u{0AD0}" => 'om', // GUJARATI OM
        ],
    ],
];
