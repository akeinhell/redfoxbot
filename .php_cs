<?php

$header = <<<'EOF'
created by akeinhell (c) 2017
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                              => true,
        '@PSR2'                                 => true,
        '@Symfony:risky'                        => true,
        'array_syntax'                          => ['syntax' => 'short'],
        'combine_consecutive_unsets'            => true,
        // one should use PHPUnit methods to set up expected exception instead of annotations
        'general_phpdoc_annotation_remove'      => [
            'expectedException',
            'expectedExceptionMessage',
            'expectedExceptionMessageRegExp',
        ],
        'heredoc_to_nowdoc'                     => true,
        'no_extra_consecutive_blank_lines'      => [
            'break',
            'continue',
            'extra',
            'return',
            'throw',
            'use',
            'parenthesis_brace_block',
            'square_brace_block',
            'curly_brace_block',
        ],
        'no_unreachable_default_argument_value' => true,
        'no_useless_else'                       => true,
        'no_useless_return'                     => true,
        'ordered_class_elements'                => true,
        'ordered_imports'                       => true,
        'php_unit_strict'                       => true,
        'phpdoc_add_missing_param_annotation'   => true,
        'phpdoc_order'                          => true,
        'psr4'                                  => true,
        'strict_comparison'                     => true,
        'strict_param'                          => true,
        'concat_space'                          => ['spacing' => 'one'],
        'binary_operator_spaces'                => [
            'align_double_arrow' => true,
            'align_equals'       => true,
        ],
        'blank_line_after_opening_tag'          => true,
        'no_short_echo_tag'                     => true,
        'no_unused_imports'                     => true,
        'standardize_not_equals'                => true,
        'single_quote'                          => true,
        'normalize_index_brace'                 => true,
        'not_operator_with_successor_space'     => true,
        'no_blank_lines_after_phpdoc'           => true,
        'no_blank_lines_after_class_opening'    => true,
        'method_argument_space'                 => true,
        'linebreak_after_opening_tag'           => true,
        'hash_to_slash_comment'                 => true,
        'function_typehint_space'               => true,
        'full_opening_tag'                      => true,
        'blank_line_before_return'              => true,
        'no_empty_comment'                      => true,
        'no_leading_import_slash'               => true,
        'no_leading_namespace_whitespace'       => true,
        'no_mixed_echo_print'                   => ['use' => 'echo'],
        'no_short_bool_cast'                    => true,
        'ternary_operator_spaces'               => true,
        'trailing_comma_in_multiline_array'     => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('tests/Fixtures')
            ->in(__DIR__)
    );
