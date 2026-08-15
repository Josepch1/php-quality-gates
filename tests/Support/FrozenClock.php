<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Shared\Clock;
use DateTimeImmutable;

final readonly class FrozenClock implements Clock
{
    public function __construct(private DateTimeImmutable $now) {}

    public static function at(string $iso8601): self
    {
        return new self(new DateTimeImmutable($iso8601));
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
