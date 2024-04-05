<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Support;

use Elasticr\ServiceBus\Support\Contract\PrioritizableContract;

abstract class ServiceCollectionSorter
{
    /**
     * @template T of PrioritizableContract
     * @param T[] $classes
     *
     * @return array<int, T>
     */
    public static function sort(array $classes): array
    {
        usort($classes, fn (PrioritizableContract $first, PrioritizableContract $second): int => $second->priority() <=> $first->priority());

        return $classes;
    }
}
