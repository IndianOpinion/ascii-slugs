<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal;

/**
 * Small UTF-8 helpers that need neither mbstring nor intl.
 *
 * @internal
 */
final class Utf8
{
    public static function isValid(string $text): bool
    {
        return preg_match('//u', $text) === 1;
    }

    public static function isAscii(string $text): bool
    {
        return preg_match('/[\x80-\xFF]/', $text) === 0;
    }

    /**
     * Splits valid UTF-8 into code points.
     *
     * @return list<string>
     */
    public static function split(string $text): array
    {
        if ($text === '') {
            return [];
        }
        $parts = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    /**
     * Number of code points in valid UTF-8, counted from lead bytes.
     */
    public static function length(string $text): int
    {
        $count = 0;
        $bytes = strlen($text);
        for ($i = 0; $i < $bytes; $i++) {
            if ((ord($text[$i]) & 0xC0) !== 0x80) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * The first character of valid UTF-8.
     */
    public static function first(string $text): string
    {
        $lead = ord($text[0]);
        $size = $lead < 0x80 ? 1 : ($lead < 0xE0 ? 2 : ($lead < 0xF0 ? 3 : 4));

        return substr($text, 0, $size);
    }

    /**
     * The code point of one UTF-8 encoded character.
     */
    public static function ord(string $char): int
    {
        $b0 = ord($char[0]);
        if ($b0 < 0x80) {
            return $b0;
        }
        if ($b0 < 0xE0) {
            return (($b0 & 0x1F) << 6) | (ord($char[1]) & 0x3F);
        }
        if ($b0 < 0xF0) {
            return (($b0 & 0x0F) << 12) | ((ord($char[1]) & 0x3F) << 6) | (ord($char[2]) & 0x3F);
        }

        return (($b0 & 0x07) << 18) | ((ord($char[1]) & 0x3F) << 12)
            | ((ord($char[2]) & 0x3F) << 6) | (ord($char[3]) & 0x3F);
    }
}
