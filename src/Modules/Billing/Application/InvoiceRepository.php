<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application;

use App\Modules\Billing\Domain\Invoice;

/**
 * The port lives with the code that needs it, not with the driver that implements
 * it. Infrastructure depends on Application, never the other way around.
 */
interface InvoiceRepository
{
    public function save(Invoice $invoice): void;

    public function find(string $id): ?Invoice;
}
