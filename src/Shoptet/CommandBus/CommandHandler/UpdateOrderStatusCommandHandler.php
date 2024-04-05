<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\CommandBus\CommandHandler;

use Elasticr\ServiceBus\ServiceBus\Command\UpdateOrderStatusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Exception\UpdateOrderStatusException;
use Elasticr\ServiceBus\Shoptet\Api\OrdersApiService;
use Elasticr\ServiceBus\Shoptet\CommandBus\Contract\ShoptetCommandHandlerContract;
use Elasticr\ServiceBus\Shoptet\Model\OrderStatusDto;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Exception;

final class UpdateOrderStatusCommandHandler implements ShoptetCommandHandlerContract
{
    private OrdersApiService $ordersApiService;

    private CustomerConfigFinder $customerConfigFinder;

    public function __construct(OrdersApiService $ordersApiService, CustomerConfigFinder $customerConfigFinder)
    {
        $this->ordersApiService = $ordersApiService;
        $this->customerConfigFinder = $customerConfigFinder;
    }

    public function __invoke(UpdateOrderStatusCommand $updateOrderStatusCommand): void
    {
        try {
            $orderStatusDto = $updateOrderStatusCommand->orderStatusDto();
            $customerId = $updateOrderStatusCommand->customerId();

            /** @var ShoptetConfig $config */
            $config = $this->customerConfigFinder->find($customerId, ShoptetConfig::NAME);

            $shoptetStatusDto = new OrderStatusDto((int) $orderStatusDto->status(), $config->eshopId(), '', null);
            $this->ordersApiService->updateOrderStatus($config->eshopId(), $orderStatusDto->orderNumber(), $shoptetStatusDto);
        } catch (Exception $exception) {
            throw new UpdateOrderStatusException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
