<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

/**
 * Checks for slugs that WordPress has percent-encoded.
 */
final class Encoding
{
    /** A percent-encoded byte of 0x80 or higher. */
    public const HIGH_BYTE_PATTERN = '/%[89a-f][0-9a-f]/i';

    /** The same test in MySQL REGEXP syntax. */
    public const HIGH_BYTE_SQL = '%[89a-fA-F][0-9a-fA-F]';

    /** WordPress cuts encoded slugs at 200 bytes, on a character boundary. */
    public const COLUMN_BYTES = 200;

    /** A Devanagari code point costs 9 encoded bytes, so a cut slug is at least 192. */
    public const LIKELY_CUT_BYTES = 192;

    public static function hasEncodedHighByte(string $slug): bool
    {
        return preg_match(self::HIGH_BYTE_PATTERN, $slug) === 1;
    }

    public static function isAscii(string $text): bool
    {
        return preg_match('/[\x80-\xFF]/', $text) === 0;
    }

    public static function isLikelyCut(string $slug): bool
    {
        return strlen($slug) >= self::LIKELY_CUT_BYTES;
    }
}
