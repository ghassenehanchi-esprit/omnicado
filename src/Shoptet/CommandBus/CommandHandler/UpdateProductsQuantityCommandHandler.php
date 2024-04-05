<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\CommandBus\CommandHandler;

use Elasticr\ServiceBus\ServiceBus\Command\UpdateProductsQuantityCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Exception\UpdateProductsQuantityException;
use Elasticr\ServiceBus\Shoptet\Api\StocksApiService;
use Elasticr\ServiceBus\Shoptet\CommandBus\Contract\ShoptetCommandHandlerContract;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Exception;

final class UpdateProductsQuantityCommandHandler implements ShoptetCommandHandlerContract
{
    private StocksApiService $stocksApiService;

    private CustomerConfigFinder $customerConfigFinder;

    public function __construct(StocksApiService $stocksApiService, CustomerConfigFinder $customerConfigFinder)
    {
        $this->stocksApiService = $stocksApiService;
        $this->customerConfigFinder = $customerConfigFinder;
    }

    public function __invoke(UpdateProductsQuantityCommand $updateProductsQuantityCommand): void
    {
        try {
            $customerId = $updateProductsQuantityCommand->customerId();

            /** @var ShoptetConfig $config */
            $config = $this->customerConfigFinder->find($customerId, ShoptetConfig::NAME);

            $stockId = $this->stocksApiService->getDefaultStockId($config->eshopId());

            $this->stocksApiService->updateQuantityInStock($config->eshopId(), $stockId, $updateProductsQuantityCommand->productsQuantity());
        } catch (Exception $exception) {
            throw new UpdateProductsQuantityException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
