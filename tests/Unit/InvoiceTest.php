<?php

declare(strict_types=1);

use App\Modules\Billing\Domain\InvalidInvoiceTransition;
use App\Modules\Billing\Domain\Invoice;
use App\Modules\Billing\Domain\InvoiceStatus;
use App\Modules\Billing\Domain\Money;

covers(Invoice::class);
covers(InvalidInvoiceTransition::class);

function draftInvoice(int $cents = 1000): Invoice
{
    return new Invoice('INV-1', Money::of($cents, 'BRL'));
}

it('starts as a draft with no dates', function (): void {
    $invoice = draftInvoice();

    expect($invoice->status())->toBe(InvoiceStatus::Draft)
        ->and($invoice->issuedAt())->toBeNull()
        ->and($invoice->paidAt())->toBeNull();
});

it('records the moment it was issued', function (): void {
    $at = new DateTimeImmutable('2026-08-15 10:00:00');
    $invoice = draftInvoice();

    $invoice->issue($at);

    expect($invoice->status())->toBe(InvoiceStatus::Issued)
        ->and($invoice->issuedAt())->toEqual($at);
});

it('refuses to issue an invoice worth nothing', function (): void {
    draftInvoice(cents: 0)->issue(new DateTimeImmutable());
})->throws(InvalidInvoiceTransition::class, 'Cannot issue an invoice with a total of zero.');

it('refuses to issue twice', function (): void {
    $invoice = draftInvoice();
    $invoice->issue(new DateTimeImmutable());
    $invoice->issue(new DateTimeImmutable());
})->throws(InvalidInvoiceTransition::class, 'Cannot issue an invoice that is issued.');

it('records the moment it was paid', function (): void {
    $issuedAt = new DateTimeImmutable('2026-08-15 10:00:00');
    $paidAt = new DateTimeImmutable('2026-08-16 09:00:00');

    $invoice = draftInvoice();
    $invoice->issue($issuedAt);
    $invoice->pay($paidAt);

    expect($invoice->status())->toBe(InvoiceStatus::Paid)
        ->and($invoice->paidAt())->toEqual($paidAt);
});

it('refuses to pay a draft', function (): void {
    draftInvoice()->pay(new DateTimeImmutable());
})->throws(InvalidInvoiceTransition::class, 'Cannot pay an invoice that is draft.');

it('refuses to pay twice', function (): void {
    $invoice = draftInvoice();
    $invoice->issue(new DateTimeImmutable('2026-08-15 10:00:00'));
    $invoice->pay(new DateTimeImmutable('2026-08-16 09:00:00'));
    $invoice->pay(new DateTimeImmutable('2026-08-17 09:00:00'));
})->throws(InvalidInvoiceTransition::class, 'Cannot pay an invoice that is paid.');

it('refuses a payment dated before the invoice existed', function (): void {
    $invoice = draftInvoice();
    $invoice->issue(new DateTimeImmutable('2026-08-15 10:00:00'));
    $invoice->pay(new DateTimeImmutable('2026-08-14 10:00:00'));
})->throws(InvalidInvoiceTransition::class, 'An invoice cannot be paid before it was issued.');

it('accepts a payment made in the same second it was issued', function (): void {
    $at = new DateTimeImmutable('2026-08-15 10:00:00');
    $invoice = draftInvoice();
    $invoice->issue($at);
    $invoice->pay($at);

    expect($invoice->status())->toBe(InvoiceStatus::Paid);
});
