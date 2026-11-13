<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

/**
 * Read access to posts with percent-encoded slugs.
 */
interface PostSource
{
    /**
     * One batch of posts with encoded slugs, ordered by ID, with ID above
     * $afterId.
     *
     * @param list<string> $postTypes
     * @param list<string> $statuses
     * @param list<int>|null $ids Only these IDs, when given.
     *
     * @return list<EncodedPost>
     */
    public function encodedPosts(array $postTypes, array $statuses, ?array $ids, int $afterId, int $batch): array;

    /**
     * How many posts have encoded slugs.
     *
     * @param list<string> $postTypes
     * @param list<string> $statuses
     */
    public function countEncoded(array $postTypes, array $statuses): int;
}
