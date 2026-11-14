<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

use InvalidArgumentException;

/**
 * Which posts audit and repair look at.
 */
final class RepairQuery
{
    /**
     * @param list<string> $postTypes
     * @param list<string> $statuses
     * @param list<int>|null $ids
     */
    public function __construct(
        public readonly array $postTypes,
        public readonly array $statuses = ['publish'],
        public readonly ?array $ids = null,
        public readonly int $limit = 100,
        public readonly int $batch = 50,
    ) {
        if ($limit < 1) {
            throw new InvalidArgumentException('--limit must be a positive whole number.');
        }
        if ($batch < 1 || $batch > 1000) {
            throw new InvalidArgumentException('--batch must be from 1 to 1000.');
        }
        foreach (array_merge($postTypes, $statuses) as $name) {
            if (preg_match('/^[a-z0-9_-]{1,20}$/', $name) !== 1) {
                throw new InvalidArgumentException('Post types and statuses must be lower-case keys such as "post" or "publish".');
            }
        }
        foreach ($ids ?? [] as $id) {
            if ($id < 1) {
                throw new InvalidArgumentException('--ids must list positive post IDs.');
            }
        }
    }
}
