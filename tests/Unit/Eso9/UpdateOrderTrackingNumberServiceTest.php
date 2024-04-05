<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\Eso9Client;
use Elasticr\ServiceBus\Eso9\Client\Eso9ClientFactory;
use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureResponseData;
use Elasticr\ServiceBus\Eso9\Factory\OrderTrackingInfoDtoFactory;
use Elasticr\ServiceBus\Eso9\Repository\Eso9SyncStatusRepository;
use Elasticr\ServiceBus\Eso9\Service\UpdateOrderTrackingNumberService;
use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UpdateOrderTrackingNumberServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_dispatch_update_order_tracking_number_command(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactory = $this->createMock(Eso9ProcedureDataFactory::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);
        $clientMock = $this->createMock(Eso9Client::class);
        $syncStatusRepositoryMock = $this->createMock(Eso9SyncStatusRepository::class);

        /** @var OrderTrackingInfoDtoFactory $orderTrackingInfoDtoFactory */
        $orderTrackingInfoDtoFactory = self::getContainer()->get(OrderTrackingInfoDtoFactory::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerRepository = $this->createMock(CustomerRepository::class);

        $service = new UpdateOrderTrackingNumberService(
            $clientFactoryMock,
            $procedureDataFactory,
            $serviceBusMock,
            $syncStatusRepositoryMock,
            $orderTrackingInfoDtoFactory,
            $customerConfigFinder,
            $customerRepository
        );

        $customerRepository->expects($this->once())->method('findByCode')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser'));

        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $clientMock->expects($this->once())->method('callProcedure')->willReturn(new Eso9ProcedureResponseData(json_encode([
            'Order' => [['number' => 'OBJ123456',
                'shipments' => [['shipment_number' => 'TRACKING_NUMBER_123456789',
                    'shipment_track_url' => 'https://tracking_url.com/TRACKING_NUMBER_123456789',
                ]],
            ],
                ['number' => 'OBJ123789',
                    'shipments' => [['shipment_number' => 'TRACKING_NUMBER_987654321',
                        'shipment_track_url' => 'https://tracking_url.com/TRACKING_NUMBER_987654321',
                    ]],
                ],
            ],
        ], JSON_THROW_ON_ERROR)));

        $syncStatusRepositoryMock->expects($this->once())->method('add');

        $serviceBusMock->expects($this->exactly(2))->method('dispatch');

        $service->execute('123456');
    }
}
