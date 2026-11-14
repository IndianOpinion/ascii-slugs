<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal;

/**
 * One matched sequence inside a run.
 *
 * @internal
 */
final class Token
{
    public function __construct(
        public readonly string $sequence,
        public readonly ?string $class,
        public readonly string $output,
        public readonly int $letters,
    ) {
    }
}
