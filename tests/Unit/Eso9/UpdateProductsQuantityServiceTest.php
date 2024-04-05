<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\Eso9Client;
use Elasticr\ServiceBus\Eso9\Client\Eso9ClientFactory;
use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureResponseData;
use Elasticr\ServiceBus\Eso9\Factory\ProductQuantityInStockDtoFactory;
use Elasticr\ServiceBus\Eso9\Service\UpdateProductsQuantityService;
use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UpdateProductsQuantityServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_dispatch_update_products_quantity_command(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactoryMock = $this->createMock(Eso9ProcedureDataFactory::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);
        $clientMock = $this->createMock(Eso9Client::class);

        /** @var ProductQuantityInStockDtoFactory $productQuantityInStockDtoFactory */
        $productQuantityInStockDtoFactory = self::getContainer()->get(ProductQuantityInStockDtoFactory::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerRepository = $this->createMock(CustomerRepository::class);

        $service = new UpdateProductsQuantityService(
            $clientFactoryMock,
            $procedureDataFactoryMock,
            $serviceBusMock,
            $productQuantityInStockDtoFactory,
            $customerConfigFinder,
            $customerRepository
        );

        $customerRepository->expects($this->once())->method('findByCode')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser'));

        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $clientMock->expects($this->once())->method('callProcedure')->willReturn(new Eso9ProcedureResponseData(json_encode([['Produkt_Id' => 'SKU-1',
            'Kod_Mj' => 'ks',
            'mnStavSklad' => 10,
        ], ['Produkt_Id' => 'SKU-2',
            'Kod_Mj' => 'ks',
            'mnStavSklad' => 20,
        ]], JSON_THROW_ON_ERROR)));

        $serviceBusMock->expects($this->once())->method('dispatch');

        $service->execute('123456');
    }
}
