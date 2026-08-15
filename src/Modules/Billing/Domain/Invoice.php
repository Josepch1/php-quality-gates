<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain;

use DateTimeImmutable;

/**
 * The transitions live here rather than in a service, so there is exactly one
 * place that decides whether an invoice may move from one state to the next.
 */
final class Invoice
{
    private InvoiceStatus $status = InvoiceStatus::Draft;

    private ?DateTimeImmutable $issuedAt = null;

    private ?DateTimeImmutable $paidAt = null;

    public function __construct(
        public readonly string $id,
        public readonly Money $total,
    ) {}

    public function issue(DateTimeImmutable $at): void
    {
        if ($this->status !== InvoiceStatus::Draft) {
            throw InvalidInvoiceTransition::from($this->status, 'issue');
        }

        if ($this->total->isZero()) {
            throw InvalidInvoiceTransition::emptyTotal();
        }

        $this->status = InvoiceStatus::Issued;
        $this->issuedAt = $at;
    }

    public function pay(DateTimeImmutable $at): void
    {
        if ($this->status !== InvoiceStatus::Issued) {
            throw InvalidInvoiceTransition::from($this->status, 'pay');
        }

        if ($this->issuedAt !== null && $at < $this->issuedAt) {
            throw InvalidInvoiceTransition::paidBeforeIssued();
        }

        $this->status = InvoiceStatus::Paid;
        $this->paidAt = $at;
    }

    public function status(): InvoiceStatus
    {
        return $this->status;
    }

    public function issuedAt(): ?DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function paidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }
}
