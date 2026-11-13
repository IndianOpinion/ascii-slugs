<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

/**
 * One line of audit or repair output.
 */
final class RepairRow
{
    public const AUDIT = 'audit';
    public const DRY_RUN = 'dry-run';
    public const REPAIRED = 'repaired';
    public const SKIPPED = 'skipped';
    public const FAILED = 'failed';

    public function __construct(
        public readonly int $id,
        public readonly string $postType,
        public readonly string $status,
        public readonly string $oldSlug,
        public readonly string $newSlug,
        public readonly string $outcome,
        public readonly string $message = '',
    ) {
    }

    public function bytes(): int
    {
        return strlen($this->oldSlug);
    }

    public function likelyCut(): bool
    {
        return Encoding::isLikelyCut($this->oldSlug);
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(bool $withOutcome): array
    {
        $row = [
            'ID' => $this->id,
            'post_type' => $this->postType,
            'status' => $this->status,
            'bytes' => $this->bytes(),
            'likely_cut' => $this->likelyCut() ? 'yes' : 'no',
            'new_slug' => $this->newSlug,
        ];
        if ($withOutcome) {
            $row['change'] = $this->oldSlug . ' -> ' . $this->newSlug;
            $row['result'] = $this->outcome;
            $row['message'] = $this->message;
        }

        return $row;
    }
}
