<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

use DateTimeInterface;
use DateTimeZone;
use IndianOpinion\AsciiSlugs\Exception\InvalidInputException;
use IndianOpinion\AsciiSlugs\Exception\SlugCollisionException;
use IndianOpinion\AsciiSlugs\Internal\TextPreparer;
use IndianOpinion\AsciiSlugs\Internal\Utf8;

/**
 * Builds ASCII slugs from titles in any script (PRD 4.3 to 4.5).
 *
 * The slugger is pure: the same title, options and clock give the same
 * slug. It does not check uniqueness; see unique().
 */
final class Slugger
{
    /** Code points transliterated at most (PRD 4.1.6). */
    public const MAX_CODE_POINTS = 1000;

    private SlugOptions $options;

    private ClockInterface $clock;

    private Transliterator $transliterator;

    /**
     * @param Transliterator|null $transliterator Pass one to add custom
     *     tables. Its intl mode and vowel rule are used as given.
     */
    public function __construct(?SlugOptions $options = null, ?ClockInterface $clock = null, ?Transliterator $transliterator = null)
    {
        $this->options = $options ?? new SlugOptions();
        $this->clock = $clock ?? new SystemClock();
        $this->transliterator = $transliterator
            ?? new Transliterator($this->options->intlMode, $this->options->dropFinalInherentVowel);
    }

    public function options(): SlugOptions
    {
        return $this->options;
    }

    public function transliterator(): Transliterator
    {
        return $this->transliterator;
    }

    /**
     * Cleans a title the way slug() does before transliterating: entities
     * decoded, tags stripped, invisible characters and in-word quotes removed.
     *
     * @throws InvalidInputException when the title is not valid UTF-8.
     */
    public function prepare(string $title): string
    {
        return TextPreparer::prepare($title);
    }

    /**
     * Builds the slug for a title.
     *
     * @param DateTimeInterface|null $date Date for the hash fallback. Null
     *     means today from the clock.
     *
     * @throws InvalidInputException when the title is not valid UTF-8.
     */
    public function slug(string $title, ?DateTimeInterface $date = null): SlugResult
    {
        $prepared = TextPreparer::prepare($title);
        $result = $this->transliterator->transliterate(TextPreparer::cap($prepared, self::MAX_CODE_POINTS));
        $coverage = $result->coverage();

        $slug = $this->cut(self::clean($result->text), $this->options->maxLength);

        $readable = preg_match_all('/[a-z0-9]/', $slug) >= $this->options->minAlnum
            && preg_match('/[a-z]/', $slug) === 1
            && $coverage >= $this->options->minCoverage;

        if (!$readable) {
            return new SlugResult(
                $this->hash($prepared, $date),
                SlugResult::METHOD_HASHED,
                $coverage,
                $result->scripts,
            );
        }

        if (Utf8::isAscii($prepared)) {
            $method = SlugResult::METHOD_ASCII;
        } elseif ($result->usedIntl) {
            $method = SlugResult::METHOD_INTL;
        } else {
            $method = SlugResult::METHOD_TRANSLITERATED;
        }

        return new SlugResult($slug, $method, $coverage, $result->scripts);
    }

    /**
     * The hash fallback only (PRD 4.4), over the prepared title.
     *
     * @throws InvalidInputException when the title is not valid UTF-8.
     */
    public function fallback(string $title, ?DateTimeInterface $date = null): string
    {
        return $this->hash(TextPreparer::prepare($title), $date);
    }

    /**
     * The hash fallback over raw bytes, with no preparation. For callers
     * that must produce a slug even from invalid UTF-8.
     */
    public function fallbackForBytes(string $bytes, ?DateTimeInterface $date = null): string
    {
        return $this->hash($bytes, $date);
    }

    /**
     * Makes a slug unique with -2, -3 and so on (PRD 4.5.2).
     *
     * @param callable(string): bool $isTaken
     *
     * @throws SlugCollisionException when every try is taken.
     */
    public function unique(string $slug, callable $isTaken, int $maxTries = 50): string
    {
        $maxTries = max(1, $maxTries);
        if (!$isTaken($slug)) {
            return $slug;
        }
        for ($n = 2; $n <= $maxTries; $n++) {
            $suffix = '-' . $n;
            $base = $this->cut($slug, $this->options->maxLength - strlen($suffix));
            $candidate = $base . $suffix;
            if (!$isTaken($candidate)) {
                return $candidate;
            }
        }

        throw new SlugCollisionException(sprintf('No free slug for "%s" after %d tries.', $slug, $maxTries));
    }

    /**
     * Cleans any string into slug form and fits it to maxLength. Used on
     * values that come back from outside code, such as a filter.
     */
    public function normalise(string $slug): string
    {
        return $this->cut(self::clean($slug), $this->options->maxLength);
    }

    /**
     * Lower-case, `[a-z0-9]` runs joined by single hyphens.
     */
    public static function clean(string $text): string
    {
        $text = strtolower($text);
        $text = (string) preg_replace('/[^a-z0-9]+/', '-', $text);

        return trim($text, '-');
    }

    /**
     * Cuts at the last hyphen at or before $max, or hard at $max when that
     * would leave a stub (PRD 4.3.5).
     */
    private function cut(string $slug, int $max): string
    {
        if (strlen($slug) <= $max) {
            return $slug;
        }
        $hyphen = strrpos(substr($slug, 0, $max + 1), '-');
        if ($hyphen === false || $hyphen < $this->options->minCutPosition) {
            $slug = substr($slug, 0, $max);
        } else {
            $slug = substr($slug, 0, $hyphen);
        }

        return rtrim($slug, '-');
    }

    private function hash(string $text, ?DateTimeInterface $date): string
    {
        $hash = substr(hash('sha256', $text), 0, $this->options->hashLength);
        if (!$this->options->includeDate) {
            return $this->options->fallbackPrefix . '-' . $hash;
        }
        $day = $date === null
            ? $this->clock->today()
            : \DateTimeImmutable::createFromInterface($date);
        $day = $day->setTimezone(new DateTimeZone('UTC'));

        return $this->options->fallbackPrefix . '-' . $day->format('Ymd') . '-' . $hash;
    }
}
