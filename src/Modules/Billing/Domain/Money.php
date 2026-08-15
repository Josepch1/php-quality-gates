<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain;

use InvalidArgumentException;

/**
 * Amounts are held in the smallest unit of the currency, so 10.50 BRL is 1050.
 * Floats are never used for money: 0.1 + 0.2 is not 0.3 and an invoice that is
 * off by a cent is a support ticket.
 */
final readonly class Money
{
    private function __construct(
        public int $amountInCents,
        public string $currency,
    ) {}

    public static function of(int $amountInCents, string $currency): self
    {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }

        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter ISO 4217 code.');
        }

        return new self($amountInCents, strtoupper($currency));
    }

    public static function zero(string $currency): self
    {
        return self::of(0, $currency);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    public function multipliedBy(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Cannot multiply money by a negative factor.');
        }

        return new self($this->amountInCents * $factor, $this->currency);
    }

    public function isZero(): bool
    {
        return $this->amountInCents === 0;
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf('Cannot mix %s and %s.', $this->currency, $other->currency),
            );
        }
    }
}
