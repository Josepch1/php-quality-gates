<?php

declare(strict_types=1);

namespace App\Shared;

use DateTimeImmutable;

/**
 * Reading the current time is I/O. Domain code that calls `new DateTimeImmutable()`
 * directly cannot be tested without waiting for the clock to move, so it asks for
 * this instead.
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}
