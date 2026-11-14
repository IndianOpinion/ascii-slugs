<?php

/**
 * Transliteration table: Odia.
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'odia',
    'type' => 'brahmic',
    'ranges' => [
        [0x0B00, 0x0B7F],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => false,
        'anusvara' => 'assimilate',
    ],
    'classes' => [
        'consonant' => [
            "\u{0B15}" => 'k', // ORIYA LETTER KA
            "\u{0B16}" => 'kh', // ORIYA LETTER KHA
            "\u{0B17}" => 'g', // ORIYA LETTER GA
            "\u{0B18}" => 'gh', // ORIYA LETTER GHA
            "\u{0B19}" => 'n', // ORIYA LETTER NGA
            "\u{0B1A}" => 'ch', // ORIYA LETTER CA
            "\u{0B1B}" => 'chh', // ORIYA LETTER CHA
            "\u{0B1C}" => 'j', // ORIYA LETTER JA
            "\u{0B1C}\u{0B4D}\u{0B1E}" => 'gy', // ORIYA LETTER JA + ORIYA SIGN VIRAMA + ORIYA LETTER NYA
            "\u{0B1D}" => 'jh', // ORIYA LETTER JHA
            "\u{0B1E}" => 'n', // ORIYA LETTER NYA
            "\u{0B1F}" => 't', // ORIYA LETTER TTA
            "\u{0B20}" => 'th', // ORIYA LETTER TTHA
            "\u{0B21}" => 'd', // ORIYA LETTER DDA
            "\u{0B21}\u{0B3C}" => 'r', // ORIYA LETTER DDA + ORIYA SIGN NUKTA
            "\u{0B22}" => 'dh', // ORIYA LETTER DDHA
            "\u{0B22}\u{0B3C}" => 'rh', // ORIYA LETTER DDHA + ORIYA SIGN NUKTA
            "\u{0B23}" => 'n', // ORIYA LETTER NNA
            "\u{0B24}" => 't', // ORIYA LETTER TA
            "\u{0B25}" => 'th', // ORIYA LETTER THA
            "\u{0B26}" => 'd', // ORIYA LETTER DA
            "\u{0B27}" => 'dh', // ORIYA LETTER DHA
            "\u{0B28}" => 'n', // ORIYA LETTER NA
            "\u{0B2A}" => 'p', // ORIYA LETTER PA
            "\u{0B2B}" => 'ph', // ORIYA LETTER PHA
            "\u{0B2C}" => 'b', // ORIYA LETTER BA
            "\u{0B2D}" => 'bh', // ORIYA LETTER BHA
            "\u{0B2E}" => 'm', // ORIYA LETTER MA
            "\u{0B2F}" => 'j', // ORIYA LETTER YA
            "\u{0B30}" => 'r', // ORIYA LETTER RA
            "\u{0B32}" => 'l', // ORIYA LETTER LA
            "\u{0B33}" => 'l', // ORIYA LETTER LLA
            "\u{0B35}" => 'v', // ORIYA LETTER VA
            "\u{0B36}" => 'sh', // ORIYA LETTER SHA
            "\u{0B37}" => 'sh', // ORIYA LETTER SSA
            "\u{0B38}" => 's', // ORIYA LETTER SA
            "\u{0B39}" => 'h', // ORIYA LETTER HA
            "\u{0B5C}" => 'r', // ORIYA LETTER RRA
            "\u{0B5D}" => 'rh', // ORIYA LETTER RHA
            "\u{0B5F}" => 'y', // ORIYA LETTER YYA
            "\u{0B71}" => 'w', // ORIYA LETTER WA
        ],
        'vowel' => [
            "\u{0B05}" => 'a', // ORIYA LETTER A
            "\u{0B06}" => 'a', // ORIYA LETTER AA
            "\u{0B07}" => 'i', // ORIYA LETTER I
            "\u{0B08}" => 'i', // ORIYA LETTER II
            "\u{0B09}" => 'u', // ORIYA LETTER U
            "\u{0B0A}" => 'u', // ORIYA LETTER UU
            "\u{0B0B}" => 'ri', // ORIYA LETTER VOCALIC R
            "\u{0B0C}" => 'li', // ORIYA LETTER VOCALIC L
            "\u{0B0F}" => 'e', // ORIYA LETTER E
            "\u{0B10}" => 'ai', // ORIYA LETTER AI
            "\u{0B13}" => 'o', // ORIYA LETTER O
            "\u{0B14}" => 'au', // ORIYA LETTER AU
            "\u{0B60}" => 'ri', // ORIYA LETTER VOCALIC RR
            "\u{0B61}" => 'li', // ORIYA LETTER VOCALIC LL
        ],
        'sign' => [
            "\u{0B3E}" => 'a', // ORIYA VOWEL SIGN AA
            "\u{0B3F}" => 'i', // ORIYA VOWEL SIGN I
            "\u{0B40}" => 'i', // ORIYA VOWEL SIGN II
            "\u{0B41}" => 'u', // ORIYA VOWEL SIGN U
            "\u{0B42}" => 'u', // ORIYA VOWEL SIGN UU
            "\u{0B43}" => 'ri', // ORIYA VOWEL SIGN VOCALIC R
            "\u{0B44}" => 'ri', // ORIYA VOWEL SIGN VOCALIC RR
            "\u{0B47}" => 'e', // ORIYA VOWEL SIGN E
            "\u{0B47}\u{0B3E}" => 'o', // ORIYA VOWEL SIGN E + ORIYA VOWEL SIGN AA
            "\u{0B47}\u{0B56}" => 'ai', // ORIYA VOWEL SIGN E + ORIYA AI LENGTH MARK
            "\u{0B47}\u{0B57}" => 'au', // ORIYA VOWEL SIGN E + ORIYA AU LENGTH MARK
            "\u{0B48}" => 'ai', // ORIYA VOWEL SIGN AI
            "\u{0B4B}" => 'o', // ORIYA VOWEL SIGN O
            "\u{0B4C}" => 'au', // ORIYA VOWEL SIGN AU
            "\u{0B56}" => 'ai', // ORIYA AI LENGTH MARK
            "\u{0B57}" => 'au', // ORIYA AU LENGTH MARK
            "\u{0B62}" => 'li', // ORIYA VOWEL SIGN VOCALIC L
            "\u{0B63}" => 'li', // ORIYA VOWEL SIGN VOCALIC LL
        ],
        'virama' => [
            "\u{0B4D}" => '', // ORIYA SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0B02}" => '', // ORIYA SIGN ANUSVARA
        ],
        'chandrabindu' => [
            "\u{0B01}" => 'n', // ORIYA SIGN CANDRABINDU
        ],
        'visarga' => [
            "\u{0B03}" => 'h', // ORIYA SIGN VISARGA
        ],
        'mark' => [
            "\u{0B3C}" => '', // ORIYA SIGN NUKTA
            "\u{0B3D}" => '', // ORIYA SIGN AVAGRAHA
        ],
        'digit' => [
            "\u{0B66}" => '0', // ORIYA DIGIT ZERO
            "\u{0B67}" => '1', // ORIYA DIGIT ONE
            "\u{0B68}" => '2', // ORIYA DIGIT TWO
            "\u{0B69}" => '3', // ORIYA DIGIT THREE
            "\u{0B6A}" => '4', // ORIYA DIGIT FOUR
            "\u{0B6B}" => '5', // ORIYA DIGIT FIVE
            "\u{0B6C}" => '6', // ORIYA DIGIT SIX
            "\u{0B6D}" => '7', // ORIYA DIGIT SEVEN
            "\u{0B6E}" => '8', // ORIYA DIGIT EIGHT
            "\u{0B6F}" => '9', // ORIYA DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
        ],
    ],
];
