<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

use DateTimeImmutable;

/**
 * Supplies the date used in the hash fallback.
 */
interface ClockInterface
{
    /**
     * The current moment, in UTC.
     */
    public function today(): DateTimeImmutable;
}
