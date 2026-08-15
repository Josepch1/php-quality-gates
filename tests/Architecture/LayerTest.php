<?php

declare(strict_types=1);

/**
 * Deptrac already blocks the wrong import. These cover the rules Deptrac cannot
 * see: what a class is allowed to be, not only what it may reference.
 */

arch('domain stays free of infrastructure concerns')
    ->expect('App\Modules\Billing\Domain')
    ->not->toUse([
        'App\Modules\Billing\Infrastructure',
        'App\Modules\Billing\Http',
        'PDO',
        'Illuminate',
    ]);

arch('domain never reads the clock on its own')
    ->expect('App\Modules\Billing\Domain')
    ->not->toUse(['time', 'date', 'microtime']);

arch('application does not reach for the framework or the database')
    ->expect('App\Modules\Billing\Application')
    ->not->toUse(['App\Modules\Billing\Infrastructure', 'PDO']);

arch('nothing debugs in production')
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'print_r', 'die', 'exit'])
    ->not->toBeUsed();

arch('classes are final unless they are meant to be extended')
    ->expect('App')
    ->classes()
    ->toBeFinal();

arch('value objects and use cases are immutable')
    ->expect(['App\Modules\Billing\Domain\Money', 'App\Modules\Billing\Application\IssueInvoice'])
    ->toBeReadonly();

arch('every file declares strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('interfaces live where they are consumed')
    ->expect('App\Modules\Billing\Application\InvoiceRepository')
    ->toBeInterface();
