<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\ServiceBus;

use Elasticr\ServiceBus\Eso9\Factory\OrderTrackingInfoDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\OrderTrackingInfoDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OrderTrackingInfoDtoFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_create_order_tracking_info_dto_from_array_data(): void
    {
        /** @var OrderTrackingInfoDtoFactory $orderTrackingInfoDtoFactory */
        $orderTrackingInfoDtoFactory = self::getContainer()->get(OrderTrackingInfoDtoFactory::class);

        $data = [
            'number' => 'OBJ123456',
            'shipments' => [
                [
                    'shipment_number' => 'TRACKING_NUMBER_123456789',
                    'shipment_track_url' => 'https://tracking_url.com/TRACKING_NUMBER_987654321',
                ],
            ],
        ];

        $this->assertEquals(
            new OrderTrackingInfoDto('OBJ123456', 'TRACKING_NUMBER_123456789', 'https://tracking_url.com/TRACKING_NUMBER_987654321'),
            $orderTrackingInfoDtoFactory->create($data)
        );
    }
}
