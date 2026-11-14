<?php

/**
 * Transliteration table: Kannada.
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'kannada',
    'type' => 'brahmic',
    'ranges' => [
        [0x0C80, 0x0CFF],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => false,
        'anusvara' => 'assimilate',
        'anusvaraFinal' => 'm',
    ],
    'classes' => [
        'consonant' => [
            "\u{0C95}" => 'k', // KANNADA LETTER KA
            "\u{0C96}" => 'kh', // KANNADA LETTER KHA
            "\u{0C97}" => 'g', // KANNADA LETTER GA
            "\u{0C98}" => 'gh', // KANNADA LETTER GHA
            "\u{0C99}" => 'n', // KANNADA LETTER NGA
            "\u{0C9A}" => 'ch', // KANNADA LETTER CA
            "\u{0C9B}" => 'chh', // KANNADA LETTER CHA
            "\u{0C9C}" => 'j', // KANNADA LETTER JA
            "\u{0C9D}" => 'jh', // KANNADA LETTER JHA
            "\u{0C9E}" => 'n', // KANNADA LETTER NYA
            "\u{0C9F}" => 't', // KANNADA LETTER TTA
            "\u{0CA0}" => 'th', // KANNADA LETTER TTHA
            "\u{0CA1}" => 'd', // KANNADA LETTER DDA
            "\u{0CA2}" => 'dh', // KANNADA LETTER DDHA
            "\u{0CA3}" => 'n', // KANNADA LETTER NNA
            "\u{0CA4}" => 't', // KANNADA LETTER TA
            "\u{0CA5}" => 'th', // KANNADA LETTER THA
            "\u{0CA6}" => 'd', // KANNADA LETTER DA
            "\u{0CA7}" => 'dh', // KANNADA LETTER DHA
            "\u{0CA8}" => 'n', // KANNADA LETTER NA
            "\u{0CAA}" => 'p', // KANNADA LETTER PA
            "\u{0CAB}" => 'ph', // KANNADA LETTER PHA
            "\u{0CAC}" => 'b', // KANNADA LETTER BA
            "\u{0CAD}" => 'bh', // KANNADA LETTER BHA
            "\u{0CAE}" => 'm', // KANNADA LETTER MA
            "\u{0CAF}" => 'y', // KANNADA LETTER YA
            "\u{0CB0}" => 'r', // KANNADA LETTER RA
            "\u{0CB1}" => 'r', // KANNADA LETTER RRA
            "\u{0CB2}" => 'l', // KANNADA LETTER LA
            "\u{0CB3}" => 'l', // KANNADA LETTER LLA
            "\u{0CB5}" => 'v', // KANNADA LETTER VA
            "\u{0CB6}" => 'sh', // KANNADA LETTER SHA
            "\u{0CB7}" => 'sh', // KANNADA LETTER SSA
            "\u{0CB8}" => 's', // KANNADA LETTER SA
            "\u{0CB9}" => 'h', // KANNADA LETTER HA
            "\u{0CDE}" => 'zh', // KANNADA LETTER FA (a misnomer: the letter is LLLA)
        ],
        'dead' => [
            "\u{0CDD}" => 'n', // KANNADA LETTER NAKAARA POLLU
        ],
        'vowel' => [
            "\u{0C85}" => 'a', // KANNADA LETTER A
            "\u{0C86}" => 'a', // KANNADA LETTER AA
            "\u{0C87}" => 'i', // KANNADA LETTER I
            "\u{0C88}" => 'i', // KANNADA LETTER II
            "\u{0C89}" => 'u', // KANNADA LETTER U
            "\u{0C8A}" => 'u', // KANNADA LETTER UU
            "\u{0C8B}" => 'ri', // KANNADA LETTER VOCALIC R
            "\u{0C8C}" => 'li', // KANNADA LETTER VOCALIC L
            "\u{0C8E}" => 'e', // KANNADA LETTER E
            "\u{0C8F}" => 'e', // KANNADA LETTER EE
            "\u{0C90}" => 'ai', // KANNADA LETTER AI
            "\u{0C92}" => 'o', // KANNADA LETTER O
            "\u{0C93}" => 'o', // KANNADA LETTER OO
            "\u{0C94}" => 'au', // KANNADA LETTER AU
            "\u{0CE0}" => 'ri', // KANNADA LETTER VOCALIC RR
            "\u{0CE1}" => 'li', // KANNADA LETTER VOCALIC LL
        ],
        'sign' => [
            "\u{0CBE}" => 'a', // KANNADA VOWEL SIGN AA
            "\u{0CBF}" => 'i', // KANNADA VOWEL SIGN I
            "\u{0CBF}\u{0CD5}" => 'i', // KANNADA VOWEL SIGN I + KANNADA LENGTH MARK
            "\u{0CC0}" => 'i', // KANNADA VOWEL SIGN II
            "\u{0CC1}" => 'u', // KANNADA VOWEL SIGN U
            "\u{0CC2}" => 'u', // KANNADA VOWEL SIGN UU
            "\u{0CC3}" => 'ri', // KANNADA VOWEL SIGN VOCALIC R
            "\u{0CC4}" => 'ri', // KANNADA VOWEL SIGN VOCALIC RR
            "\u{0CC6}" => 'e', // KANNADA VOWEL SIGN E
            "\u{0CC6}\u{0CC2}" => 'o', // KANNADA VOWEL SIGN E + KANNADA VOWEL SIGN UU
            "\u{0CC6}\u{0CC2}\u{0CD5}" => 'o', // KANNADA VOWEL SIGN E + KANNADA VOWEL SIGN UU + KANNADA LENGTH MARK
            "\u{0CC6}\u{0CD5}" => 'e', // KANNADA VOWEL SIGN E + KANNADA LENGTH MARK
            "\u{0CC6}\u{0CD6}" => 'ai', // KANNADA VOWEL SIGN E + KANNADA AI LENGTH MARK
            "\u{0CC7}" => 'e', // KANNADA VOWEL SIGN EE
            "\u{0CC8}" => 'ai', // KANNADA VOWEL SIGN AI
            "\u{0CCA}" => 'o', // KANNADA VOWEL SIGN O
            "\u{0CCA}\u{0CD5}" => 'o', // KANNADA VOWEL SIGN O + KANNADA LENGTH MARK
            "\u{0CCB}" => 'o', // KANNADA VOWEL SIGN OO
            "\u{0CCC}" => 'au', // KANNADA VOWEL SIGN AU
            "\u{0CD5}" => '', // KANNADA LENGTH MARK
            "\u{0CD6}" => 'ai', // KANNADA AI LENGTH MARK
            "\u{0CE2}" => 'li', // KANNADA VOWEL SIGN VOCALIC L
            "\u{0CE3}" => 'li', // KANNADA VOWEL SIGN VOCALIC LL
        ],
        'virama' => [
            "\u{0CCD}" => '', // KANNADA SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0C82}" => '', // KANNADA SIGN ANUSVARA
        ],
        'chandrabindu' => [
            "\u{0C81}" => 'n', // KANNADA SIGN CANDRABINDU
        ],
        'visarga' => [
            "\u{0C83}" => 'h', // KANNADA SIGN VISARGA
        ],
        'mark' => [
            "\u{0C84}" => '', // KANNADA SIGN SIDDHAM
            "\u{0CBC}" => '', // KANNADA SIGN NUKTA
            "\u{0CBD}" => '', // KANNADA SIGN AVAGRAHA
        ],
        'digit' => [
            "\u{0CE6}" => '0', // KANNADA DIGIT ZERO
            "\u{0CE7}" => '1', // KANNADA DIGIT ONE
            "\u{0CE8}" => '2', // KANNADA DIGIT TWO
            "\u{0CE9}" => '3', // KANNADA DIGIT THREE
            "\u{0CEA}" => '4', // KANNADA DIGIT FOUR
            "\u{0CEB}" => '5', // KANNADA DIGIT FIVE
            "\u{0CEC}" => '6', // KANNADA DIGIT SIX
            "\u{0CED}" => '7', // KANNADA DIGIT SEVEN
            "\u{0CEE}" => '8', // KANNADA DIGIT EIGHT
            "\u{0CEF}" => '9', // KANNADA DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
        ],
    ],
];
