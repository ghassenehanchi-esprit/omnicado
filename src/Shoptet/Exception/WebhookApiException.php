<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Exception;

final class WebhookApiException extends ShoptetApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, '');
    }
}
