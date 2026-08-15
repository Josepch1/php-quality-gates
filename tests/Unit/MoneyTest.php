<?php

declare(strict_types=1);

use App\Modules\Billing\Domain\Money;

it('keeps the amount in cents and upper-cases the currency', function (): void {
    $money = Money::of(1050, 'brl');

    expect($money->amountInCents)->toBe(1050)
        ->and($money->currency)->toBe('BRL');
});

it('refuses a negative amount', function (): void {
    Money::of(-1, 'BRL');
})->throws(InvalidArgumentException::class, 'Money cannot be negative.');

it('refuses a currency code that is not three letters', function (string $currency): void {
    Money::of(100, $currency);
})->with(['BR', 'BRLL', ''])->throws(InvalidArgumentException::class);

it('adds two amounts of the same currency', function (): void {
    $sum = Money::of(1000, 'BRL')->add(Money::of(50, 'BRL'));

    expect($sum->amountInCents)->toBe(1050);
});

it('refuses to add different currencies', function (): void {
    Money::of(1000, 'BRL')->add(Money::of(1000, 'USD'));
})->throws(InvalidArgumentException::class, 'Cannot mix BRL and USD.');

it('multiplies by a whole factor', function (): void {
    expect(Money::of(250, 'BRL')->multipliedBy(4)->amountInCents)->toBe(1000);
});

it('refuses a negative factor', function (): void {
    Money::of(250, 'BRL')->multipliedBy(-1);
})->throws(InvalidArgumentException::class, 'Cannot multiply money by a negative factor.');

it('reports zero', function (): void {
    expect(Money::zero('BRL')->isZero())->toBeTrue()
        ->and(Money::of(1, 'BRL')->isZero())->toBeFalse();
});

it('compares amount and currency together', function (): void {
    expect(Money::of(100, 'BRL')->equals(Money::of(100, 'BRL')))->toBeTrue()
        ->and(Money::of(100, 'BRL')->equals(Money::of(101, 'BRL')))->toBeFalse()
        ->and(Money::of(100, 'BRL')->equals(Money::of(100, 'USD')))->toBeFalse();
});

it('leaves the original untouched when adding', function (): void {
    $original = Money::of(1000, 'BRL');
    $original->add(Money::of(500, 'BRL'));

    expect($original->amountInCents)->toBe(1000);
});
