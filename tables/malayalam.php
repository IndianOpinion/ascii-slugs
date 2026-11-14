<?php

/**
 * Transliteration table: Malayalam.
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'malayalam',
    'type' => 'brahmic',
    'ranges' => [
        [0x0D00, 0x0D7F],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => false,
        'anusvara' => 'm',
    ],
    'classes' => [
        'consonant' => [
            "\u{0D15}" => 'k', // MALAYALAM LETTER KA
            "\u{0D16}" => 'kh', // MALAYALAM LETTER KHA
            "\u{0D17}" => 'g', // MALAYALAM LETTER GA
            "\u{0D18}" => 'gh', // MALAYALAM LETTER GHA
            "\u{0D19}" => 'n', // MALAYALAM LETTER NGA
            "\u{0D1A}" => 'ch', // MALAYALAM LETTER CA
            "\u{0D1B}" => 'chh', // MALAYALAM LETTER CHA
            "\u{0D1C}" => 'j', // MALAYALAM LETTER JA
            "\u{0D1D}" => 'jh', // MALAYALAM LETTER JHA
            "\u{0D1E}" => 'n', // MALAYALAM LETTER NYA
            "\u{0D1F}" => 't', // MALAYALAM LETTER TTA
            "\u{0D20}" => 'th', // MALAYALAM LETTER TTHA
            "\u{0D21}" => 'd', // MALAYALAM LETTER DDA
            "\u{0D22}" => 'dh', // MALAYALAM LETTER DDHA
            "\u{0D23}" => 'n', // MALAYALAM LETTER NNA
            "\u{0D24}" => 't', // MALAYALAM LETTER TA
            "\u{0D25}" => 'th', // MALAYALAM LETTER THA
            "\u{0D26}" => 'd', // MALAYALAM LETTER DA
            "\u{0D27}" => 'dh', // MALAYALAM LETTER DHA
            "\u{0D28}" => 'n', // MALAYALAM LETTER NA
            "\u{0D28}\u{0D4D}\u{0D31}" => 'nt', // MALAYALAM LETTER NA + MALAYALAM SIGN VIRAMA + MALAYALAM LETTER RRA
            "\u{0D29}" => 'n', // MALAYALAM LETTER NNNA
            "\u{0D2A}" => 'p', // MALAYALAM LETTER PA
            "\u{0D2B}" => 'ph', // MALAYALAM LETTER PHA
            "\u{0D2C}" => 'b', // MALAYALAM LETTER BA
            "\u{0D2D}" => 'bh', // MALAYALAM LETTER BHA
            "\u{0D2E}" => 'm', // MALAYALAM LETTER MA
            "\u{0D2F}" => 'y', // MALAYALAM LETTER YA
            "\u{0D30}" => 'r', // MALAYALAM LETTER RA
            "\u{0D31}" => 'r', // MALAYALAM LETTER RRA
            "\u{0D31}\u{0D4D}\u{0D31}" => 'tt', // MALAYALAM LETTER RRA + MALAYALAM SIGN VIRAMA + MALAYALAM LETTER RRA
            "\u{0D32}" => 'l', // MALAYALAM LETTER LA
            "\u{0D33}" => 'l', // MALAYALAM LETTER LLA
            "\u{0D34}" => 'zh', // MALAYALAM LETTER LLLA
            "\u{0D35}" => 'v', // MALAYALAM LETTER VA
            "\u{0D36}" => 'sh', // MALAYALAM LETTER SHA
            "\u{0D37}" => 'sh', // MALAYALAM LETTER SSA
            "\u{0D38}" => 's', // MALAYALAM LETTER SA
            "\u{0D39}" => 'h', // MALAYALAM LETTER HA
            "\u{0D3A}" => 'tt', // MALAYALAM LETTER TTTA
        ],
        'dead' => [
            "\u{0D4E}" => 'r', // MALAYALAM LETTER DOT REPH
            "\u{0D54}" => 'm', // MALAYALAM LETTER CHILLU M
            "\u{0D55}" => 'y', // MALAYALAM LETTER CHILLU Y
            "\u{0D56}" => 'zh', // MALAYALAM LETTER CHILLU LLL
            "\u{0D7A}" => 'n', // MALAYALAM LETTER CHILLU NN
            "\u{0D7B}" => 'n', // MALAYALAM LETTER CHILLU N
            "\u{0D7C}" => 'r', // MALAYALAM LETTER CHILLU RR
            "\u{0D7D}" => 'l', // MALAYALAM LETTER CHILLU L
            "\u{0D7E}" => 'l', // MALAYALAM LETTER CHILLU LL
            "\u{0D7F}" => 'k', // MALAYALAM LETTER CHILLU K
        ],
        'vowel' => [
            "\u{0D05}" => 'a', // MALAYALAM LETTER A
            "\u{0D06}" => 'a', // MALAYALAM LETTER AA
            "\u{0D07}" => 'i', // MALAYALAM LETTER I
            "\u{0D08}" => 'i', // MALAYALAM LETTER II
            "\u{0D09}" => 'u', // MALAYALAM LETTER U
            "\u{0D0A}" => 'u', // MALAYALAM LETTER UU
            "\u{0D0B}" => 'ri', // MALAYALAM LETTER VOCALIC R
            "\u{0D0C}" => 'li', // MALAYALAM LETTER VOCALIC L
            "\u{0D0E}" => 'e', // MALAYALAM LETTER E
            "\u{0D0F}" => 'e', // MALAYALAM LETTER EE
            "\u{0D10}" => 'ai', // MALAYALAM LETTER AI
            "\u{0D12}" => 'o', // MALAYALAM LETTER O
            "\u{0D13}" => 'o', // MALAYALAM LETTER OO
            "\u{0D14}" => 'au', // MALAYALAM LETTER AU
            "\u{0D60}" => 'ri', // MALAYALAM LETTER VOCALIC RR
            "\u{0D61}" => 'li', // MALAYALAM LETTER VOCALIC LL
        ],
        'sign' => [
            "\u{0D3E}" => 'a', // MALAYALAM VOWEL SIGN AA
            "\u{0D3F}" => 'i', // MALAYALAM VOWEL SIGN I
            "\u{0D40}" => 'i', // MALAYALAM VOWEL SIGN II
            "\u{0D41}" => 'u', // MALAYALAM VOWEL SIGN U
            "\u{0D42}" => 'u', // MALAYALAM VOWEL SIGN UU
            "\u{0D43}" => 'ri', // MALAYALAM VOWEL SIGN VOCALIC R
            "\u{0D44}" => 'ri', // MALAYALAM VOWEL SIGN VOCALIC RR
            "\u{0D46}" => 'e', // MALAYALAM VOWEL SIGN E
            "\u{0D46}\u{0D3E}" => 'o', // MALAYALAM VOWEL SIGN E + MALAYALAM VOWEL SIGN AA
            "\u{0D46}\u{0D57}" => 'au', // MALAYALAM VOWEL SIGN E + MALAYALAM AU LENGTH MARK
            "\u{0D47}" => 'e', // MALAYALAM VOWEL SIGN EE
            "\u{0D47}\u{0D3E}" => 'o', // MALAYALAM VOWEL SIGN EE + MALAYALAM VOWEL SIGN AA
            "\u{0D48}" => 'ai', // MALAYALAM VOWEL SIGN AI
            "\u{0D4A}" => 'o', // MALAYALAM VOWEL SIGN O
            "\u{0D4B}" => 'o', // MALAYALAM VOWEL SIGN OO
            "\u{0D4C}" => 'au', // MALAYALAM VOWEL SIGN AU
            "\u{0D57}" => 'au', // MALAYALAM AU LENGTH MARK
            "\u{0D62}" => 'li', // MALAYALAM VOWEL SIGN VOCALIC L
            "\u{0D63}" => 'li', // MALAYALAM VOWEL SIGN VOCALIC LL
        ],
        'virama' => [
            "\u{0D4D}" => '', // MALAYALAM SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0D02}" => '', // MALAYALAM SIGN ANUSVARA
        ],
        'chandrabindu' => [
            "\u{0D01}" => 'n', // MALAYALAM SIGN CANDRABINDU
        ],
        'visarga' => [
            "\u{0D03}" => 'h', // MALAYALAM SIGN VISARGA
        ],
        'mark' => [
            "\u{0D04}" => '', // MALAYALAM LETTER VEDIC ANUSVARA
            "\u{0D3B}" => '', // MALAYALAM SIGN VERTICAL BAR VIRAMA
            "\u{0D3C}" => '', // MALAYALAM SIGN CIRCULAR VIRAMA
            "\u{0D3D}" => '', // MALAYALAM SIGN AVAGRAHA
        ],
        'digit' => [
            "\u{0D66}" => '0', // MALAYALAM DIGIT ZERO
            "\u{0D67}" => '1', // MALAYALAM DIGIT ONE
            "\u{0D68}" => '2', // MALAYALAM DIGIT TWO
            "\u{0D69}" => '3', // MALAYALAM DIGIT THREE
            "\u{0D6A}" => '4', // MALAYALAM DIGIT FOUR
            "\u{0D6B}" => '5', // MALAYALAM DIGIT FIVE
            "\u{0D6C}" => '6', // MALAYALAM DIGIT SIX
            "\u{0D6D}" => '7', // MALAYALAM DIGIT SEVEN
            "\u{0D6E}" => '8', // MALAYALAM DIGIT EIGHT
            "\u{0D6F}" => '9', // MALAYALAM DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
        ],
    ],
];
