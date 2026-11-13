<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal;

/**
 * Index of the built-in tables: names and code point ranges only.
 *
 * The mappings live in `tables/<name>.php` and load on first use. A test
 * checks that these ranges match the ranges stated in each table file.
 *
 * @internal
 */
final class BuiltinTables
{
    /** @var array<string, list<array{0: int, 1: int}>> */
    public const RANGES = [
        'devanagari' => [[0x0900, 0x097F], [0xA8E0, 0xA8FF]],
        'bengali' => [[0x0980, 0x09FF]],
        'gurmukhi' => [[0x0A00, 0x0A7F]],
        'gujarati' => [[0x0A80, 0x0AFF]],
        'odia' => [[0x0B00, 0x0B7F]],
        'tamil' => [[0x0B80, 0x0BFF]],
        'telugu' => [[0x0C00, 0x0C7F]],
        'kannada' => [[0x0C80, 0x0CFF]],
        'malayalam' => [[0x0D00, 0x0D7F]],
        'arabic' => [[0x0600, 0x06FF], [0x0750, 0x077F]],
        'cyrillic' => [[0x0400, 0x04FF], [0x0500, 0x052F]],
        'greek' => [[0x0370, 0x03FF], [0x1F00, 0x1FFF]],
        'latin' => [[0x00C0, 0x024F], [0x1E00, 0x1EFF]],
    ];

    public static function directory(): string
    {
        return dirname(__DIR__, 2) . '/tables';
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::RANGES);
    }
}
