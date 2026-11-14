<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

use IndianOpinion\AsciiSlugs\Exception\InvalidOptionException;

/**
 * Immutable slug options (PRD section 7). Every field is checked in the
 * constructor; a bad value raises InvalidOptionException.
 */
final class SlugOptions
{
    public const MAX_LENGTH_LIMIT = 190;

    private const FIELDS = [
        'maxLength', 'minCutPosition', 'minAlnum', 'minCoverage', 'fallbackPrefix',
        'hashLength', 'includeDate', 'intlMode', 'dropFinalInherentVowel',
    ];

    /**
     * @throws InvalidOptionException when a value is out of range.
     */
    public function __construct(
        public readonly int $maxLength = 80,
        public readonly int $minCutPosition = 20,
        public readonly int $minAlnum = 3,
        public readonly float $minCoverage = 0.5,
        public readonly string $fallbackPrefix = 'post',
        public readonly int $hashLength = 8,
        public readonly bool $includeDate = true,
        public readonly string $intlMode = Transliterator::INTL_AUTO,
        public readonly bool $dropFinalInherentVowel = true,
    ) {
        if ($maxLength < 20 || $maxLength > self::MAX_LENGTH_LIMIT) {
            throw new InvalidOptionException(sprintf('maxLength must be from 20 to %d, got %d.', self::MAX_LENGTH_LIMIT, $maxLength));
        }
        if ($minCutPosition < 1 || $minCutPosition > $maxLength) {
            throw new InvalidOptionException(sprintf('minCutPosition must be from 1 to maxLength (%d), got %d.', $maxLength, $minCutPosition));
        }
        if ($minAlnum < 1 || $minAlnum > 20) {
            throw new InvalidOptionException(sprintf('minAlnum must be from 1 to 20, got %d.', $minAlnum));
        }
        if (is_nan($minCoverage) || $minCoverage < 0.0 || $minCoverage > 1.0) {
            throw new InvalidOptionException('minCoverage must be from 0.0 to 1.0.');
        }
        if (preg_match('/^[a-z][a-z0-9]{0,19}$/D', $fallbackPrefix) !== 1) {
            throw new InvalidOptionException('fallbackPrefix must match ^[a-z][a-z0-9]{0,19}$.');
        }
        if ($hashLength < 6 || $hashLength > 16) {
            throw new InvalidOptionException(sprintf('hashLength must be from 6 to 16, got %d.', $hashLength));
        }
        if (!in_array($intlMode, [Transliterator::INTL_AUTO, Transliterator::INTL_NEVER, Transliterator::INTL_PREFER], true)) {
            throw new InvalidOptionException('intlMode must be "auto", "never" or "prefer".');
        }
    }

    /**
     * Builds options from an array keyed by field name. Missing keys keep
     * their defaults. Unknown keys and wrong types raise an exception.
     *
     * @param array<mixed> $values
     *
     * @throws InvalidOptionException
     */
    public static function fromArray(array $values): self
    {
        $defaults = (new self())->toArray();
        foreach ($values as $key => $value) {
            if (!is_string($key) || !in_array($key, self::FIELDS, true)) {
                throw new InvalidOptionException(sprintf('Unknown option "%s".', (string) $key));
            }
            $expected = get_debug_type($defaults[$key]);
            if ($expected === 'float' && is_int($value)) {
                $value = (float) $value;
            }
            if (get_debug_type($value) !== $expected) {
                throw new InvalidOptionException(sprintf('Option "%s" must be of type %s.', $key, $expected));
            }
            $defaults[$key] = $value;
        }

        return new self(
            maxLength: self::int($defaults['maxLength']),
            minCutPosition: self::int($defaults['minCutPosition']),
            minAlnum: self::int($defaults['minAlnum']),
            minCoverage: is_float($defaults['minCoverage']) ? $defaults['minCoverage'] : 0.5,
            fallbackPrefix: (string) $defaults['fallbackPrefix'],
            hashLength: self::int($defaults['hashLength']),
            includeDate: $defaults['includeDate'] === true,
            intlMode: (string) $defaults['intlMode'],
            dropFinalInherentVowel: $defaults['dropFinalInherentVowel'] === true,
        );
    }

    /**
     * @return array{
     *     maxLength: int,
     *     minCutPosition: int,
     *     minAlnum: int,
     *     minCoverage: float,
     *     fallbackPrefix: string,
     *     hashLength: int,
     *     includeDate: bool,
     *     intlMode: string,
     *     dropFinalInherentVowel: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'maxLength' => $this->maxLength,
            'minCutPosition' => $this->minCutPosition,
            'minAlnum' => $this->minAlnum,
            'minCoverage' => $this->minCoverage,
            'fallbackPrefix' => $this->fallbackPrefix,
            'hashLength' => $this->hashLength,
            'includeDate' => $this->includeDate,
            'intlMode' => $this->intlMode,
            'dropFinalInherentVowel' => $this->dropFinalInherentVowel,
        ];
    }

    public function withMaxLength(int $maxLength): self
    {
        return self::fromArray(['maxLength' => $maxLength, 'minCutPosition' => min($this->minCutPosition, max(1, $maxLength))] + $this->toArray());
    }

    public function withMinCutPosition(int $minCutPosition): self
    {
        return self::fromArray(['minCutPosition' => $minCutPosition] + $this->toArray());
    }

    public function withMinAlnum(int $minAlnum): self
    {
        return self::fromArray(['minAlnum' => $minAlnum] + $this->toArray());
    }

    public function withMinCoverage(float $minCoverage): self
    {
        return self::fromArray(['minCoverage' => $minCoverage] + $this->toArray());
    }

    public function withFallbackPrefix(string $fallbackPrefix): self
    {
        return self::fromArray(['fallbackPrefix' => $fallbackPrefix] + $this->toArray());
    }

    public function withHashLength(int $hashLength): self
    {
        return self::fromArray(['hashLength' => $hashLength] + $this->toArray());
    }

    public function withIncludeDate(bool $includeDate): self
    {
        return self::fromArray(['includeDate' => $includeDate] + $this->toArray());
    }

    public function withIntlMode(string $intlMode): self
    {
        return self::fromArray(['intlMode' => $intlMode] + $this->toArray());
    }

    public function withDropFinalInherentVowel(bool $drop): self
    {
        return self::fromArray(['dropFinalInherentVowel' => $drop] + $this->toArray());
    }

    private static function int(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
