<?php

declare(strict_types=1);

namespace App\Modules\Billing\Infrastructure;

use App\Modules\Billing\Application\InvoiceRepository;
use App\Modules\Billing\Domain\Invoice;

/**
 * Stands in for a database adapter. A real one would live here too, which is the
 * point: swapping it does not touch Domain or Application.
 *
 * @internal
 */
final class InMemoryInvoiceRepository implements InvoiceRepository
{
    /** @var array<string, Invoice> */
    private array $invoices = [];

    public function save(Invoice $invoice): void
    {
        $this->invoices[$invoice->id] = $invoice;
    }

    public function find(string $id): ?Invoice
    {
        return $this->invoices[$id] ?? null;
    }
}
