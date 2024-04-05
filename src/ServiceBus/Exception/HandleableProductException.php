<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Exception;

use Exception;
use Throwable;

abstract class HandleableProductException extends Exception
{
    private string $productId;

    public function __construct(string $productId, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->productId = $productId;

        parent::__construct($message, $code, $previous);
    }

    public function productId(): string
    {
        return $this->productId;
    }
}
