<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Shoptet\Transforming;

use Elasticr\ServiceBus\ServiceBus\Model\OrderTrackingInfoDto;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderTrackingInfoTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OrderTrackingInfoTransformerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function tranform_dto_to_array(): void
    {
        /** @var OrderTrackingInfoTransformer $transformer */
        $transformer = self::getContainer()->get(OrderTrackingInfoTransformer::class);

        $trackingInfo = new OrderTrackingInfoDto('20210001', null, null);
        $this->assertEquals($transformer->transform($trackingInfo), []);

        $trackingInfo = new OrderTrackingInfoDto('20210001', 'DR123456789', null);
        $this->assertEquals($transformer->transform($trackingInfo), [
            'trackingNumber' => 'DR123456789',
        ]);

        $trackingInfo = new OrderTrackingInfoDto('20210001', 'DR123456789', 'http://cpost.cz/tracking/DR123456789');
        $this->assertEquals($transformer->transform($trackingInfo), [
            'trackingNumber' => 'DR123456789',
        ]);
    }
}
