<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Eventing;

use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderDtoForMoneyS5WasCreatedEvent extends Event
{
    public function __construct(
        private readonly OrderDto $orderDto
    ) {
    }

    public function orderDto(): OrderDto
    {
        return $this->orderDto;
    }
}
