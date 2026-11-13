<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * A clock that always returns the same moment. Meant for tests.
 */
final class FixedClock implements ClockInterface
{
    private DateTimeImmutable $now;

    public function __construct(DateTimeInterface $now)
    {
        $this->now = DateTimeImmutable::createFromInterface($now)->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Builds a clock from a date string such as "2026-11-12".
     */
    public static function at(string $date): self
    {
        return new self(new DateTimeImmutable($date, new DateTimeZone('UTC')));
    }

    public function today(): DateTimeImmutable
    {
        return $this->now;
    }
}
