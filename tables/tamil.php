<?php

/**
 * Transliteration table: Tamil.
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'tamil',
    'type' => 'brahmic',
    'ranges' => [
        [0x0B80, 0x0BFF],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => false,
        'anusvara' => 'assimilate',
    ],
    'classes' => [
        'consonant' => [
            "\u{0B95}" => 'k', // TAMIL LETTER KA
            "\u{0B99}" => 'n', // TAMIL LETTER NGA
            "\u{0B9A}" => 'ch', // TAMIL LETTER CA
            "\u{0B9C}" => 'j', // TAMIL LETTER JA
            "\u{0B9E}" => 'n', // TAMIL LETTER NYA
            "\u{0B9F}" => 't', // TAMIL LETTER TTA
            "\u{0BA3}" => 'n', // TAMIL LETTER NNA
            "\u{0BA4}" => 't', // TAMIL LETTER TA
            "\u{0BA8}" => 'n', // TAMIL LETTER NA
            "\u{0BA9}" => 'n', // TAMIL LETTER NNNA
            "\u{0BAA}" => 'p', // TAMIL LETTER PA
            "\u{0BAE}" => 'm', // TAMIL LETTER MA
            "\u{0BAF}" => 'y', // TAMIL LETTER YA
            "\u{0BB0}" => 'r', // TAMIL LETTER RA
            "\u{0BB1}" => 'r', // TAMIL LETTER RRA
            "\u{0BB2}" => 'l', // TAMIL LETTER LA
            "\u{0BB3}" => 'l', // TAMIL LETTER LLA
            "\u{0BB4}" => 'zh', // TAMIL LETTER LLLA
            "\u{0BB5}" => 'v', // TAMIL LETTER VA
            "\u{0BB6}" => 'sh', // TAMIL LETTER SHA
            "\u{0BB7}" => 'sh', // TAMIL LETTER SSA
            "\u{0BB8}" => 's', // TAMIL LETTER SA
            "\u{0BB9}" => 'h', // TAMIL LETTER HA
        ],
        'dead' => [
            "\u{0B83}" => 'h', // TAMIL SIGN VISARGA
        ],
        'vowel' => [
            "\u{0B85}" => 'a', // TAMIL LETTER A
            "\u{0B86}" => 'a', // TAMIL LETTER AA
            "\u{0B87}" => 'i', // TAMIL LETTER I
            "\u{0B88}" => 'i', // TAMIL LETTER II
            "\u{0B89}" => 'u', // TAMIL LETTER U
            "\u{0B8A}" => 'u', // TAMIL LETTER UU
            "\u{0B8E}" => 'e', // TAMIL LETTER E
            "\u{0B8F}" => 'e', // TAMIL LETTER EE
            "\u{0B90}" => 'ai', // TAMIL LETTER AI
            "\u{0B92}" => 'o', // TAMIL LETTER O
            "\u{0B93}" => 'o', // TAMIL LETTER OO
            "\u{0B94}" => 'au', // TAMIL LETTER AU
        ],
        'sign' => [
            "\u{0BBE}" => 'a', // TAMIL VOWEL SIGN AA
            "\u{0BBF}" => 'i', // TAMIL VOWEL SIGN I
            "\u{0BC0}" => 'i', // TAMIL VOWEL SIGN II
            "\u{0BC1}" => 'u', // TAMIL VOWEL SIGN U
            "\u{0BC2}" => 'u', // TAMIL VOWEL SIGN UU
            "\u{0BC6}" => 'e', // TAMIL VOWEL SIGN E
            "\u{0BC6}\u{0BBE}" => 'o', // TAMIL VOWEL SIGN E + TAMIL VOWEL SIGN AA
            "\u{0BC6}\u{0BD7}" => 'au', // TAMIL VOWEL SIGN E + TAMIL AU LENGTH MARK
            "\u{0BC7}" => 'e', // TAMIL VOWEL SIGN EE
            "\u{0BC7}\u{0BBE}" => 'o', // TAMIL VOWEL SIGN EE + TAMIL VOWEL SIGN AA
            "\u{0BC8}" => 'ai', // TAMIL VOWEL SIGN AI
            "\u{0BCA}" => 'o', // TAMIL VOWEL SIGN O
            "\u{0BCB}" => 'o', // TAMIL VOWEL SIGN OO
            "\u{0BCC}" => 'au', // TAMIL VOWEL SIGN AU
            "\u{0BD7}" => 'au', // TAMIL AU LENGTH MARK
        ],
        'virama' => [
            "\u{0BCD}" => '', // TAMIL SIGN VIRAMA
        ],
        'digit' => [
            "\u{0BE6}" => '0', // TAMIL DIGIT ZERO
            "\u{0BE7}" => '1', // TAMIL DIGIT ONE
            "\u{0BE8}" => '2', // TAMIL DIGIT TWO
            "\u{0BE9}" => '3', // TAMIL DIGIT THREE
            "\u{0BEA}" => '4', // TAMIL DIGIT FOUR
            "\u{0BEB}" => '5', // TAMIL DIGIT FIVE
            "\u{0BEC}" => '6', // TAMIL DIGIT SIX
            "\u{0BED}" => '7', // TAMIL DIGIT SEVEN
            "\u{0BEE}" => '8', // TAMIL DIGIT EIGHT
            "\u{0BEF}" => '9', // TAMIL DIGIT NINE
            "\u{0BF0}" => '10', // TAMIL NUMBER TEN
            "\u{0BF1}" => '100', // TAMIL NUMBER ONE HUNDRED
            "\u{0BF2}" => '1000', // TAMIL NUMBER ONE THOUSAND
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
        ],
        'other' => [
            "\u{0BD0}" => 'om', // TAMIL OM
        ],
    ],
];
