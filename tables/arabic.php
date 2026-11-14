<?php

/**
 * Transliteration table: Arabic script with Urdu rules (Urdu, Kashmiri, Sindhi, Arabic, Persian).
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'arabic',
    'type' => 'abjad',
    'ranges' => [
        [0x0600, 0x06FF],
        [0x0750, 0x077F],
    ],
    'rules' => [
        'exceptions' => 'urdu-words.php',
    ],
    'classes' => [
        'consonant' => [
            "\u{0628}" => 'b', // ARABIC LETTER BEH
            "\u{0629}" => 'h', // ARABIC LETTER TEH MARBUTA
            "\u{062A}" => 't', // ARABIC LETTER TEH
            "\u{062B}" => 's', // ARABIC LETTER THEH
            "\u{062C}" => 'j', // ARABIC LETTER JEEM
            "\u{062D}" => 'h', // ARABIC LETTER HAH
            "\u{062E}" => 'kh', // ARABIC LETTER KHAH
            "\u{062F}" => 'd', // ARABIC LETTER DAL
            "\u{0630}" => 'z', // ARABIC LETTER THAL
            "\u{0631}" => 'r', // ARABIC LETTER REH
            "\u{0632}" => 'z', // ARABIC LETTER ZAIN
            "\u{0633}" => 's', // ARABIC LETTER SEEN
            "\u{0634}" => 'sh', // ARABIC LETTER SHEEN
            "\u{0635}" => 's', // ARABIC LETTER SAD
            "\u{0636}" => 'z', // ARABIC LETTER DAD
            "\u{0637}" => 't', // ARABIC LETTER TAH
            "\u{0638}" => 'z', // ARABIC LETTER ZAH
            "\u{063A}" => 'gh', // ARABIC LETTER GHAIN
            "\u{0641}" => 'f', // ARABIC LETTER FEH
            "\u{0642}" => 'q', // ARABIC LETTER QAF
            "\u{0643}" => 'k', // ARABIC LETTER KAF
            "\u{0644}" => 'l', // ARABIC LETTER LAM
            "\u{0645}" => 'm', // ARABIC LETTER MEEM
            "\u{0646}" => 'n', // ARABIC LETTER NOON
            "\u{0647}" => 'h', // ARABIC LETTER HEH
            "\u{0679}" => 't', // ARABIC LETTER TTEH
            "\u{067A}" => 'th', // ARABIC LETTER TTEHEH
            "\u{067B}" => 'b', // ARABIC LETTER BEEH
            "\u{067E}" => 'p', // ARABIC LETTER PEH
            "\u{067F}" => 'th', // ARABIC LETTER TEHEH
            "\u{0680}" => 'bh', // ARABIC LETTER BEHEH
            "\u{0683}" => 'ny', // ARABIC LETTER NYEH
            "\u{0684}" => 'j', // ARABIC LETTER DYEH
            "\u{0686}" => 'ch', // ARABIC LETTER TCHEH
            "\u{0687}" => 'chh', // ARABIC LETTER TCHEHEH
            "\u{0688}" => 'd', // ARABIC LETTER DDAL
            "\u{068A}" => 'd', // ARABIC LETTER DAL WITH DOT BELOW
            "\u{068C}" => 'dh', // ARABIC LETTER DAHAL
            "\u{068D}" => 'd', // ARABIC LETTER DDAHAL
            "\u{068F}" => 'dh', // ARABIC LETTER DAL WITH THREE DOTS ABOVE DOWNWARDS
            "\u{0691}" => 'r', // ARABIC LETTER RREH
            "\u{0698}" => 'zh', // ARABIC LETTER JEH
            "\u{0699}" => 'r', // ARABIC LETTER REH WITH FOUR DOTS ABOVE
            "\u{06A0}" => 'gh', // ARABIC LETTER AIN WITH THREE DOTS ABOVE
            "\u{06A4}" => 'v', // ARABIC LETTER VEH
            "\u{06A6}" => 'ph', // ARABIC LETTER PEHEH
            "\u{06A9}" => 'k', // ARABIC LETTER KEHEH
            "\u{06AA}" => 'k', // ARABIC LETTER SWASH KAF
            "\u{06AF}" => 'g', // ARABIC LETTER GAF
            "\u{06B1}" => 'ng', // ARABIC LETTER NGOEH
            "\u{06B3}" => 'g', // ARABIC LETTER GUEH
            "\u{06B5}" => 'l', // ARABIC LETTER LAM WITH SMALL V
            "\u{06BA}" => 'n', // ARABIC LETTER NOON GHUNNA
            "\u{06BB}" => 'n', // ARABIC LETTER RNOON
            "\u{06BE}" => 'h', // ARABIC LETTER HEH DOACHASHMEE
            "\u{06C0}" => 'h', // ARABIC LETTER HEH WITH YEH ABOVE
            "\u{06C1}" => 'h', // ARABIC LETTER HEH GOAL
            "\u{06C1}\u{0654}" => 'h', // ARABIC LETTER HEH GOAL + ARABIC HAMZA ABOVE
            "\u{06C2}" => 'h', // ARABIC LETTER HEH GOAL WITH HAMZA ABOVE
            "\u{06C3}" => 'h', // ARABIC LETTER TEH MARBUTA GOAL
            "\u{06C6}" => 'o', // ARABIC LETTER OE
            "\u{06C7}" => 'u', // ARABIC LETTER U
            "\u{06C8}" => 'u', // ARABIC LETTER YU
            "\u{06CB}" => 'v', // ARABIC LETTER VE
            "\u{06D5}" => 'h', // ARABIC LETTER AE
            "\u{06D5}\u{0654}" => 'h', // ARABIC LETTER AE + ARABIC HAMZA ABOVE
        ],
        'vowel' => [
            "\u{0622}" => 'a', // ARABIC LETTER ALEF WITH MADDA ABOVE
            "\u{0623}" => 'a', // ARABIC LETTER ALEF WITH HAMZA ABOVE
            "\u{0624}" => 'o', // ARABIC LETTER WAW WITH HAMZA ABOVE
            "\u{0625}" => 'i', // ARABIC LETTER ALEF WITH HAMZA BELOW
            "\u{0627}" => 'a', // ARABIC LETTER ALEF
            "\u{0627}\u{0653}" => 'a', // ARABIC LETTER ALEF + ARABIC MADDAH ABOVE
            "\u{0627}\u{0654}" => 'a', // ARABIC LETTER ALEF + ARABIC HAMZA ABOVE
            "\u{0627}\u{0655}" => 'i', // ARABIC LETTER ALEF + ARABIC HAMZA BELOW
            "\u{0648}\u{0654}" => 'o', // ARABIC LETTER WAW + ARABIC HAMZA ABOVE
            "\u{0671}" => 'a', // ARABIC LETTER ALEF WASLA
            "\u{06D2}" => 'e', // ARABIC LETTER YEH BARREE
            "\u{06D2}\u{0654}" => 'e', // ARABIC LETTER YEH BARREE + ARABIC HAMZA ABOVE
            "\u{06D3}" => 'e', // ARABIC LETTER YEH BARREE WITH HAMZA ABOVE
        ],
        'yeh' => [
            "\u{0649}" => '', // ARABIC LETTER ALEF MAKSURA
            "\u{064A}" => '', // ARABIC LETTER YEH
            "\u{06CC}" => '', // ARABIC LETTER FARSI YEH
            "\u{06D0}" => '', // ARABIC LETTER E
        ],
        'waw' => [
            "\u{0648}" => '', // ARABIC LETTER WAW
        ],
        'harakat' => [
            "\u{064E}" => 'a', // ARABIC FATHA
            "\u{064F}" => 'u', // ARABIC DAMMA
            "\u{0650}" => 'i', // ARABIC KASRA
            "\u{0656}" => 'i', // ARABIC SUBSCRIPT ALEF
            "\u{0657}" => 'u', // ARABIC INVERTED DAMMA
            "\u{0670}" => 'a', // ARABIC LETTER SUPERSCRIPT ALEF
        ],
        'shadda' => [
            "\u{0651}" => '', // ARABIC SHADDA
        ],
        'mark' => [
            "\u{0610}" => '', // ARABIC SIGN SALLALLAHOU ALAYHE WASSALLAM
            "\u{0611}" => '', // ARABIC SIGN ALAYHE ASSALLAM
            "\u{0612}" => '', // ARABIC SIGN RAHMATULLAH ALAYHE
            "\u{0613}" => '', // ARABIC SIGN RADI ALLAHOU ANHU
            "\u{0614}" => '', // ARABIC SIGN TAKHALLUS
            "\u{0615}" => '', // ARABIC SMALL HIGH TAH
            "\u{0616}" => '', // ARABIC SMALL HIGH LIGATURE ALEF WITH LAM WITH YEH
            "\u{0617}" => '', // ARABIC SMALL HIGH ZAIN
            "\u{0618}" => '', // ARABIC SMALL FATHA
            "\u{0619}" => '', // ARABIC SMALL DAMMA
            "\u{061A}" => '', // ARABIC SMALL KASRA
            "\u{0621}" => '', // ARABIC LETTER HAMZA
            "\u{0626}" => '', // ARABIC LETTER YEH WITH HAMZA ABOVE
            "\u{0639}" => '', // ARABIC LETTER AIN
            "\u{064A}\u{0654}" => '', // ARABIC LETTER YEH + ARABIC HAMZA ABOVE
            "\u{064B}" => '', // ARABIC FATHATAN
            "\u{064C}" => '', // ARABIC DAMMATAN
            "\u{064D}" => '', // ARABIC KASRATAN
            "\u{0652}" => '', // ARABIC SUKUN
            "\u{0653}" => '', // ARABIC MADDAH ABOVE
            "\u{0654}" => '', // ARABIC HAMZA ABOVE
            "\u{0655}" => '', // ARABIC HAMZA BELOW
            "\u{0658}" => '', // ARABIC MARK NOON GHUNNA
            "\u{0659}" => '', // ARABIC ZWARAKAY
            "\u{065A}" => '', // ARABIC VOWEL SIGN SMALL V ABOVE
            "\u{065B}" => '', // ARABIC VOWEL SIGN INVERTED SMALL V ABOVE
            "\u{065C}" => '', // ARABIC VOWEL SIGN DOT BELOW
            "\u{065D}" => '', // ARABIC REVERSED DAMMA
            "\u{065E}" => '', // ARABIC FATHA WITH TWO DOTS
            "\u{065F}" => '', // ARABIC WAVY HAMZA BELOW
            "\u{06CC}\u{0654}" => '', // ARABIC LETTER FARSI YEH + ARABIC HAMZA ABOVE
            "\u{06D6}" => '', // ARABIC SMALL HIGH LIGATURE SAD WITH LAM WITH ALEF MAKSURA
            "\u{06D7}" => '', // ARABIC SMALL HIGH LIGATURE QAF WITH LAM WITH ALEF MAKSURA
            "\u{06D8}" => '', // ARABIC SMALL HIGH MEEM INITIAL FORM
            "\u{06D9}" => '', // ARABIC SMALL HIGH LAM ALEF
            "\u{06DA}" => '', // ARABIC SMALL HIGH JEEM
            "\u{06DB}" => '', // ARABIC SMALL HIGH THREE DOTS
            "\u{06DC}" => '', // ARABIC SMALL HIGH SEEN
            "\u{06DF}" => '', // ARABIC SMALL HIGH ROUNDED ZERO
            "\u{06E0}" => '', // ARABIC SMALL HIGH UPRIGHT RECTANGULAR ZERO
            "\u{06E1}" => '', // ARABIC SMALL HIGH DOTLESS HEAD OF KHAH
            "\u{06E2}" => '', // ARABIC SMALL HIGH MEEM ISOLATED FORM
            "\u{06E3}" => '', // ARABIC SMALL LOW SEEN
            "\u{06E4}" => '', // ARABIC SMALL HIGH MADDA
            "\u{06E7}" => '', // ARABIC SMALL HIGH YEH
            "\u{06E8}" => '', // ARABIC SMALL HIGH NOON
            "\u{06EA}" => '', // ARABIC EMPTY CENTRE LOW STOP
            "\u{06EB}" => '', // ARABIC EMPTY CENTRE HIGH STOP
            "\u{06EC}" => '', // ARABIC ROUNDED HIGH STOP WITH FILLED CENTRE
            "\u{06ED}" => '', // ARABIC SMALL LOW MEEM
        ],
        'digit' => [
            "\u{0660}" => '0', // ARABIC-INDIC DIGIT ZERO
            "\u{0661}" => '1', // ARABIC-INDIC DIGIT ONE
            "\u{0662}" => '2', // ARABIC-INDIC DIGIT TWO
            "\u{0663}" => '3', // ARABIC-INDIC DIGIT THREE
            "\u{0664}" => '4', // ARABIC-INDIC DIGIT FOUR
            "\u{0665}" => '5', // ARABIC-INDIC DIGIT FIVE
            "\u{0666}" => '6', // ARABIC-INDIC DIGIT SIX
            "\u{0667}" => '7', // ARABIC-INDIC DIGIT SEVEN
            "\u{0668}" => '8', // ARABIC-INDIC DIGIT EIGHT
            "\u{0669}" => '9', // ARABIC-INDIC DIGIT NINE
            "\u{06F0}" => '0', // EXTENDED ARABIC-INDIC DIGIT ZERO
            "\u{06F1}" => '1', // EXTENDED ARABIC-INDIC DIGIT ONE
            "\u{06F2}" => '2', // EXTENDED ARABIC-INDIC DIGIT TWO
            "\u{06F3}" => '3', // EXTENDED ARABIC-INDIC DIGIT THREE
            "\u{06F4}" => '4', // EXTENDED ARABIC-INDIC DIGIT FOUR
            "\u{06F5}" => '5', // EXTENDED ARABIC-INDIC DIGIT FIVE
            "\u{06F6}" => '6', // EXTENDED ARABIC-INDIC DIGIT SIX
            "\u{06F7}" => '7', // EXTENDED ARABIC-INDIC DIGIT SEVEN
            "\u{06F8}" => '8', // EXTENDED ARABIC-INDIC DIGIT EIGHT
            "\u{06F9}" => '9', // EXTENDED ARABIC-INDIC DIGIT NINE
        ],
        'separator' => [
            "\u{060C}" => '', // ARABIC COMMA
            "\u{060D}" => '', // ARABIC DATE SEPARATOR
            "\u{061B}" => '', // ARABIC SEMICOLON
            "\u{061E}" => '', // ARABIC TRIPLE DOT PUNCTUATION MARK
            "\u{061F}" => '', // ARABIC QUESTION MARK
            "\u{066A}" => '', // ARABIC PERCENT SIGN
            "\u{066B}" => '', // ARABIC DECIMAL SEPARATOR
            "\u{066C}" => '', // ARABIC THOUSANDS SEPARATOR
            "\u{066D}" => '', // ARABIC FIVE POINTED STAR
            "\u{06D4}" => '', // ARABIC FULL STOP
        ],
    ],
];
