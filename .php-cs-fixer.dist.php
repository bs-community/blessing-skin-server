<?php

$finder = PhpCsFixer\Finder::create()
    ->in('app')
    ->in('database')
    ->in('routes')
    ->in('tests');

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        'align_multiline_comment' => true,
        'array_syntax' => ['syntax' => 'short'],
        'increment_style' => ['style' => 'post'],
        'list_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
    ])
    ->setFinder($finder);
