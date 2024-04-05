<?php

declare(strict_types=1);

namespace Unit\Shoptet\Api;

use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;
use Elasticr\ServiceBus\Shoptet\Api\ShoptetApi;
use Elasticr\ServiceBus\Shoptet\Api\StocksApiService;
use Elasticr\ServiceBus\Shoptet\Transforming\ProductQuantityInStockTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class StocksApiServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function update_quantity_in_stock(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $transformer = new ProductQuantityInStockTransformer();
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(200);

        $cacheMock = $this->createMock(FilesystemAdapter::class);
        $cacheMock->method('getItem')->willReturn(new CacheItem());

        $service = new StocksApiService($shoptetApiMock, $httpClientMock, $transformer, $cacheMock);

        $httpClientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $data = [];
        $data[] = new ProductQuantityInStockDto('A', 10, 10);

        $service->updateQuantityInStock(1, 1, $data);
    }

    /**
     * @test
     */
    public function update_quantity_in_stock_with_more_than_50_items(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $transformer = new ProductQuantityInStockTransformer();
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(200);

        $cacheMock = $this->createMock(FilesystemAdapter::class);
        $cacheMock->method('getItem')->willReturn(new CacheItem());

        $service = new StocksApiService($shoptetApiMock, $httpClientMock, $transformer, $cacheMock);

        $httpClientMock->expects($this->exactly(2))->method('request')->willReturn($responseMock);

        $data = [];
        for ($i = 0; $i < 65; $i++) {
            $data[] = new ProductQuantityInStockDto('A', $i, $i);
        }

        $service->updateQuantityInStock(1, 1, $data);
    }

    /**
     * @test
     */
    public function update_only_changed_quantity_in_stock(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $transformer = new ProductQuantityInStockTransformer();
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(200);

        $cacheMock = $this->createMock(FilesystemAdapter::class);
        $cachedData = [
            'A' => new ProductQuantityInStockDto('A', 10, 10),
        ];
        $cachedItem = $this->createMock(CacheItem::class);
        $cachedItem->method('isHit')->willReturnOnConsecutiveCalls(false, true, true);
        $cachedItem->expects($this->exactly(2))->method('get')->willReturn(serialize($cachedData));
        $cacheMock->method('getItem')->willReturn($cachedItem);

        $service = new StocksApiService($shoptetApiMock, $httpClientMock, $transformer, $cacheMock);

        $httpClientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $data = [];
        $data[] = new ProductQuantityInStockDto('A', 10, 10);

        $service->updateQuantityInStock(1, 1, $data);
        $service->updateQuantityInStock(1, 1, $data);
        $service->updateQuantityInStock(1, 1, $data);
    }

    /**
     * @test
     */
    public function update_changed_quantity_in_stock(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $transformer = new ProductQuantityInStockTransformer();
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(200);

        $cacheMock = $this->createMock(FilesystemAdapter::class);
        $cachedData = [
            'A' => new ProductQuantityInStockDto('A', 10, 10),
        ];
        $cachedItem = $this->createMock(CacheItem::class);
        $cachedItem->method('get')->willReturn(serialize($cachedData));
        $cachedItem->method('isHit')->willReturn(true);
        $cacheMock->method('getItem')->willReturn($cachedItem);

        $service = new StocksApiService($shoptetApiMock, $httpClientMock, $transformer, $cacheMock);

        $httpClientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $data = [];
        $data[] = new ProductQuantityInStockDto('A', 100, 100);

        $service->updateQuantityInStock(1, 1, $data);
    }

    /**
     * @test
     */
    public function get_default_stock_id(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $transformer = new ProductQuantityInStockTransformer();
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([
            'data' => [
                'stocks' => [],
                'defaultStockId' => 1,
            ],
        ]);
        $responseMock->method('getStatusCode')->willReturn(200);

        $cacheMock = $this->createMock(FilesystemAdapter::class);

        $service = new StocksApiService($shoptetApiMock, $httpClientMock, $transformer, $cacheMock);

        $httpClientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $stockId = $service->getDefaultStockId(1);

        $this->assertEquals(1, $stockId);
    }
}
