<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

/**
 * A post whose stored slug holds a percent-encoded high byte.
 */
final class EncodedPost
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $postType,
        public readonly string $status,
        public readonly string $dateGmt,
    ) {
    }
}
