<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

use DateTimeImmutable;
use DateTimeZone;

/**
 * The system clock, in UTC.
 */
final class SystemClock implements ClockInterface
{
    public function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
