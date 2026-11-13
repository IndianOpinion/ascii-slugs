<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

/**
 * A slug and how it was made.
 */
final class SlugResult
{
    /** The input was already plain ASCII. */
    public const METHOD_ASCII = 'ascii';

    /** Built-in tables made the slug. */
    public const METHOD_TRANSLITERATED = 'transliterated';

    /** At least one run went through ICU. */
    public const METHOD_INTL = 'intl';

    /** The readability floor failed, so this is the hash fallback. */
    public const METHOD_HASHED = 'hashed';

    /**
     * @param list<string> $scripts
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $method,
        public readonly float $coverage,
        public readonly array $scripts,
    ) {
    }

    public function isHashed(): bool
    {
        return $this->method === self::METHOD_HASHED;
    }
}
