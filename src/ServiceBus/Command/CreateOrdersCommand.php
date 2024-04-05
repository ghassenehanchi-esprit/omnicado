<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\Support\Collection\ImmutableCollection;

final class CreateOrdersCommand implements CommandContract
{
    /**
     * @var ImmutableCollection<int, OrderDto>
     */
    private ImmutableCollection $orders;

    private int $customerId;

    /**
     * @param ImmutableCollection<int, OrderDto> $orders
     */
    public function __construct(ImmutableCollection $orders, int $customerId)
    {
        $this->orders = $orders;
        $this->customerId = $customerId;
    }

    /**
     * @return ImmutableCollection<int, OrderDto>
     */
    public function orders(): ImmutableCollection
    {
        return $this->orders;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
