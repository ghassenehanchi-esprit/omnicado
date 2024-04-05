<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Support;

abstract class OrderHelper
{
    /**
     * @return string[]
     */
    public static function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName));
        $lastname = array_pop($parts);
        $firstname = implode(' ', $parts);

        return [$firstname, $lastname];
    }
}
