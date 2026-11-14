<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal;

use IndianOpinion\AsciiSlugs\Exception\InvalidInputException;

/**
 * Cleans a title before transliteration (PRD 4.1, steps 1 to 5).
 *
 * @internal
 */
final class TextPreparer
{
    /** Code points removed with no separator left behind (PRD 4.1.4). */
    private const INVISIBLE = '/[\x{200B}\x{200C}\x{200D}\x{2060}\x{FEFF}\x{00AD}\x{0640}'
        . '\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{061C}]/u';

    /** Apostrophes and quotes between two word characters (PRD 4.1.5). */
    private const INNER_QUOTE = '/(?<=[\p{L}\p{M}\p{N}])[\x{0027}\x{2019}\x{02BC}\x{2018}\x{0022}\x{201C}\x{201D}]'
        . '(?=[\p{L}\p{M}\p{N}])/u';

    /**
     * An HTML tag or comment. A bare `<` followed by a digit, a space or
     * nothing that closes it is text, not a tag: `3<5` stays.
     */
    private const TAG = '/<!--.*?-->|<\/?[A-Za-z][^<>]*>/s';

    private const ENTITY_PASSES = 3;

    /**
     * @throws InvalidInputException when the input is not valid UTF-8.
     */
    public static function prepare(string $title): string
    {
        if (!Utf8::isValid($title)) {
            throw new InvalidInputException('The title is not valid UTF-8.');
        }

        $text = $title;
        for ($i = 0; $i < self::ENTITY_PASSES; $i++) {
            $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
            if ($decoded === $text) {
                break;
            }
            $text = $decoded;
        }

        $text = (string) preg_replace(self::TAG, '', $text);
        $text = (string) preg_replace(self::INVISIBLE, '', $text);

        return (string) preg_replace(self::INNER_QUOTE, '', $text);
    }

    /**
     * Keeps at most $limit code points.
     */
    public static function cap(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }
        if (preg_match('/^.{0,' . $limit . '}/us', $text, $m) !== 1) {
            return $text;
        }

        return $m[0];
    }
}
