<?php

declare(strict_types=1);

use Elasticr\CodingStandard\Rector\RemoveFinalFromEntityRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->rule(RemoveFinalFromEntityRector::class);

    $rectorConfig->import(SetList::PHP_70);
    $rectorConfig->import(SetList::PHP_71);
    $rectorConfig->import(SetList::PHP_72);
    $rectorConfig->import(SetList::PHP_73);
    $rectorConfig->import(SetList::PHP_74);

    $rectorConfig->skip([
        __DIR__ . '/tests/var',
    ]);

    $rectorConfig->importNames();
};
