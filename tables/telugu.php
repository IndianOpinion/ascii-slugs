<?php

/**
 * Transliteration table: Telugu.
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'telugu',
    'type' => 'brahmic',
    'ranges' => [
        [0x0C00, 0x0C7F],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => false,
        'anusvara' => 'assimilate',
        'anusvaraFinal' => 'm',
    ],
    'classes' => [
        'consonant' => [
            "\u{0C15}" => 'k', // TELUGU LETTER KA
            "\u{0C16}" => 'kh', // TELUGU LETTER KHA
            "\u{0C17}" => 'g', // TELUGU LETTER GA
            "\u{0C18}" => 'gh', // TELUGU LETTER GHA
            "\u{0C19}" => 'n', // TELUGU LETTER NGA
            "\u{0C1A}" => 'ch', // TELUGU LETTER CA
            "\u{0C1B}" => 'chh', // TELUGU LETTER CHA
            "\u{0C1C}" => 'j', // TELUGU LETTER JA
            "\u{0C1D}" => 'jh', // TELUGU LETTER JHA
            "\u{0C1E}" => 'n', // TELUGU LETTER NYA
            "\u{0C1F}" => 't', // TELUGU LETTER TTA
            "\u{0C20}" => 'th', // TELUGU LETTER TTHA
            "\u{0C21}" => 'd', // TELUGU LETTER DDA
            "\u{0C22}" => 'dh', // TELUGU LETTER DDHA
            "\u{0C23}" => 'n', // TELUGU LETTER NNA
            "\u{0C24}" => 't', // TELUGU LETTER TA
            "\u{0C25}" => 'th', // TELUGU LETTER THA
            "\u{0C26}" => 'd', // TELUGU LETTER DA
            "\u{0C27}" => 'dh', // TELUGU LETTER DHA
            "\u{0C28}" => 'n', // TELUGU LETTER NA
            "\u{0C2A}" => 'p', // TELUGU LETTER PA
            "\u{0C2B}" => 'ph', // TELUGU LETTER PHA
            "\u{0C2C}" => 'b', // TELUGU LETTER BA
            "\u{0C2D}" => 'bh', // TELUGU LETTER BHA
            "\u{0C2E}" => 'm', // TELUGU LETTER MA
            "\u{0C2F}" => 'y', // TELUGU LETTER YA
            "\u{0C30}" => 'r', // TELUGU LETTER RA
            "\u{0C31}" => 'r', // TELUGU LETTER RRA
            "\u{0C32}" => 'l', // TELUGU LETTER LA
            "\u{0C33}" => 'l', // TELUGU LETTER LLA
            "\u{0C34}" => 'zh', // TELUGU LETTER LLLA
            "\u{0C35}" => 'v', // TELUGU LETTER VA
            "\u{0C36}" => 'sh', // TELUGU LETTER SHA
            "\u{0C37}" => 'sh', // TELUGU LETTER SSA
            "\u{0C38}" => 's', // TELUGU LETTER SA
            "\u{0C39}" => 'h', // TELUGU LETTER HA
            "\u{0C58}" => 'ts', // TELUGU LETTER TSA
            "\u{0C59}" => 'z', // TELUGU LETTER DZA
            "\u{0C5A}" => 'r', // TELUGU LETTER RRRA
        ],
        'dead' => [
            "\u{0C5D}" => 'n', // TELUGU LETTER NAKAARA POLLU
        ],
        'vowel' => [
            "\u{0C05}" => 'a', // TELUGU LETTER A
            "\u{0C06}" => 'a', // TELUGU LETTER AA
            "\u{0C07}" => 'i', // TELUGU LETTER I
            "\u{0C08}" => 'i', // TELUGU LETTER II
            "\u{0C09}" => 'u', // TELUGU LETTER U
            "\u{0C0A}" => 'u', // TELUGU LETTER UU
            "\u{0C0B}" => 'ri', // TELUGU LETTER VOCALIC R
            "\u{0C0C}" => 'li', // TELUGU LETTER VOCALIC L
            "\u{0C0E}" => 'e', // TELUGU LETTER E
            "\u{0C0F}" => 'e', // TELUGU LETTER EE
            "\u{0C10}" => 'ai', // TELUGU LETTER AI
            "\u{0C12}" => 'o', // TELUGU LETTER O
            "\u{0C13}" => 'o', // TELUGU LETTER OO
            "\u{0C14}" => 'au', // TELUGU LETTER AU
            "\u{0C60}" => 'ri', // TELUGU LETTER VOCALIC RR
            "\u{0C61}" => 'li', // TELUGU LETTER VOCALIC LL
        ],
        'sign' => [
            "\u{0C3E}" => 'a', // TELUGU VOWEL SIGN AA
            "\u{0C3F}" => 'i', // TELUGU VOWEL SIGN I
            "\u{0C40}" => 'i', // TELUGU VOWEL SIGN II
            "\u{0C41}" => 'u', // TELUGU VOWEL SIGN U
            "\u{0C42}" => 'u', // TELUGU VOWEL SIGN UU
            "\u{0C43}" => 'ri', // TELUGU VOWEL SIGN VOCALIC R
            "\u{0C44}" => 'ri', // TELUGU VOWEL SIGN VOCALIC RR
            "\u{0C46}" => 'e', // TELUGU VOWEL SIGN E
            "\u{0C46}\u{0C56}" => 'ai', // TELUGU VOWEL SIGN E + TELUGU AI LENGTH MARK
            "\u{0C47}" => 'e', // TELUGU VOWEL SIGN EE
            "\u{0C48}" => 'ai', // TELUGU VOWEL SIGN AI
            "\u{0C4A}" => 'o', // TELUGU VOWEL SIGN O
            "\u{0C4B}" => 'o', // TELUGU VOWEL SIGN OO
            "\u{0C4C}" => 'au', // TELUGU VOWEL SIGN AU
            "\u{0C55}" => '', // TELUGU LENGTH MARK
            "\u{0C56}" => 'ai', // TELUGU AI LENGTH MARK
            "\u{0C62}" => 'li', // TELUGU VOWEL SIGN VOCALIC L
            "\u{0C63}" => 'li', // TELUGU VOWEL SIGN VOCALIC LL
        ],
        'virama' => [
            "\u{0C4D}" => '', // TELUGU SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0C02}" => '', // TELUGU SIGN ANUSVARA
            "\u{0C04}" => '', // TELUGU SIGN COMBINING ANUSVARA ABOVE
        ],
        'chandrabindu' => [
            "\u{0C00}" => 'n', // TELUGU SIGN COMBINING CANDRABINDU ABOVE
            "\u{0C01}" => 'n', // TELUGU SIGN CANDRABINDU
        ],
        'visarga' => [
            "\u{0C03}" => 'h', // TELUGU SIGN VISARGA
        ],
        'mark' => [
            "\u{0C3C}" => '', // TELUGU SIGN NUKTA
            "\u{0C3D}" => '', // TELUGU SIGN AVAGRAHA
        ],
        'digit' => [
            "\u{0C66}" => '0', // TELUGU DIGIT ZERO
            "\u{0C67}" => '1', // TELUGU DIGIT ONE
            "\u{0C68}" => '2', // TELUGU DIGIT TWO
            "\u{0C69}" => '3', // TELUGU DIGIT THREE
            "\u{0C6A}" => '4', // TELUGU DIGIT FOUR
            "\u{0C6B}" => '5', // TELUGU DIGIT FIVE
            "\u{0C6C}" => '6', // TELUGU DIGIT SIX
            "\u{0C6D}" => '7', // TELUGU DIGIT SEVEN
            "\u{0C6E}" => '8', // TELUGU DIGIT EIGHT
            "\u{0C6F}" => '9', // TELUGU DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
        ],
    ],
];
