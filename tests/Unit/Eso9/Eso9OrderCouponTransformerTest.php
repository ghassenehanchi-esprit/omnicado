<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Transforming\Eso9OrderCouponTransformer;
use Elasticr\ServiceBus\ServiceBus\CustomerService;
use Elasticr\ServiceBus\ServiceBus\Model\OrderCouponDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9OrderCouponTransformerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_transforms_eso9_order_coupon_data(): void
    {
        /** @var Eso9OrderCouponTransformer $transformer */
        $transformer = self::getContainer()->get(Eso9OrderCouponTransformer::class);

        $this->assertEquals($this->expectedArray(''), $transformer->transform($this->createCouponDto()));
    }

    /**
     * @test
     */
    public function it_transforms_eso9_order_coupon_data_with_custom_eso9_transformers_if_they_supports_customer(): void
    {
        $transformer = new Eso9OrderCouponTransformer([new TestingEso9OrderCouponTransformer()], $this->createCustomerService(1));

        $this->assertEquals($this->expectedArray('Changed sku'), $transformer->transform($this->createCouponDto()));

        $transformer = new Eso9OrderCouponTransformer([new TestingEso9OrderCouponTransformer()], $this->createCustomerService(999));
        $this->assertEquals($this->expectedArray(''), $transformer->transform($this->createCouponDto()));
    }

    private function createCouponDto(): OrderCouponDto
    {
        return new OrderCouponDto('', 'Slevový kupon č. BOUT100 - Sleva 100 Kč', -100.0, -82.64, 21.00);
    }

    private function createCustomerService(int $customerId): CustomerService
    {
        /** @var CustomerService $customerService */
        $customerService = self::getContainer()->get(CustomerService::class);
        $customerService->setCustomerId($customerId);

        return $customerService;
    }

    /**
     * @return array<string, mixed>
     */
    private function expectedArray(string $modifiedSku): array
    {
        return [
            'discount' => '100.0000',
            'code' => 'discount-coupon',
            'campaign' => null,
            'sku' => $modifiedSku,
            'description' => 'Slevový kupon č. BOUT100 - Sleva 100 Kč',
        ];
    }
}
