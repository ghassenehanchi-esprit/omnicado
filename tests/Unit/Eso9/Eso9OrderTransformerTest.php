<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\Eso9\Transforming\Eso9BaseOrderTransformer;
use Elasticr\ServiceBus\Eso9\Transforming\Eso9OrderTransformer;
use Elasticr\ServiceBus\ServiceBus\Constant\AttachmentTypes;
use Elasticr\ServiceBus\ServiceBus\CustomerService;
use Elasticr\ServiceBus\ServiceBus\Model\AttachmentDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderCouponDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\TestingAddressTrait;

final class Eso9OrderTransformerTest extends KernelTestCase
{
    use TestingAddressTrait;

    private Eso9OrderTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Eso9BaseOrderTransformer $baseTransformer */
        $baseTransformer = self::getContainer()->get(Eso9BaseOrderTransformer::class);

        /** @var CustomerService $customerService' */
        $customerService = self::getContainer()->get(CustomerService::class);
        $customerService->setCustomerId(1);

        $this->transformer = new Eso9OrderTransformer([$baseTransformer], $customerService);
    }

    /**
     * @test
     */
    public function it_transforms_order_dto_to_eso9_data(): void
    {
        $this->assertEquals($this->expectedArray(), $this->transformer->transform($this->createOrderDto()));
    }

    /**
     * @test
     */
    public function it_throws_exception_if_currency_is_not_czk(): void
    {
        $expectedArray = $this->expectedArray();
        $expectedArray['currency']['code'] = 'EUR';
        $this->assertEquals($expectedArray, $this->transformer->transform($this->createOrderDto('EUR')));
    }

    private function createOrderDto(string $currency = 'CZK'): OrderDto
    {
        return new OrderDto(
            1,
            'OBJ123456',
            $this->createAddress(),
            $this->createAddress(),
            $this->createPaymentMethodDto(),
            $this->createShippingMethodDto(),
            'created',
            'pending',
            1000.0,
            790.0,
            $currency,
            $this->createItemsDto(),
            Chronos::parse('2021-09-16 12:51:33'),
            $this->createNoteDto(),
            $this->createCouponDto(),
            $this->createAttachments(),
        );
    }

    private function createPaymentMethodDto(): PaymentMethodDto
    {
        return new PaymentMethodDto('payment-method', 'Payment method', new VatRateAwareValueDto(16.53, 20, 21.0));
    }

    private function createShippingMethodDto(): ShippingMethodDto
    {
        return new ShippingMethodDto('shipping-method', 'Shipping method', new VatRateAwareValueDto(66.12, 80, 21.0), '10001');
    }

    /**
     * @return OrderItemDto[]
     */
    private function createItemsDto(): array
    {
        $items = [];
        $items[] = new OrderItemDto(1, 'SKU-1', 'Product 1', 1, 'PIECE', 400, 330.58, 21, 69.42, 'supplier-test-1', 0.5000, 1);
        $items[] = new OrderItemDto(2, 'SKU-2', 'Product 2', 2, 'PIECE', 250, 206.61, 21, 43.39, 'supplier-test-2', 0.2000, 2);

        return $items;
    }

    private function createNoteDto(): OrderNoteDto
    {
        return new OrderNoteDto('testing note');
    }

    private function createCouponDto(): OrderCouponDto
    {
        return new OrderCouponDto('', 'Slevový kupon č. BOUT100 - Sleva 100 Kč', -100.0, -82.64, 21.00);
    }

    /**
     * @return AttachmentDto[]
     */
    private function createAttachments(): array
    {
        return [
            new AttachmentDto('file1.pdf', AttachmentTypes::PRINTABLE_DOCUMENT, 'hashedData1'),
            new AttachmentDto('file2.pdf', AttachmentTypes::PRINTABLE_DOCUMENT, 'hashedData2'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function expectedArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../expectations/eso9_order_data.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
