<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\ServiceBus;

use Elasticr\ServiceBus\Eso9\Factory\OrderStatusDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\OrderStatusDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OrderStatusDtoFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_create_order_status_dto_from_array_data(): void
    {
        /** @var OrderStatusDtoFactory $orderStatusFactory */
        $orderStatusFactory = self::getContainer()->get(OrderStatusDtoFactory::class);

        $data = [
            'number' => 'OBJ123456789',
            'order_status' => 'closed',
        ];

        $this->assertEquals(new OrderStatusDto('OBJ123456789', 'closed'), $orderStatusFactory->create($data));
    }
}
