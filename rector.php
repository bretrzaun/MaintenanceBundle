<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withPhpSets()
    ->withPreparedSets(deadCode: true, symfonyCodeQuality: true)
    ->withAttributesSets(symfony: true, doctrine: true, phpunit: true)
    ->withTypeCoverageLevel(4)
;
