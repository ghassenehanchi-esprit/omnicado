<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Exception;

use Exception;

final class StockQuantityUpdateException extends Exception
{
    /**
     *  @var array<string>
     */
    private array $errors;

    /**
     * @param array<string> $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct('Error during updating stock quantity');
        $this->errors = $errors;
    }

    /**
     * @return array<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
