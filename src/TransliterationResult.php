<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

/**
 * What the transliterator made of a text, before the slug rules run.
 */
final class TransliterationResult
{
    /**
     * @param string $text ASCII text, not yet lower-cased or hyphenated.
     * @param int $letters Letters and combining marks in the input.
     * @param int $coveredLetters Of those, the ones a table, ICU or ASCII handled.
     * @param list<string> $scripts Scripts seen, in order of first appearance.
     * @param list<string> $uncoveredScripts Scripts with letters that were dropped.
     * @param bool $usedIntl At least one run went through ICU.
     */
    public function __construct(
        public readonly string $text,
        public readonly int $letters,
        public readonly int $coveredLetters,
        public readonly array $scripts,
        public readonly array $uncoveredScripts,
        public readonly bool $usedIntl,
    ) {
    }

    /**
     * Covered letters divided by all letters, from 0 to 1. A text with no
     * letters at all has coverage 1: nothing in it was lost.
     */
    public function coverage(): float
    {
        if ($this->letters === 0) {
            return 1.0;
        }

        return $this->coveredLetters / $this->letters;
    }
}
