<?php

declare(strict_types=1);

use App\Modules\Billing\Application\IssueInvoice;
use App\Modules\Billing\Domain\InvoiceStatus;
use App\Modules\Billing\Http\IssueInvoiceController;
use App\Modules\Billing\Infrastructure\InMemoryInvoiceRepository;
use Tests\Support\FrozenClock;

/**
 * Each test builds its own graph. Sharing it through `$this` in beforeEach saves
 * four lines and costs a static analyser that can no longer tell what type
 * anything is.
 *
 * @return array{IssueInvoice, InMemoryInvoiceRepository, FrozenClock}
 */
function billingContext(): array
{
    $invoices = new InMemoryInvoiceRepository();
    $clock = FrozenClock::at('2026-08-15 10:00:00');

    return [new IssueInvoice($invoices, $clock), $invoices, $clock];
}

it('issues an invoice and stores it', function (): void {
    [$issueInvoice, $invoices, $clock] = billingContext();

    $invoice = $issueInvoice('INV-1', 2500, 'BRL');

    expect($invoice->status())->toBe(InvoiceStatus::Issued)
        ->and($invoice->issuedAt())->toEqual($clock->now())
        ->and($invoices->find('INV-1'))->toBe($invoice);
});

it('refuses to issue the same id twice', function (): void {
    [$issueInvoice] = billingContext();

    $issueInvoice('INV-1', 2500, 'BRL');
    $issueInvoice('INV-1', 9900, 'BRL');
})->throws(RuntimeException::class, 'Invoice INV-1 already exists.');

it('rejects an amount the domain will not accept', function (): void {
    [$issueInvoice] = billingContext();

    $issueInvoice('INV-2', 0, 'BRL');
})->throws(DomainException::class);

it('leaves nothing behind when the domain rejects the invoice', function (): void {
    [$issueInvoice, $invoices] = billingContext();

    try {
        $issueInvoice('INV-2', 0, 'BRL');
    } catch (DomainException) {
        // The point of the test is what the repository looks like afterwards.
    }

    expect($invoices->find('INV-2'))->toBeNull();
});

it('returns a serialisable payload through the controller', function (): void {
    [$issueInvoice] = billingContext();

    $response = (new IssueInvoiceController($issueInvoice))([
        'id' => 'INV-3',
        'amount_in_cents' => 1050,
        'currency' => 'brl',
    ]);

    expect($response)->toBe([
        'id' => 'INV-3',
        'status' => 'issued',
        'total' => 1050,
        'currency' => 'BRL',
        'issued_at' => '2026-08-15T10:00:00+00:00',
    ]);
});
