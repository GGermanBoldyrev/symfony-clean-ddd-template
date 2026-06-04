<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('var')
    ->exclude('vendor')
    ->append([__FILE__]);

return new PhpCsFixer\Config()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // Базовые правила DDD и PHP 8+
        'declare_strict_types' => true,
        'final_class' => true,

        // Массивы и синтаксис
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters', 'match']],

        // Импорты (сортировка по алфавиту, глобальные классы импортируются)
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],

        // Чистота кода и читаемость
        'yoda_style' => false, // Пишем if ($a === 1), а не if (1 === $a)
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],

        // PHP-Doc
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => false,
            'remove_inheritdoc' => true,
        ],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,

        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],

        // Видимость и операторы
        'visibility_required' => ['elements' => ['property', 'method', 'const']],
        'operator_linebreak' => ['only_booleans' => true, 'position' => 'beginning'],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/cache/.php-cs-fixer.cache');
