<?php

/**
 * Transliteration table: Cyrillic (Russian, Ukrainian, Bulgarian, Serbian, Macedonian, Belarusian).
 *
 * Keys are code point sequences written as escapes, values are ASCII.
 * See docs/ARCHITECTURE.md for the rules each class follows.
 */

declare(strict_types=1);

return [
    'name' => 'cyrillic',
    'type' => 'alphabet',
    'ranges' => [
        [0x0400, 0x04FF],
        [0x0500, 0x052F],
    ],
    'rules' => [
        'dropCombiningDiacritics' => true,
    ],
    'classes' => [
        'letter' => [
            "\u{0400}" => 'e', // CYRILLIC CAPITAL LETTER IE WITH GRAVE
            "\u{0401}" => 'e', // CYRILLIC CAPITAL LETTER IO
            "\u{0402}" => 'dj', // CYRILLIC CAPITAL LETTER DJE
            "\u{0403}" => 'gj', // CYRILLIC CAPITAL LETTER GJE
            "\u{0404}" => 'ye', // CYRILLIC CAPITAL LETTER UKRAINIAN IE
            "\u{0405}" => 'dz', // CYRILLIC CAPITAL LETTER DZE
            "\u{0406}" => 'i', // CYRILLIC CAPITAL LETTER BYELORUSSIAN-UKRAINIAN I
            "\u{0406}\u{0308}" => 'yi', // CYRILLIC CAPITAL LETTER BYELORUSSIAN-UKRAINIAN I + COMBINING DIAERESIS
            "\u{0407}" => 'yi', // CYRILLIC CAPITAL LETTER YI
            "\u{0408}" => 'j', // CYRILLIC CAPITAL LETTER JE
            "\u{0409}" => 'lj', // CYRILLIC CAPITAL LETTER LJE
            "\u{040A}" => 'nj', // CYRILLIC CAPITAL LETTER NJE
            "\u{040B}" => 'c', // CYRILLIC CAPITAL LETTER TSHE
            "\u{040C}" => 'kj', // CYRILLIC CAPITAL LETTER KJE
            "\u{040D}" => 'i', // CYRILLIC CAPITAL LETTER I WITH GRAVE
            "\u{040E}" => 'u', // CYRILLIC CAPITAL LETTER SHORT U
            "\u{040F}" => 'dz', // CYRILLIC CAPITAL LETTER DZHE
            "\u{0410}" => 'a', // CYRILLIC CAPITAL LETTER A
            "\u{0411}" => 'b', // CYRILLIC CAPITAL LETTER BE
            "\u{0412}" => 'v', // CYRILLIC CAPITAL LETTER VE
            "\u{0413}" => 'g', // CYRILLIC CAPITAL LETTER GHE
            "\u{0413}\u{0301}" => 'gj', // CYRILLIC CAPITAL LETTER GHE + COMBINING ACUTE ACCENT
            "\u{0414}" => 'd', // CYRILLIC CAPITAL LETTER DE
            "\u{0415}" => 'e', // CYRILLIC CAPITAL LETTER IE
            "\u{0415}\u{0300}" => 'e', // CYRILLIC CAPITAL LETTER IE + COMBINING GRAVE ACCENT
            "\u{0415}\u{0308}" => 'e', // CYRILLIC CAPITAL LETTER IE + COMBINING DIAERESIS
            "\u{0416}" => 'zh', // CYRILLIC CAPITAL LETTER ZHE
            "\u{0417}" => 'z', // CYRILLIC CAPITAL LETTER ZE
            "\u{0418}" => 'i', // CYRILLIC CAPITAL LETTER I
            "\u{0418}\u{0300}" => 'i', // CYRILLIC CAPITAL LETTER I + COMBINING GRAVE ACCENT
            "\u{0418}\u{0306}" => 'y', // CYRILLIC CAPITAL LETTER I + COMBINING BREVE
            "\u{0419}" => 'y', // CYRILLIC CAPITAL LETTER SHORT I
            "\u{041A}" => 'k', // CYRILLIC CAPITAL LETTER KA
            "\u{041A}\u{0301}" => 'kj', // CYRILLIC CAPITAL LETTER KA + COMBINING ACUTE ACCENT
            "\u{041B}" => 'l', // CYRILLIC CAPITAL LETTER EL
            "\u{041C}" => 'm', // CYRILLIC CAPITAL LETTER EM
            "\u{041D}" => 'n', // CYRILLIC CAPITAL LETTER EN
            "\u{041E}" => 'o', // CYRILLIC CAPITAL LETTER O
            "\u{041F}" => 'p', // CYRILLIC CAPITAL LETTER PE
            "\u{0420}" => 'r', // CYRILLIC CAPITAL LETTER ER
            "\u{0421}" => 's', // CYRILLIC CAPITAL LETTER ES
            "\u{0422}" => 't', // CYRILLIC CAPITAL LETTER TE
            "\u{0423}" => 'u', // CYRILLIC CAPITAL LETTER U
            "\u{0423}\u{0306}" => 'u', // CYRILLIC CAPITAL LETTER U + COMBINING BREVE
            "\u{0424}" => 'f', // CYRILLIC CAPITAL LETTER EF
            "\u{0425}" => 'kh', // CYRILLIC CAPITAL LETTER HA
            "\u{0426}" => 'ts', // CYRILLIC CAPITAL LETTER TSE
            "\u{0427}" => 'ch', // CYRILLIC CAPITAL LETTER CHE
            "\u{0428}" => 'sh', // CYRILLIC CAPITAL LETTER SHA
            "\u{0429}" => 'shch', // CYRILLIC CAPITAL LETTER SHCHA
            "\u{042A}" => '', // CYRILLIC CAPITAL LETTER HARD SIGN
            "\u{042B}" => 'y', // CYRILLIC CAPITAL LETTER YERU
            "\u{042C}" => '', // CYRILLIC CAPITAL LETTER SOFT SIGN
            "\u{042D}" => 'e', // CYRILLIC CAPITAL LETTER E
            "\u{042E}" => 'yu', // CYRILLIC CAPITAL LETTER YU
            "\u{042F}" => 'ya', // CYRILLIC CAPITAL LETTER YA
            "\u{0430}" => 'a', // CYRILLIC SMALL LETTER A
            "\u{0431}" => 'b', // CYRILLIC SMALL LETTER BE
            "\u{0432}" => 'v', // CYRILLIC SMALL LETTER VE
            "\u{0433}" => 'g', // CYRILLIC SMALL LETTER GHE
            "\u{0433}\u{0301}" => 'gj', // CYRILLIC SMALL LETTER GHE + COMBINING ACUTE ACCENT
            "\u{0434}" => 'd', // CYRILLIC SMALL LETTER DE
            "\u{0435}" => 'e', // CYRILLIC SMALL LETTER IE
            "\u{0435}\u{0300}" => 'e', // CYRILLIC SMALL LETTER IE + COMBINING GRAVE ACCENT
            "\u{0435}\u{0308}" => 'e', // CYRILLIC SMALL LETTER IE + COMBINING DIAERESIS
            "\u{0436}" => 'zh', // CYRILLIC SMALL LETTER ZHE
            "\u{0437}" => 'z', // CYRILLIC SMALL LETTER ZE
            "\u{0438}" => 'i', // CYRILLIC SMALL LETTER I
            "\u{0438}\u{0300}" => 'i', // CYRILLIC SMALL LETTER I + COMBINING GRAVE ACCENT
            "\u{0438}\u{0306}" => 'y', // CYRILLIC SMALL LETTER I + COMBINING BREVE
            "\u{0439}" => 'y', // CYRILLIC SMALL LETTER SHORT I
            "\u{043A}" => 'k', // CYRILLIC SMALL LETTER KA
            "\u{043A}\u{0301}" => 'kj', // CYRILLIC SMALL LETTER KA + COMBINING ACUTE ACCENT
            "\u{043B}" => 'l', // CYRILLIC SMALL LETTER EL
            "\u{043C}" => 'm', // CYRILLIC SMALL LETTER EM
            "\u{043D}" => 'n', // CYRILLIC SMALL LETTER EN
            "\u{043E}" => 'o', // CYRILLIC SMALL LETTER O
            "\u{043F}" => 'p', // CYRILLIC SMALL LETTER PE
            "\u{0440}" => 'r', // CYRILLIC SMALL LETTER ER
            "\u{0441}" => 's', // CYRILLIC SMALL LETTER ES
            "\u{0442}" => 't', // CYRILLIC SMALL LETTER TE
            "\u{0443}" => 'u', // CYRILLIC SMALL LETTER U
            "\u{0443}\u{0306}" => 'u', // CYRILLIC SMALL LETTER U + COMBINING BREVE
            "\u{0444}" => 'f', // CYRILLIC SMALL LETTER EF
            "\u{0445}" => 'kh', // CYRILLIC SMALL LETTER HA
            "\u{0446}" => 'ts', // CYRILLIC SMALL LETTER TSE
            "\u{0447}" => 'ch', // CYRILLIC SMALL LETTER CHE
            "\u{0448}" => 'sh', // CYRILLIC SMALL LETTER SHA
            "\u{0449}" => 'shch', // CYRILLIC SMALL LETTER SHCHA
            "\u{044A}" => '', // CYRILLIC SMALL LETTER HARD SIGN
            "\u{044B}" => 'y', // CYRILLIC SMALL LETTER YERU
            "\u{044C}" => '', // CYRILLIC SMALL LETTER SOFT SIGN
            "\u{044D}" => 'e', // CYRILLIC SMALL LETTER E
            "\u{044E}" => 'yu', // CYRILLIC SMALL LETTER YU
            "\u{044F}" => 'ya', // CYRILLIC SMALL LETTER YA
            "\u{0450}" => 'e', // CYRILLIC SMALL LETTER IE WITH GRAVE
            "\u{0451}" => 'e', // CYRILLIC SMALL LETTER IO
            "\u{0452}" => 'dj', // CYRILLIC SMALL LETTER DJE
            "\u{0453}" => 'gj', // CYRILLIC SMALL LETTER GJE
            "\u{0454}" => 'ye', // CYRILLIC SMALL LETTER UKRAINIAN IE
            "\u{0455}" => 'dz', // CYRILLIC SMALL LETTER DZE
            "\u{0456}" => 'i', // CYRILLIC SMALL LETTER BYELORUSSIAN-UKRAINIAN I
            "\u{0456}\u{0308}" => 'yi', // CYRILLIC SMALL LETTER BYELORUSSIAN-UKRAINIAN I + COMBINING DIAERESIS
            "\u{0457}" => 'yi', // CYRILLIC SMALL LETTER YI
            "\u{0458}" => 'j', // CYRILLIC SMALL LETTER JE
            "\u{0459}" => 'lj', // CYRILLIC SMALL LETTER LJE
            "\u{045A}" => 'nj', // CYRILLIC SMALL LETTER NJE
            "\u{045B}" => 'c', // CYRILLIC SMALL LETTER TSHE
            "\u{045C}" => 'kj', // CYRILLIC SMALL LETTER KJE
            "\u{045D}" => 'i', // CYRILLIC SMALL LETTER I WITH GRAVE
            "\u{045E}" => 'u', // CYRILLIC SMALL LETTER SHORT U
            "\u{045F}" => 'dz', // CYRILLIC SMALL LETTER DZHE
            "\u{0490}" => 'g', // CYRILLIC CAPITAL LETTER GHE WITH UPTURN
            "\u{0491}" => 'g', // CYRILLIC SMALL LETTER GHE WITH UPTURN
        ],
    ],
];
