<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\Eso9Client;
use Elasticr\ServiceBus\Eso9\Client\Eso9ClientFactory;
use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureResponseData;
use Elasticr\ServiceBus\Eso9\Factory\OrderStatusDtoFactory;
use Elasticr\ServiceBus\Eso9\Repository\Eso9SyncStatusRepository;
use Elasticr\ServiceBus\Eso9\Service\UpdateOrderStatusService;
use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UpdateOrderStatusServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_dispatch_update_order_status_command(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactoryMock = $this->createMock(Eso9ProcedureDataFactory::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);
        $clientMock = $this->createMock(Eso9Client::class);
        $syncStatusRepositoryMock = $this->createMock(Eso9SyncStatusRepository::class);

        /** @var OrderStatusDtoFactory $orderStatusFactory */
        $orderStatusFactory = self::getContainer()->get(OrderStatusDtoFactory::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerRepository = $this->createMock(CustomerRepository::class);

        $service = new UpdateOrderStatusService(
            $clientFactoryMock,
            $procedureDataFactoryMock,
            $serviceBusMock,
            $syncStatusRepositoryMock,
            $orderStatusFactory,
            $customerConfigFinder,
            $customerRepository,
            $this->createMock(LoggerInterface::class)
        );

        $customerRepository->expects($this->once())->method('findByCode')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser'));

        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);

        $clientMock->expects($this->once())->method('callProcedure')->willReturn(new Eso9ProcedureResponseData(json_encode([
            'Order' => [['number' => 'OBJ123456',
                'order_status' => 'closed',
            ],
                ['number' => 'OBJ123789',
                    'order_status' => 'shipped',
                ],
            ],
        ], JSON_THROW_ON_ERROR)));

        $syncStatusRepositoryMock->expects($this->once())->method('add');

        $serviceBusMock->expects($this->exactly(2))->method('dispatch');

        $service->execute('123456');
    }
}
