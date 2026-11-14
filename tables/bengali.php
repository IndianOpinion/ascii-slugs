<?php

/**
 * Transliteration table: Bengali-Assamese.
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'bengali',
    'type' => 'brahmic',
    'ranges' => [
        [0x0980, 0x09FF],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => true,
        'anusvara' => 'ng',
    ],
    'classes' => [
        'consonant' => [
            "\u{0995}" => 'k', // BENGALI LETTER KA
            "\u{0996}" => 'kh', // BENGALI LETTER KHA
            "\u{0997}" => 'g', // BENGALI LETTER GA
            "\u{0998}" => 'gh', // BENGALI LETTER GHA
            "\u{0999}" => 'n', // BENGALI LETTER NGA
            "\u{099A}" => 'ch', // BENGALI LETTER CA
            "\u{099B}" => 'chh', // BENGALI LETTER CHA
            "\u{099C}" => 'j', // BENGALI LETTER JA
            "\u{099C}\u{09CD}\u{099E}" => 'gy', // BENGALI LETTER JA + BENGALI SIGN VIRAMA + BENGALI LETTER NYA
            "\u{099D}" => 'jh', // BENGALI LETTER JHA
            "\u{099E}" => 'n', // BENGALI LETTER NYA
            "\u{099F}" => 't', // BENGALI LETTER TTA
            "\u{09A0}" => 'th', // BENGALI LETTER TTHA
            "\u{09A1}" => 'd', // BENGALI LETTER DDA
            "\u{09A1}\u{09BC}" => 'r', // BENGALI LETTER DDA + BENGALI SIGN NUKTA
            "\u{09A2}" => 'dh', // BENGALI LETTER DDHA
            "\u{09A2}\u{09BC}" => 'rh', // BENGALI LETTER DDHA + BENGALI SIGN NUKTA
            "\u{09A3}" => 'n', // BENGALI LETTER NNA
            "\u{09A4}" => 't', // BENGALI LETTER TA
            "\u{09A5}" => 'th', // BENGALI LETTER THA
            "\u{09A6}" => 'd', // BENGALI LETTER DA
            "\u{09A7}" => 'dh', // BENGALI LETTER DHA
            "\u{09A8}" => 'n', // BENGALI LETTER NA
            "\u{09AA}" => 'p', // BENGALI LETTER PA
            "\u{09AB}" => 'ph', // BENGALI LETTER PHA
            "\u{09AC}" => 'b', // BENGALI LETTER BA
            "\u{09AD}" => 'bh', // BENGALI LETTER BHA
            "\u{09AE}" => 'm', // BENGALI LETTER MA
            "\u{09AF}" => 'j', // BENGALI LETTER YA
            "\u{09AF}\u{09BC}" => 'y', // BENGALI LETTER YA + BENGALI SIGN NUKTA
            "\u{09B0}" => 'r', // BENGALI LETTER RA
            "\u{09B2}" => 'l', // BENGALI LETTER LA
            "\u{09B6}" => 'sh', // BENGALI LETTER SHA
            "\u{09B7}" => 'sh', // BENGALI LETTER SSA
            "\u{09B8}" => 's', // BENGALI LETTER SA
            "\u{09B9}" => 'h', // BENGALI LETTER HA
            "\u{09DC}" => 'r', // BENGALI LETTER RRA
            "\u{09DD}" => 'rh', // BENGALI LETTER RHA
            "\u{09DF}" => 'y', // BENGALI LETTER YYA
            "\u{09F0}" => 'r', // BENGALI LETTER RA WITH MIDDLE DIAGONAL
            "\u{09F1}" => 'w', // BENGALI LETTER RA WITH LOWER DIAGONAL
        ],
        'dead' => [
            "\u{09CE}" => 't', // BENGALI LETTER KHANDA TA
        ],
        'vowel' => [
            "\u{0985}" => 'a', // BENGALI LETTER A
            "\u{0986}" => 'a', // BENGALI LETTER AA
            "\u{0987}" => 'i', // BENGALI LETTER I
            "\u{0988}" => 'i', // BENGALI LETTER II
            "\u{0989}" => 'u', // BENGALI LETTER U
            "\u{098A}" => 'u', // BENGALI LETTER UU
            "\u{098B}" => 'ri', // BENGALI LETTER VOCALIC R
            "\u{098C}" => 'li', // BENGALI LETTER VOCALIC L
            "\u{098F}" => 'e', // BENGALI LETTER E
            "\u{0990}" => 'ai', // BENGALI LETTER AI
            "\u{0993}" => 'o', // BENGALI LETTER O
            "\u{0994}" => 'au', // BENGALI LETTER AU
            "\u{09E0}" => 'ri', // BENGALI LETTER VOCALIC RR
            "\u{09E1}" => 'li', // BENGALI LETTER VOCALIC LL
        ],
        'sign' => [
            "\u{09BE}" => 'a', // BENGALI VOWEL SIGN AA
            "\u{09BF}" => 'i', // BENGALI VOWEL SIGN I
            "\u{09C0}" => 'i', // BENGALI VOWEL SIGN II
            "\u{09C1}" => 'u', // BENGALI VOWEL SIGN U
            "\u{09C2}" => 'u', // BENGALI VOWEL SIGN UU
            "\u{09C3}" => 'ri', // BENGALI VOWEL SIGN VOCALIC R
            "\u{09C4}" => 'ri', // BENGALI VOWEL SIGN VOCALIC RR
            "\u{09C7}" => 'e', // BENGALI VOWEL SIGN E
            "\u{09C7}\u{09BE}" => 'o', // BENGALI VOWEL SIGN E + BENGALI VOWEL SIGN AA
            "\u{09C7}\u{09D7}" => 'au', // BENGALI VOWEL SIGN E + BENGALI AU LENGTH MARK
            "\u{09C8}" => 'ai', // BENGALI VOWEL SIGN AI
            "\u{09CB}" => 'o', // BENGALI VOWEL SIGN O
            "\u{09CC}" => 'au', // BENGALI VOWEL SIGN AU
            "\u{09D7}" => 'au', // BENGALI AU LENGTH MARK
            "\u{09E2}" => 'li', // BENGALI VOWEL SIGN VOCALIC L
            "\u{09E3}" => 'li', // BENGALI VOWEL SIGN VOCALIC LL
        ],
        'virama' => [
            "\u{09CD}" => '', // BENGALI SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0982}" => '', // BENGALI SIGN ANUSVARA
        ],
        'chandrabindu' => [
            "\u{0981}" => 'n', // BENGALI SIGN CANDRABINDU
        ],
        'visarga' => [
            "\u{0983}" => 'h', // BENGALI SIGN VISARGA
        ],
        'mark' => [
            "\u{09BC}" => '', // BENGALI SIGN NUKTA
            "\u{09BD}" => '', // BENGALI SIGN AVAGRAHA
        ],
        'digit' => [
            "\u{09E6}" => '0', // BENGALI DIGIT ZERO
            "\u{09E7}" => '1', // BENGALI DIGIT ONE
            "\u{09E8}" => '2', // BENGALI DIGIT TWO
            "\u{09E9}" => '3', // BENGALI DIGIT THREE
            "\u{09EA}" => '4', // BENGALI DIGIT FOUR
            "\u{09EB}" => '5', // BENGALI DIGIT FIVE
            "\u{09EC}" => '6', // BENGALI DIGIT SIX
            "\u{09ED}" => '7', // BENGALI DIGIT SEVEN
            "\u{09EE}" => '8', // BENGALI DIGIT EIGHT
            "\u{09EF}" => '9', // BENGALI DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
        ],
    ],
];
