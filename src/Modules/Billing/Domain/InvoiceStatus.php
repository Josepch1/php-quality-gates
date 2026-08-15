<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Paid = 'paid';
}
