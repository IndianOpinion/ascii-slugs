<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

/**
 * The outcome of a repair run.
 */
final class RepairReport
{
    /**
     * @param list<RepairRow> $rows
     */
    public function __construct(public readonly array $rows)
    {
    }

    public function count(string $outcome): int
    {
        return count(array_filter($this->rows, static fn (RepairRow $row): bool => $row->outcome === $outcome));
    }

    public function hasFailures(): bool
    {
        return $this->count(RepairRow::FAILED) > 0;
    }
}
