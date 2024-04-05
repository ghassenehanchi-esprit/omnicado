<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\CommandHandler;

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClient;
use Automattic\WooCommerce\HttpClient\Response;
use Elasticr\ServiceBus\ServiceBus\Command\UpdateProductsQuantityCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;
use Elasticr\ServiceBus\WooCommerce\Api\ApiClientFactory;
use Elasticr\ServiceBus\WooCommerce\Api\CatalogApiService;
use Elasticr\ServiceBus\WooCommerce\CommandBus\CommandHandler\UpdateProductsQuantityCommandHandler;
use Elasticr\ServiceBus\WooCommerce\Factory\ProductDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Transforming\ProductQuantityInStockTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api\WooCommerceConfigTrait;

final class UpdateProductsQuantityCommandHandlerTest extends KernelTestCase
{
    use WooCommerceConfigTrait;

    /**
     * @test
     */
    public function it_handles_update_products_quantity_command(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;
        $clientMock->expects($this->once())->method('post');

        $productsApiData = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/list_products.json') ?: '', false, 512, JSON_THROW_ON_ERROR);
        $clientMock->method('get')->willReturnOnConsecutiveCalls($productsApiData, []);

        /** @var ProductDtoFactory $productDtoFactory */
        $productDtoFactory = self::getContainer()->get(ProductDtoFactory::class);
        /** @var ProductQuantityInStockTransformer $transformer */
        $transformer = self::getContainer()->get(ProductQuantityInStockTransformer::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->once())->method('find')->willReturn($this->getSimpleConfig());

        $catalogApiService = new CatalogApiService($clientFactoryMock, $productDtoFactory, $transformer);

        $commandHandler = new UpdateProductsQuantityCommandHandler($catalogApiService, $customerConfigFinder);

        $updateProductsQuantityCommand = $this->createMock(UpdateProductsQuantityCommand::class);
        $updateProductsQuantityCommand->method('productsQuantity')->willReturn([new ProductQuantityInStockDto('6779290', 10, 10)]);

        $commandHandler->__invoke($updateProductsQuantityCommand);
    }
}
