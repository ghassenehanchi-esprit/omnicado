<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Exception;

use Exception;
use Throwable;

abstract class HandleableOrderException extends Exception
{
    private string $orderNumber;

    public function __construct(string $orderNumber, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->orderNumber = $orderNumber;

        parent::__construct($message, $code, $previous);
    }

    public function orderNumber(): string
    {
        return $this->orderNumber;
    }
}
