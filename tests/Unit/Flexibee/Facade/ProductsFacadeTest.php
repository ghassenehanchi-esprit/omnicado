<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Flexibee\Facade;

use Elasticr\ServiceBus\Flexibee\Api\PriceListApiService;
use Elasticr\ServiceBus\Flexibee\Facade\ProductsFacade;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\Flexibee\Api\FlexibeeConfigTrait;

final class ProductsFacadeTest extends KernelTestCase
{
    use FlexibeeConfigTrait;

    /**
     * @test
     */
    public function transfer_products(): void
    {
        $productsApiServiceMock = $this->createMock(PriceListApiService::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);

        $serviceBusMock->expects($this->exactly(4))->method('dispatch');

        $productsApiServiceMock->expects($this->exactly(1))->method('getItems')->willReturn([
            $this->createProduct(1),
            $this->createProduct(2),
            $this->createProduct(3),
            $this->createProduct(4),
        ]);

        $customerRepository = $this->createMock(CustomerRepository::class);
        $customerRepository->expects($this->once())->method('findByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->atLeastOnce())->method('find')->willReturn($this->getConfig());

        $ordersFacade = new ProductsFacade($productsApiServiceMock, $serviceBusMock, $customerRepository, $customerConfigFinder);

        $ordersFacade->transferProducts('123456');
    }

    private function createProduct(int $id): ProductDto
    {
        return new ProductDto($id, 'AB123', 'Produkt', 121.0, 100.00, 21.00, 0, 'ks', 10);
    }
}
