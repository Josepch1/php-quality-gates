<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application;

use App\Modules\Billing\Domain\Invoice;
use App\Modules\Billing\Domain\Money;
use App\Shared\Clock;
use RuntimeException;

final readonly class IssueInvoice
{
    public function __construct(
        private InvoiceRepository $invoices,
        private Clock $clock,
    ) {}

    public function __invoke(string $id, int $amountInCents, string $currency): Invoice
    {
        if ($this->invoices->find($id) !== null) {
            throw new RuntimeException(sprintf('Invoice %s already exists.', $id));
        }

        $invoice = new Invoice($id, Money::of($amountInCents, $currency));
        $invoice->issue($this->clock->now());

        $this->invoices->save($invoice);

        return $invoice;
    }
}
