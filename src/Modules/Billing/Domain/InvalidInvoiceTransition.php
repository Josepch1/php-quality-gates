<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain;

use DomainException;

final class InvalidInvoiceTransition extends DomainException
{
    public static function from(InvoiceStatus $current, string $action): self
    {
        return new self(sprintf('Cannot %s an invoice that is %s.', $action, $current->value));
    }

    public static function emptyTotal(): self
    {
        return new self('Cannot issue an invoice with a total of zero.');
    }

    public static function paidBeforeIssued(): self
    {
        return new self('An invoice cannot be paid before it was issued.');
    }
}
