<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__.'/src', __DIR__.'/tests']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PHP84Migration' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_functions' => false],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setFinder($finder);
