<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\ServiceBus\Constant\AttachmentTypes;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\AttachmentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\PriceDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ValueDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\TestingAddressTrait;

final class DocumentDtoTest extends KernelTestCase
{
    use TestingAddressTrait;

    private DocumentDto $documentDto;

    protected function setUp(): void
    {
        parent::setUp();

        $documentItemDto = new DocumentItemDto('1', 'sku', 'name', 100.0, 'ks', new VatRateAwareValueDto(1000.0, 1210.0, 21.0), null, 10.0);

        $billingAddress = $this->createAddress();

        $this->documentDto = new DocumentDto('1', 'NUMBER123456', $billingAddress, $billingAddress, new PaymentMethodDto(
            'payment-method',
            'Payment method',
            new VatRateAwareValueDto(
                16.53,
                20.0,
                21.0
            )
        ), new ShippingMethodDto(
            'shipping-method',
            'Shipping method',
            new VatRateAwareValueDto(66.12, 80, 21.0),
            '10001'
        ), new PriceDto(new ValueDto(2000.0, 2420.0), 'CZK'), [$documentItemDto], Chronos::createFromFormat(
            'Y-m-d H:i:s',
            '2022-12-12 00:00:00'
        ), new DocumentReferenceDto('type', 'id'), 'supplier', 'Note', 'state', 'paymentState', [
            new AttachmentDto('file1.pdf', AttachmentTypes::PRINTABLE_DOCUMENT, 'hashedData1'),
        ], 'EXTERNAL_NUMBER_123456', 'stockroomId', Chronos::createFromFormat(
            'Y-m-d H:i:s',
            '2023-08-03 08:15:30'
        ));
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame('1', $this->documentDto->id());
        $this->assertSame('NUMBER123456', $this->documentDto->number());

        $billingAddress = $this->documentDto->billingAddress();

        $this->assertInstanceOf(AddressDto::class, $billingAddress);
        $this->assertSame('John', $billingAddress->firstName());
        $this->assertSame('Doe', $billingAddress->lastName());
        $this->assertSame('St. Johns', $billingAddress->street());
        $this->assertSame('12', $billingAddress->houseNumber());
        $this->assertSame('Doetown', $billingAddress->city());
        $this->assertSame('11122', $billingAddress->zipCode());
        $this->assertSame('CZE', $billingAddress->country());
        $this->assertSame('john@doe.com', $billingAddress->email());
        $this->assertSame('111222333', $billingAddress->phoneNumber());
        $this->assertSame('CZ12345678', $billingAddress->vatNumber());
        $this->assertSame('KomTeSa spol. s r.o.', $billingAddress->company());

        $shippingAddress = $this->documentDto->shippingAddress();

        $this->assertInstanceOf(AddressDto::class, $shippingAddress);
        $this->assertSame('John', $shippingAddress->firstName());
        $this->assertSame('Doe', $shippingAddress->lastName());
        $this->assertSame('St. Johns', $shippingAddress->street());
        $this->assertSame('12', $shippingAddress->houseNumber());
        $this->assertSame('Doetown', $shippingAddress->city());
        $this->assertSame('11122', $shippingAddress->zipCode());
        $this->assertSame('CZE', $shippingAddress->country());
        $this->assertSame('john@doe.com', $shippingAddress->email());
        $this->assertSame('111222333', $shippingAddress->phoneNumber());
        $this->assertSame('CZ12345678', $shippingAddress->vatNumber());
        $this->assertSame('KomTeSa spol. s r.o.', $shippingAddress->company());

        $paymentMethod = $this->documentDto->paymentMethod();

        $this->assertInstanceOf(PaymentMethodDto::class, $paymentMethod);
        $this->assertSame('payment-method', $paymentMethod->code());
        $this->assertSame('Payment method', $paymentMethod->name());
        $this->assertSame(20.0, $paymentMethod->price());
        $this->assertSame(16.53, $paymentMethod->priceWithoutVat());
        $this->assertSame(21.0, $paymentMethod->taxRate());

        $shippingMethod = $this->documentDto->shippingMethod();

        $this->assertInstanceOf(ShippingMethodDto::class, $shippingMethod);
        $this->assertSame('shipping-method', $shippingMethod->code());
        $this->assertSame('Shipping method', $shippingMethod->name());
        $this->assertSame(80.0, $shippingMethod->price());
        $this->assertSame(66.12, $shippingMethod->priceWithoutVat());
        $this->assertSame(21.0, $shippingMethod->taxRate());
        $this->assertSame('10001', $shippingMethod->branchId());

        $this->assertSame('state', $this->documentDto->state());
        $this->assertSame('paymentState', $this->documentDto->paymentState());

        $price = $this->documentDto->price();

        $this->assertNotNull($price);
        $this->assertSame(2420.0, $price->value()->withVat());
        $this->assertSame(2000.0, $price->value()->withoutVat());
        $this->assertSame('CZK', $price->currency());

        $this->assertEquals(Chronos::createFromFormat('Y-m-d H:i:s', '2022-12-12 00:00:00'), $this->documentDto->createdAt());

        $this->assertSame('Note', $this->documentDto->note());

        $this->assertCount(1, $this->documentDto->attachments());
        $this->assertCount(1, $this->documentDto->items());

        $this->assertSame('EXTERNAL_NUMBER_123456', $this->documentDto->externalNumber());

        $this->assertSame('stockroomId', $this->documentDto->stockroomId());

        $this->assertEquals(new DocumentReferenceDto('type', 'id'), $this->documentDto->origin());
        $this->assertSame('supplier', $this->documentDto->supplier());

        $this->assertNotNull($this->documentDto->sentAt());
        $this->assertTrue(Chronos::createFromFormat('Y-m-d H:i:s', '2023-08-03 08:15:30')->equals($this->documentDto->sentAt()));
    }
}
