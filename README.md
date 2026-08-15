# php-quality-gates

A small PHP module wired to the checks I run on real projects. The domain here is
deliberately boring, an invoice that can be issued and paid, because the point is
not the feature. The point is what happens when you try to write it badly.

Most of my work is in private repositories, so this is the closest I can get to
showing how I set a project up.

```bash
composer install
composer check
```

## The gates

| Gate | Command | What it stops |
| :--- | :--- | :--- |
| Formatting | `composer lint:check` | Style arguments in review |
| Static analysis | `composer stan` | Types that only exist in your head |
| Layer boundaries | `composer arch` | The shortcut import that seemed fine at the time |
| Tests | `composer test` | The obvious regressions |
| Mutation testing | `composer test:mutation` | Tests that run the code without checking it |

CI runs all five on every push and pull request. Mutation testing goes last,
since there is no point mutating code whose tests already fail.

## Layers

```
src/
├── Modules/
│   └── Billing/
│       ├── Domain/           the rules, no I/O, no framework
│       ├── Application/      use cases and the ports they need
│       ├── Infrastructure/   the adapters that implement those ports
│       └── Http/             request in, response out
└── Shared/                   things every module may use
```

Deptrac holds the arrows down. `Domain` may reference `Shared` and nothing else,
`Application` may reference `Domain`, and so on outward. Try it:

```php
// src/Modules/Billing/Domain/Invoice.php
use App\Modules\Billing\Infrastructure\InMemoryInvoiceRepository;
```

```
DependsOnDisallowedLayer
App\Modules\Billing\Domain\Invoice must not depend on
App\Modules\Billing\Infrastructure\InMemoryInvoiceRepository
src/Modules/Billing/Domain/Invoice.php:56
```

The build fails. Nobody has to catch it in review, and nobody has to remember the
rule.

## Why mutation testing

Coverage says which lines ran. It does not say whether anything would have
noticed if those lines were wrong. Infection edits the source on purpose, flips a
`<` to `<=`, returns an empty array instead of the real one, and reruns the
suite. Every edit that survives is a line your tests execute but do not check.

It is slower than the rest and it is the gate that finds the tests worth having.

## Exceptions

There are two places where a rule is switched off, and both say why in the file
itself:

- `deptrac.yaml` keeps a `skip_violations` list, empty here. Every entry it ever
  gets has to carry the reason above it.
- `phpstan.neon` excludes `tests/Architecture`. Pest's `arch()` returns a fluent
  object PHPStan cannot model, so the whole file reads as calls to undefined
  methods. Those rules are checked by running them.

An exception with a reason next to it is a decision. An exception in a baseline
file is a number nobody looks at again.

## Notes for anyone copying this

Two things cost me time and are not obvious:

**Deptrac 2 and the `analyser` node.** Declaring `analyser` to set `internal_tag`
stops the defaults being applied to `types` in the same node. Deptrac then
analyses nothing, reports `Allowed 0` and exits green. If you touch that node,
list `types` explicitly and check the report is not suspiciously empty.

**Pest and `$this` in `beforeEach`.** Sharing setup through `$this->something`
means PHPStan sees an undefined property on `TestCase`. Building the graph inside
each test costs a few lines and keeps the analyser useful.

## Licence

MIT.
