<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/routes',
        __DIR__ . '/database',
        __DIR__ . '/config',
        __DIR__ . '/tests',
    ])
    ->exclude('storage')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'blank_line_after_namespace' => true,
        // добавь ещё правила, если нужно
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
