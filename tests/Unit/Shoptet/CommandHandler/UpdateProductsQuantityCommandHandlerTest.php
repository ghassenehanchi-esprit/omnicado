<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Shoptet\CommandHandler;

use Elasticr\ServiceBus\ServiceBus\Command\UpdateProductsQuantityCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;
use Elasticr\ServiceBus\Shoptet\Api\ShoptetApi;
use Elasticr\ServiceBus\Shoptet\Api\StocksApiService;
use Elasticr\ServiceBus\Shoptet\CommandBus\CommandHandler\UpdateProductsQuantityCommandHandler;
use Elasticr\ServiceBus\Shoptet\Transforming\ProductQuantityInStockTransformer;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class UpdateProductsQuantityCommandHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_handles_update_products_quantity_command(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $quantityInStockTransformer = new ProductQuantityInStockTransformer();

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->will($this->onConsecutiveCalls([
            'data' => [
                'stocks' => [],
                'defaultStockId' => 1,
            ],
        ], []));
        $responseMock->method('getStatusCode')->willReturn(200);

        $cacheMock = $this->createMock(FilesystemAdapter::class);
        $cacheMock->method('getItem')->willReturn(new CacheItem());

        $stockApiServiceMock = new StocksApiService($shoptetApiMock, $httpClientMock, $quantityInStockTransformer, $cacheMock);
        $httpClientMock->expects($this->exactly(2))->method('request')->willReturn($responseMock);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new ShoptetConfig(12345, 20, 30, 'paid'));

        $commandHandler = new UpdateProductsQuantityCommandHandler($stockApiServiceMock, $customerConfigFinder);

        $updateOrderTrackingNumberCommand = $this->createMock(UpdateProductsQuantityCommand::class);
        $updateOrderTrackingNumberCommand->method('productsQuantity')->willReturn([new ProductQuantityInStockDto('132', 10, 10)]);

        $commandHandler->__invoke($updateOrderTrackingNumberCommand);
    }
}
