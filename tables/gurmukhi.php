<?php

/**
 * Transliteration table: Gurmukhi (Punjabi).
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'gurmukhi',
    'type' => 'brahmic',
    'ranges' => [
        [0x0A00, 0x0A7F],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => true,
        'anusvara' => 'assimilate',
    ],
    'classes' => [
        'consonant' => [
            "\u{0A15}" => 'k', // GURMUKHI LETTER KA
            "\u{0A16}" => 'kh', // GURMUKHI LETTER KHA
            "\u{0A16}\u{0A3C}" => 'kh', // GURMUKHI LETTER KHA + GURMUKHI SIGN NUKTA
            "\u{0A17}" => 'g', // GURMUKHI LETTER GA
            "\u{0A17}\u{0A3C}" => 'gh', // GURMUKHI LETTER GA + GURMUKHI SIGN NUKTA
            "\u{0A18}" => 'gh', // GURMUKHI LETTER GHA
            "\u{0A19}" => 'n', // GURMUKHI LETTER NGA
            "\u{0A1A}" => 'ch', // GURMUKHI LETTER CA
            "\u{0A1B}" => 'chh', // GURMUKHI LETTER CHA
            "\u{0A1C}" => 'j', // GURMUKHI LETTER JA
            "\u{0A1C}\u{0A3C}" => 'z', // GURMUKHI LETTER JA + GURMUKHI SIGN NUKTA
            "\u{0A1D}" => 'jh', // GURMUKHI LETTER JHA
            "\u{0A1E}" => 'n', // GURMUKHI LETTER NYA
            "\u{0A1F}" => 't', // GURMUKHI LETTER TTA
            "\u{0A20}" => 'th', // GURMUKHI LETTER TTHA
            "\u{0A21}" => 'd', // GURMUKHI LETTER DDA
            "\u{0A22}" => 'dh', // GURMUKHI LETTER DDHA
            "\u{0A23}" => 'n', // GURMUKHI LETTER NNA
            "\u{0A24}" => 't', // GURMUKHI LETTER TA
            "\u{0A25}" => 'th', // GURMUKHI LETTER THA
            "\u{0A26}" => 'd', // GURMUKHI LETTER DA
            "\u{0A27}" => 'dh', // GURMUKHI LETTER DHA
            "\u{0A28}" => 'n', // GURMUKHI LETTER NA
            "\u{0A2A}" => 'p', // GURMUKHI LETTER PA
            "\u{0A2B}" => 'ph', // GURMUKHI LETTER PHA
            "\u{0A2B}\u{0A3C}" => 'f', // GURMUKHI LETTER PHA + GURMUKHI SIGN NUKTA
            "\u{0A2C}" => 'b', // GURMUKHI LETTER BA
            "\u{0A2D}" => 'bh', // GURMUKHI LETTER BHA
            "\u{0A2E}" => 'm', // GURMUKHI LETTER MA
            "\u{0A2F}" => 'y', // GURMUKHI LETTER YA
            "\u{0A30}" => 'r', // GURMUKHI LETTER RA
            "\u{0A32}" => 'l', // GURMUKHI LETTER LA
            "\u{0A32}\u{0A3C}" => 'l', // GURMUKHI LETTER LA + GURMUKHI SIGN NUKTA
            "\u{0A33}" => 'l', // GURMUKHI LETTER LLA
            "\u{0A35}" => 'v', // GURMUKHI LETTER VA
            "\u{0A36}" => 'sh', // GURMUKHI LETTER SHA
            "\u{0A38}" => 's', // GURMUKHI LETTER SA
            "\u{0A38}\u{0A3C}" => 'sh', // GURMUKHI LETTER SA + GURMUKHI SIGN NUKTA
            "\u{0A39}" => 'h', // GURMUKHI LETTER HA
            "\u{0A59}" => 'kh', // GURMUKHI LETTER KHHA
            "\u{0A5A}" => 'gh', // GURMUKHI LETTER GHHA
            "\u{0A5B}" => 'z', // GURMUKHI LETTER ZA
            "\u{0A5C}" => 'r', // GURMUKHI LETTER RRA
            "\u{0A5E}" => 'f', // GURMUKHI LETTER FA
        ],
        'vowel' => [
            "\u{0A05}" => 'a', // GURMUKHI LETTER A
            "\u{0A06}" => 'a', // GURMUKHI LETTER AA
            "\u{0A07}" => 'i', // GURMUKHI LETTER I
            "\u{0A08}" => 'i', // GURMUKHI LETTER II
            "\u{0A09}" => 'u', // GURMUKHI LETTER U
            "\u{0A0A}" => 'u', // GURMUKHI LETTER UU
            "\u{0A0F}" => 'e', // GURMUKHI LETTER EE
            "\u{0A10}" => 'ai', // GURMUKHI LETTER AI
            "\u{0A13}" => 'o', // GURMUKHI LETTER OO
            "\u{0A14}" => 'au', // GURMUKHI LETTER AU
        ],
        'sign' => [
            "\u{0A3E}" => 'a', // GURMUKHI VOWEL SIGN AA
            "\u{0A3F}" => 'i', // GURMUKHI VOWEL SIGN I
            "\u{0A40}" => 'i', // GURMUKHI VOWEL SIGN II
            "\u{0A41}" => 'u', // GURMUKHI VOWEL SIGN U
            "\u{0A42}" => 'u', // GURMUKHI VOWEL SIGN UU
            "\u{0A47}" => 'e', // GURMUKHI VOWEL SIGN EE
            "\u{0A48}" => 'ai', // GURMUKHI VOWEL SIGN AI
            "\u{0A4B}" => 'o', // GURMUKHI VOWEL SIGN OO
            "\u{0A4C}" => 'au', // GURMUKHI VOWEL SIGN AU
        ],
        'virama' => [
            "\u{0A4D}" => '', // GURMUKHI SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0A02}" => '', // GURMUKHI SIGN BINDI
            "\u{0A70}" => '', // GURMUKHI TIPPI
        ],
        'chandrabindu' => [
            "\u{0A01}" => 'n', // GURMUKHI SIGN ADAK BINDI
        ],
        'visarga' => [
            "\u{0A03}" => 'h', // GURMUKHI SIGN VISARGA
        ],
        'addak' => [
            "\u{0A71}" => '', // GURMUKHI ADDAK
        ],
        'mark' => [
            "\u{0A3C}" => '', // GURMUKHI SIGN NUKTA
            "\u{0A51}" => '', // GURMUKHI SIGN UDAAT
            "\u{0A72}" => '', // GURMUKHI IRI
            "\u{0A73}" => '', // GURMUKHI URA
            "\u{0A75}" => '', // GURMUKHI SIGN YAKASH
        ],
        'digit' => [
            "\u{0A66}" => '0', // GURMUKHI DIGIT ZERO
            "\u{0A67}" => '1', // GURMUKHI DIGIT ONE
            "\u{0A68}" => '2', // GURMUKHI DIGIT TWO
            "\u{0A69}" => '3', // GURMUKHI DIGIT THREE
            "\u{0A6A}" => '4', // GURMUKHI DIGIT FOUR
            "\u{0A6B}" => '5', // GURMUKHI DIGIT FIVE
            "\u{0A6C}" => '6', // GURMUKHI DIGIT SIX
            "\u{0A6D}" => '7', // GURMUKHI DIGIT SEVEN
            "\u{0A6E}" => '8', // GURMUKHI DIGIT EIGHT
            "\u{0A6F}" => '9', // GURMUKHI DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
        ],
        'other' => [
            "\u{0A74}" => 'ek onkar', // GURMUKHI EK ONKAR
        ],
    ],
];
