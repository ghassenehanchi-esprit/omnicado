<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Shoptet\Transforming;

use Elasticr\ServiceBus\Shoptet\Model\OrderStatusDto;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderStatusTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OrderStatusTransformerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function tranform_dto_to_array(): void
    {
        /** @var OrderStatusTransformer $orderStatusTransformer */
        $orderStatusTransformer = self::getContainer()->get(OrderStatusTransformer::class);

        $orderStatusDto = new OrderStatusDto(1, 123, 'Vyřízeno', true);
        $eshopId = 1234;

        $this->assertEquals($orderStatusTransformer->transform($eshopId, $orderStatusDto), [
            'statusId' => 1,
        ]);
    }
}
