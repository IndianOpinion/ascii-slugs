<?php

/**
 * Transliteration table: Devanagari (Hindi, Marathi, Nepali, Sanskrit, Konkani).
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'devanagari',
    'type' => 'brahmic',
    'ranges' => [
        [0x0900, 0x097F],
        [0xA8E0, 0xA8FF],
    ],
    'rules' => [
        'inherentVowel' => 'a',
        'dropFinalInherentVowel' => true,
        'anusvara' => 'assimilate',
    ],
    'classes' => [
        'consonant' => [
            "\u{0915}" => 'k', // DEVANAGARI LETTER KA
            "\u{0915}\u{093C}" => 'q', // DEVANAGARI LETTER KA + DEVANAGARI SIGN NUKTA
            "\u{0916}" => 'kh', // DEVANAGARI LETTER KHA
            "\u{0916}\u{093C}" => 'kh', // DEVANAGARI LETTER KHA + DEVANAGARI SIGN NUKTA
            "\u{0917}" => 'g', // DEVANAGARI LETTER GA
            "\u{0917}\u{093C}" => 'gh', // DEVANAGARI LETTER GA + DEVANAGARI SIGN NUKTA
            "\u{0918}" => 'gh', // DEVANAGARI LETTER GHA
            "\u{0919}" => 'n', // DEVANAGARI LETTER NGA
            "\u{091A}" => 'ch', // DEVANAGARI LETTER CA
            "\u{091B}" => 'chh', // DEVANAGARI LETTER CHA
            "\u{091C}" => 'j', // DEVANAGARI LETTER JA
            "\u{091C}\u{093C}" => 'z', // DEVANAGARI LETTER JA + DEVANAGARI SIGN NUKTA
            "\u{091C}\u{094D}\u{091E}" => 'gy', // DEVANAGARI LETTER JA + DEVANAGARI SIGN VIRAMA + DEVANAGARI LETTER NYA
            "\u{091D}" => 'jh', // DEVANAGARI LETTER JHA
            "\u{091E}" => 'n', // DEVANAGARI LETTER NYA
            "\u{091F}" => 't', // DEVANAGARI LETTER TTA
            "\u{0920}" => 'th', // DEVANAGARI LETTER TTHA
            "\u{0921}" => 'd', // DEVANAGARI LETTER DDA
            "\u{0921}\u{093C}" => 'r', // DEVANAGARI LETTER DDA + DEVANAGARI SIGN NUKTA
            "\u{0922}" => 'dh', // DEVANAGARI LETTER DDHA
            "\u{0922}\u{093C}" => 'rh', // DEVANAGARI LETTER DDHA + DEVANAGARI SIGN NUKTA
            "\u{0923}" => 'n', // DEVANAGARI LETTER NNA
            "\u{0924}" => 't', // DEVANAGARI LETTER TA
            "\u{0925}" => 'th', // DEVANAGARI LETTER THA
            "\u{0926}" => 'd', // DEVANAGARI LETTER DA
            "\u{0927}" => 'dh', // DEVANAGARI LETTER DHA
            "\u{0928}" => 'n', // DEVANAGARI LETTER NA
            "\u{0928}\u{093C}" => 'n', // DEVANAGARI LETTER NA + DEVANAGARI SIGN NUKTA
            "\u{0929}" => 'n', // DEVANAGARI LETTER NNNA
            "\u{092A}" => 'p', // DEVANAGARI LETTER PA
            "\u{092B}" => 'ph', // DEVANAGARI LETTER PHA
            "\u{092B}\u{093C}" => 'f', // DEVANAGARI LETTER PHA + DEVANAGARI SIGN NUKTA
            "\u{092C}" => 'b', // DEVANAGARI LETTER BA
            "\u{092D}" => 'bh', // DEVANAGARI LETTER BHA
            "\u{092E}" => 'm', // DEVANAGARI LETTER MA
            "\u{092F}" => 'y', // DEVANAGARI LETTER YA
            "\u{092F}\u{093C}" => 'y', // DEVANAGARI LETTER YA + DEVANAGARI SIGN NUKTA
            "\u{0930}" => 'r', // DEVANAGARI LETTER RA
            "\u{0930}\u{093C}" => 'r', // DEVANAGARI LETTER RA + DEVANAGARI SIGN NUKTA
            "\u{0931}" => 'r', // DEVANAGARI LETTER RRA
            "\u{0932}" => 'l', // DEVANAGARI LETTER LA
            "\u{0933}" => 'l', // DEVANAGARI LETTER LLA
            "\u{0933}\u{093C}" => 'zh', // DEVANAGARI LETTER LLA + DEVANAGARI SIGN NUKTA
            "\u{0934}" => 'zh', // DEVANAGARI LETTER LLLA
            "\u{0935}" => 'v', // DEVANAGARI LETTER VA
            "\u{0936}" => 'sh', // DEVANAGARI LETTER SHA
            "\u{0937}" => 'sh', // DEVANAGARI LETTER SSA
            "\u{0938}" => 's', // DEVANAGARI LETTER SA
            "\u{0939}" => 'h', // DEVANAGARI LETTER HA
            "\u{0958}" => 'q', // DEVANAGARI LETTER QA
            "\u{0959}" => 'kh', // DEVANAGARI LETTER KHHA
            "\u{095A}" => 'gh', // DEVANAGARI LETTER GHHA
            "\u{095B}" => 'z', // DEVANAGARI LETTER ZA
            "\u{095C}" => 'r', // DEVANAGARI LETTER DDDHA
            "\u{095D}" => 'rh', // DEVANAGARI LETTER RHA
            "\u{095E}" => 'f', // DEVANAGARI LETTER FA
            "\u{095F}" => 'y', // DEVANAGARI LETTER YYA
            "\u{0979}" => 'z', // DEVANAGARI LETTER ZHA
            "\u{097A}" => 'y', // DEVANAGARI LETTER HEAVY YA
        ],
        'vowel' => [
            "\u{0904}" => 'a', // DEVANAGARI LETTER SHORT A
            "\u{0905}" => 'a', // DEVANAGARI LETTER A
            "\u{0906}" => 'a', // DEVANAGARI LETTER AA
            "\u{0907}" => 'i', // DEVANAGARI LETTER I
            "\u{0908}" => 'i', // DEVANAGARI LETTER II
            "\u{0909}" => 'u', // DEVANAGARI LETTER U
            "\u{090A}" => 'u', // DEVANAGARI LETTER UU
            "\u{090B}" => 'ri', // DEVANAGARI LETTER VOCALIC R
            "\u{090C}" => 'li', // DEVANAGARI LETTER VOCALIC L
            "\u{090D}" => 'e', // DEVANAGARI LETTER CANDRA E
            "\u{090E}" => 'e', // DEVANAGARI LETTER SHORT E
            "\u{090F}" => 'e', // DEVANAGARI LETTER E
            "\u{0910}" => 'ai', // DEVANAGARI LETTER AI
            "\u{0911}" => 'o', // DEVANAGARI LETTER CANDRA O
            "\u{0912}" => 'o', // DEVANAGARI LETTER SHORT O
            "\u{0913}" => 'o', // DEVANAGARI LETTER O
            "\u{0914}" => 'au', // DEVANAGARI LETTER AU
            "\u{0960}" => 'ri', // DEVANAGARI LETTER VOCALIC RR
            "\u{0961}" => 'li', // DEVANAGARI LETTER VOCALIC LL
            "\u{0972}" => 'a', // DEVANAGARI LETTER CANDRA A
            "\u{0973}" => 'a', // DEVANAGARI LETTER OE
            "\u{0974}" => 'a', // DEVANAGARI LETTER OOE
            "\u{0975}" => 'a', // DEVANAGARI LETTER AW
            "\u{0976}" => 'u', // DEVANAGARI LETTER UE
            "\u{0977}" => 'u', // DEVANAGARI LETTER UUE
        ],
        'sign' => [
            "\u{093A}" => 'o', // DEVANAGARI VOWEL SIGN OE
            "\u{093B}" => 'a', // DEVANAGARI VOWEL SIGN OOE
            "\u{093E}" => 'a', // DEVANAGARI VOWEL SIGN AA
            "\u{093F}" => 'i', // DEVANAGARI VOWEL SIGN I
            "\u{0940}" => 'i', // DEVANAGARI VOWEL SIGN II
            "\u{0941}" => 'u', // DEVANAGARI VOWEL SIGN U
            "\u{0942}" => 'u', // DEVANAGARI VOWEL SIGN UU
            "\u{0943}" => 'ri', // DEVANAGARI VOWEL SIGN VOCALIC R
            "\u{0944}" => 'ri', // DEVANAGARI VOWEL SIGN VOCALIC RR
            "\u{0945}" => 'e', // DEVANAGARI VOWEL SIGN CANDRA E
            "\u{0946}" => 'e', // DEVANAGARI VOWEL SIGN SHORT E
            "\u{0947}" => 'e', // DEVANAGARI VOWEL SIGN E
            "\u{0948}" => 'ai', // DEVANAGARI VOWEL SIGN AI
            "\u{0949}" => 'o', // DEVANAGARI VOWEL SIGN CANDRA O
            "\u{094A}" => 'o', // DEVANAGARI VOWEL SIGN SHORT O
            "\u{094B}" => 'o', // DEVANAGARI VOWEL SIGN O
            "\u{094C}" => 'au', // DEVANAGARI VOWEL SIGN AU
            "\u{094E}" => 'e', // DEVANAGARI VOWEL SIGN PRISHTHAMATRA E
            "\u{094F}" => 'aw', // DEVANAGARI VOWEL SIGN AW
            "\u{0955}" => 'o', // DEVANAGARI VOWEL SIGN CANDRA LONG E
            "\u{0956}" => 'u', // DEVANAGARI VOWEL SIGN UE
            "\u{0957}" => 'u', // DEVANAGARI VOWEL SIGN UUE
            "\u{0962}" => 'li', // DEVANAGARI VOWEL SIGN VOCALIC L
            "\u{0963}" => 'li', // DEVANAGARI VOWEL SIGN VOCALIC LL
        ],
        'virama' => [
            "\u{094D}" => '', // DEVANAGARI SIGN VIRAMA
        ],
        'anusvara' => [
            "\u{0902}" => '', // DEVANAGARI SIGN ANUSVARA
        ],
        'chandrabindu' => [
            "\u{0901}" => 'n', // DEVANAGARI SIGN CANDRABINDU
        ],
        'visarga' => [
            "\u{0903}" => 'h', // DEVANAGARI SIGN VISARGA
        ],
        'mark' => [
            "\u{093C}" => '', // DEVANAGARI SIGN NUKTA
            "\u{093D}" => '', // DEVANAGARI SIGN AVAGRAHA
            "\u{0951}" => '', // DEVANAGARI STRESS SIGN UDATTA
            "\u{0952}" => '', // DEVANAGARI STRESS SIGN ANUDATTA
            "\u{0953}" => '', // DEVANAGARI GRAVE ACCENT
            "\u{0954}" => '', // DEVANAGARI ACUTE ACCENT
            "\u{0971}" => '', // DEVANAGARI SIGN HIGH SPACING DOT
        ],
        'digit' => [
            "\u{0966}" => '0', // DEVANAGARI DIGIT ZERO
            "\u{0967}" => '1', // DEVANAGARI DIGIT ONE
            "\u{0968}" => '2', // DEVANAGARI DIGIT TWO
            "\u{0969}" => '3', // DEVANAGARI DIGIT THREE
            "\u{096A}" => '4', // DEVANAGARI DIGIT FOUR
            "\u{096B}" => '5', // DEVANAGARI DIGIT FIVE
            "\u{096C}" => '6', // DEVANAGARI DIGIT SIX
            "\u{096D}" => '7', // DEVANAGARI DIGIT SEVEN
            "\u{096E}" => '8', // DEVANAGARI DIGIT EIGHT
            "\u{096F}" => '9', // DEVANAGARI DIGIT NINE
        ],
        'separator' => [
            "\u{0964}" => '', // DEVANAGARI DANDA
            "\u{0965}" => '', // DEVANAGARI DOUBLE DANDA
            "\u{0970}" => '', // DEVANAGARI ABBREVIATION SIGN
        ],
        'other' => [
            "\u{0950}" => 'om', // DEVANAGARI OM
        ],
    ],
];
