<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\CommandBus\CommandHandler;

use Elasticr\ServiceBus\ServiceBus\Command\UpdateOrderTrackingNumberCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Exception\UpdateOrderTrackingNumberException;
use Elasticr\ServiceBus\Shoptet\Api\OrdersApiService;
use Elasticr\ServiceBus\Shoptet\CommandBus\Contract\ShoptetCommandHandlerContract;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Exception;

final class UpdateOrderTrackingNumberCommandHandler implements ShoptetCommandHandlerContract
{
    private OrdersApiService $ordersApiService;

    private CustomerConfigFinder $customerConfigFinder;

    public function __construct(OrdersApiService $ordersApiService, CustomerConfigFinder $customerConfigFinder)
    {
        $this->ordersApiService = $ordersApiService;
        $this->customerConfigFinder = $customerConfigFinder;
    }

    public function __invoke(UpdateOrderTrackingNumberCommand $updateOrderTrackingNumberCommand): void
    {
        try {
            $customerId = $updateOrderTrackingNumberCommand->customerId();

            /** @var ShoptetConfig $config */
            $config = $this->customerConfigFinder->find($customerId, ShoptetConfig::NAME);

            $this->ordersApiService->updateTrackingInfo($config->eshopId(), $updateOrderTrackingNumberCommand->orderTrackingInfoDto());
        } catch (Exception $exception) {
            throw new UpdateOrderTrackingNumberException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
