<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http;

use App\Modules\Billing\Application\IssueInvoice;

/**
 * Framework-free on purpose. The HTTP layer translates a request into a call and
 * a result into a response, and nothing here should be worth unit testing.
 */
final readonly class IssueInvoiceController
{
    public function __construct(private IssueInvoice $issueInvoice) {}

    /**
     * @param array{id: string, amount_in_cents: int, currency: string} $request
     *
     * @return array{id: string, status: string, total: int, currency: string, issued_at: ?string}
     */
    public function __invoke(array $request): array
    {
        $invoice = ($this->issueInvoice)(
            $request['id'],
            $request['amount_in_cents'],
            $request['currency'],
        );

        return [
            'id' => $invoice->id,
            'status' => $invoice->status()->value,
            'total' => $invoice->total->amountInCents,
            'currency' => $invoice->total->currency,
            'issued_at' => $invoice->issuedAt()?->format(DATE_ATOM),
        ];
    }
}
