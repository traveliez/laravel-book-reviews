<?php
/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/?version=2.15#configurator
 * you can change this configuration by importing this file.
 */

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        '@PSR1' => true,
        '@Symfony' => true,
        '@PhpCsFixer' => true,
        '@PHP71Migration' => true,
        '@PHP70Migration' => true,
        // PHP arrays should be declared using the configured syntax.
        'array_syntax' => ['syntax' => 'short'],
        // The body of each structure MUST be enclosed by braces.
        // Braces should be properly placed.
        // Body of braces should be properly indented.
        'braces' => ['allow_single_line_closure' => true],
        // Concatenation should be spaced according configuration.
        'concat_space' => ['spacing' => 'one'],
        // Unused `use` statements must be removed.
        'no_unused_imports' => false,
        // All PHPUnit test classes should be marked as internal.
        'php_unit_internal_class' => false,
        // Enforce camel (or snake) case for PHPUnit test methods, following configuration.
        'php_unit_method_casing' => ['case' => 'snake_case'],
        // Adds a default `@coversNothing` annotation to PHPUnit test classes that have no `@covers*` annotation.
        'php_unit_test_class_requires_covers' => false,
        // Visibility MUST be declared on all properties and methods; `abstract` and `final` MUST be declared before the visibility; `static` MUST be declared after the visibility.
        'visibility_required' => ['elements' => ['const']],
        // Write conditions in Yoda style (`true`), non-Yoda style (`false`) or ignore those conditions (`null`) based on configuration.
        'yoda_style' => false,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__)
    )
;