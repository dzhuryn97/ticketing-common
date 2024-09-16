<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
;


return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true
    ])
    ->setCacheFile('./var/.phpunit.cache')
    ->setFinder($finder)
;
