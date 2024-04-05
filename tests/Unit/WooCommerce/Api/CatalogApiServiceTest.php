<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api;

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClient;
use Automattic\WooCommerce\HttpClient\Response;
use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;
use Elasticr\ServiceBus\WooCommerce\Api\ApiClientFactory;
use Elasticr\ServiceBus\WooCommerce\Api\CatalogApiService;
use Elasticr\ServiceBus\WooCommerce\Factory\ProductDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Transforming\ProductQuantityInStockTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CatalogApiServiceTest extends KernelTestCase
{
    use WooCommerceConfigTrait;

    /**
     * @test
     */
    public function list_of_products(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $productsApiData = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/list_products.json') ?: '', false, 512, JSON_THROW_ON_ERROR);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $clientMock->method('get')->willReturnOnConsecutiveCalls($productsApiData, []);
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;

        /** @var ProductDtoFactory $factory */
        $factory = self::getContainer()->get(ProductDtoFactory::class);
        /** @var ProductQuantityInStockTransformer $transformer */
        $transformer = self::getContainer()->get(ProductQuantityInStockTransformer::class);
        $catalogApiService = new CatalogApiService($clientFactoryMock, $factory, $transformer);

        $products = $catalogApiService->getProducts($this->getSimpleConfig());

        $this->assertEquals(2, count($products));
    }

    /**
     * @test
     */
    public function update_quantity_in_stock(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $clientMock->method('post')->willReturnCallback(function (string $endpoint, array $data) {
            $this->assertEquals([
                'update' => [
                    ['id' => 1,
                        'stock_quantity' => 10,
                    ],
                ],
            ], $data);
        });
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;

        /** @var ProductDtoFactory $factory */
        $factory = self::getContainer()->get(ProductDtoFactory::class);
        /** @var ProductQuantityInStockTransformer $transformer */
        $transformer = self::getContainer()->get(ProductQuantityInStockTransformer::class);
        $catalogApiService = new CatalogApiService($clientFactoryMock, $factory, $transformer);

        $data = [];
        $data[] = new ProductQuantityInStockDto('1', 10, 10);

        $catalogApiService->updateQuantityInStock($this->getSimpleConfig(), $data);
    }

    /**
     * @test
     */
    public function update_quantity_in_stock_more_products(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;

        /** @var ProductDtoFactory $factory */
        $factory = self::getContainer()->get(ProductDtoFactory::class);
        /** @var ProductQuantityInStockTransformer $transformer */
        $transformer = self::getContainer()->get(ProductQuantityInStockTransformer::class);
        $catalogApiService = new CatalogApiService($clientFactoryMock, $factory, $transformer);

        $data = [];
        $expectedRequestData = [
            'update' => [],
        ];
        for ($i = 0; $i < 50; $i++) {
            $data[] = new ProductQuantityInStockDto((string) $i, $i, $i);
            $expectedRequestData['update'][] = ['id' => $i,
                'stock_quantity' => $i,
            ];
        }

        $clientMock->method('post')->willReturnCallback(function (string $endpoint, array $data) use ($expectedRequestData) {
            $this->assertEquals($expectedRequestData, $data);
        });

        $catalogApiService->updateQuantityInStock($this->getSimpleConfig(), $data);
    }
}
