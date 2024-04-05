<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Facade;

use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\WooCommerce\Api\CatalogApiService;
use Elasticr\ServiceBus\WooCommerce\Facade\ProductsFacade;
use Elasticr\ServiceBus\WooCommerce\Factory\ProductDtoFactory;
use Elasticr\ServiceBus\WooCommerce\ValueObject\WooCommerceTaxConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api\WooCommerceConfigTrait;

final class ProductsFacadeTest extends KernelTestCase
{
    use WooCommerceConfigTrait;

    /**
     * @test
     */
    public function transfer_new_orders(): void
    {
        $catalogApiServiceMock = $this->createMock(CatalogApiService::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);
        $customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $customerRepositoryMock->expects($this->once())->method('findByCode')->willReturn(new Customer('Testing customer', '123456'));
        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinderMock->expects($this->atLeastOnce())->method('find')->willReturn($this->getSimpleConfig());

        $serviceBusMock->expects($this->exactly(2))->method('dispatch');

        $catalogApiServiceMock->expects($this->exactly(1))->method('getProducts')->willReturn([$this->createProduct(), $this->createProduct()]);

        $facade = new ProductsFacade($catalogApiServiceMock, $serviceBusMock, $customerRepositoryMock, $customerConfigFinderMock);

        $facade->transferProducts('123456');
    }

    private function createProduct(): ProductDto
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/product.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        /** @var ProductDtoFactory $factory */
        $factory = self::getContainer()->get(ProductDtoFactory::class);
        return $factory->createFromApiResponse($data, new WooCommerceTaxConfig([]));
    }
}
