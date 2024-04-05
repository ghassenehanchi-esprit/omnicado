<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9\Factory;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\Eso9\Factory\Eso9OrderDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderCouponDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9OrderDtoFactoryTest extends KernelTestCase
{
    /**
     * @test
     * @param array<string, mixed> $data
     *
     * @dataProvider data
     **/
    public function it_creates_dto_from_data_array(OrderDto $expectedOrder, array $data): void
    {
        /** @var Eso9OrderDtoFactory $factory */
        $factory = $this->getContainer()->get(Eso9OrderDtoFactory::class);

        $this->assertEquals($expectedOrder, $factory->create($data));
    }

    /**
     * @return array<int, mixed>
     */
    public function data(): array
    {
        return [[$this->orderDto1(), $this->orderData1()], [$this->orderDto2(), $this->orderData2()]];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderData1(): array
    {
        return json_decode(
            '{"number":"4517","variable_symbol":"4517","total_price":397,"price_with_vat":397,"price_without_vat":309.95,"order_status":"on-hold","payment_status":"-999","created":"2022-12-12 00:00:00","note":"","customer_order_number":"4517","billing_address":{"firstname":"Joe","lastname":"Doe","email":"joe.doe@example.com","phone":"+420777888999","address":"Masarykova 2853","city":"Havl\u00ed\u010dk\u016fv Brod","zipcode":"58001","countrycode":"CZ","company_name":"KomTeSa spol. s r.o."},"shipping_address":{"firstname":"Joe","lastname":"Doe","email":"joe.doe@example.com","phone":"+420777888999","address":"Masarykova 2853","city":"Havl\u00ed\u010dk\u016fv Brod","zipcode":"58001","countrycode":"CZ","company_name":"KomTeSa spol. s r.o."},"shipping":{"code":"dpd_classic","price":79.89,"price_excl_vat":66.02,"tax_rate":21},"payment":{"code":"comgatebank","price":0,"price_excl_vat":0,"tax_rate":0},"currency":{"code":"CZK","round":0,"rate":1},"items":{"products":{"item":[{"sku":"product-1","ean":"4001499946103","title":"Product 1","price":346.03,"price_excl_vat":285.98,"tax_rate":21,"base_price_excl_vat":0,"cost_price_excl_vat":0,"suppliers":{"supplier":{"code":"Joe-Doe"}},"quantity":1,"size":0,"weight":0,"volume":0,"unit":"KS"},{"sku":"product-2","ean":"4009175165213","title":"Product 2","price":76.17,"price_excl_vat":62.95,"tax_rate":21,"base_price_excl_vat":0,"cost_price_excl_vat":0,"suppliers":{"supplier":{"code":"Joe-Doe"}},"quantity":1,"size":0,"weight":0,"volume":0,"unit":"KS"}]}},"coupons":{"item":[{"discount":105,"code":"discount-coupon","description":"zabunato","vat":0}]}}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function orderDto1(): OrderDto
    {
        $dtoBillingAddress = new AddressDto(
            'KomTeSa spol. s r.o.',
            'Joe',
            'Doe',
            'Masarykova 2853',
            '',
            'Havlíčkův Brod',
            '58001',
            'CZ',
            'joe.doe@example.com',
            '+420777888999',
            '',
            ''
        );
        $dtoShippingAddress = null;
        $dtoPayment = new PaymentMethodDto('comgatebank', '', new VatRateAwareValueDto(0, 0, 0));
        $dtoShippingMethod = new ShippingMethodDto('dpd_classic', '', new VatRateAwareValueDto(66.02, 79.89, 21.0), null);
        $dtoNotes = new OrderNoteDto('');
        $items = [new OrderItemDto(0, 'product-1', 'Product 1', 1.0, 'KS', 346.03, 285.98, 21.0, 60.05, null, 0, 0), new OrderItemDto(
            0,
            'product-2',
            'Product 2',
            1.0,
            'KS',
            76.17,
            62.95,
            21.0,
            13.22,
            null,
            0,
            0
        )];

        $coupon = new OrderCouponDto('zabunato', 'zabunato', -105.0, -105.0, 0.00);

        return new OrderDto(
            0,
            '4517',
            $dtoBillingAddress,
            $dtoShippingAddress,
            $dtoPayment,
            $dtoShippingMethod,
            'on-hold',
            '-999',
            397.00,
            309.95,
            'CZK',
            $items,
            Chronos::createFromFormat('Y-m-d H:i:s', '2022-12-12 00:00:00'),
            $dtoNotes,
            $coupon
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function orderData2(): array
    {
        return json_decode(
            '{"number":"4517","variable_symbol":"4517","total_price":397,"price_with_vat":397,"price_without_vat":309.95,"order_status":"on-hold","payment_status":"-999","created":"2022-12-12 00:00:00","note":"","customer_order_number":"4517","billing_address":{"firstname":"Joe","lastname":"Doe","email":"joe.doe@example.com","phone":"+420777888999","address":"Masarykova 2853","city":"Havl\u00ed\u010dk\u016fv Brod","zipcode":"58001","countrycode":"CZ","company_name":"KomTeSa spol. s r.o.","vat_number":"CZ45535663","cid_number":"45535663"},"shipping_address":{"firstname":"Jane","lastname":"Doe","email":"jane.doe@example.com","phone":"+420777888999","address":"Masarykova 2853","city":"Havl\u00ed\u010dk\u016fv Brod","zipcode":"58001","countrycode":"CZ","company_name":"KomTeSa spol. s r.o."},"shipping":{"code":"dpd_classic","price":79.89,"price_excl_vat":66.02,"tax_rate":21},"payment":{"code":"comgatebank","price":0,"price_excl_vat":0,"tax_rate":0},"currency":{"code":"CZK","round":0,"rate":1},"items":{"products":{"item":[{"sku":"product-1","ean":"4001499946103","title":"Product 1","price":346.03,"price_excl_vat":285.98,"tax_rate":21,"base_price_excl_vat":0,"cost_price_excl_vat":0,"suppliers":{"supplier":{"code":"Joe-Doe"}},"quantity":1,"size":0,"weight":0,"volume":0,"unit":"KS"},{"sku":"product-2","ean":"4009175165213","title":"Product 2","price":76.17,"price_excl_vat":62.95,"tax_rate":21,"base_price_excl_vat":0,"cost_price_excl_vat":0,"suppliers":{"supplier":{"code":"Joe-Doe"}},"quantity":1,"size":0,"weight":0,"volume":0,"unit":"KS"}]}},"coupons":{"item":[{"discount":105,"code":"discount-coupon","description":"zabunato","vat":0}]}}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function orderDto2(): OrderDto
    {
        $dtoBillingAddress = new AddressDto(
            'KomTeSa spol. s r.o.',
            'Joe',
            'Doe',
            'Masarykova 2853',
            '',
            'Havlíčkův Brod',
            '58001',
            'CZ',
            'joe.doe@example.com',
            '+420777888999',
            '45535663',
            'CZ45535663'
        );
        $dtoShippingAddress = new AddressDto('KomTeSa spol. s r.o.', 'Jane', 'Doe', 'Masarykova 2853', null, 'Havlíčkův Brod', '58001', 'CZ', null, null, null, null);
        $dtoPayment = new PaymentMethodDto('comgatebank', '', new VatRateAwareValueDto(0, 0, 0));
        $dtoShippingMethod = new ShippingMethodDto('dpd_classic', '', new VatRateAwareValueDto(66.02, 79.89, 21.0), null);
        $dtoNotes = new OrderNoteDto('');
        $items = [new OrderItemDto(0, 'product-1', 'Product 1', 1.0, 'KS', 346.03, 285.98, 21.0, 60.05, null, 0, 0), new OrderItemDto(
            0,
            'product-2',
            'Product 2',
            1.0,
            'KS',
            76.17,
            62.95,
            21.0,
            13.22,
            null,
            0,
            0
        )];

        $coupon = new OrderCouponDto('zabunato', 'zabunato', -105.0, -105.0, 0.00);

        return new OrderDto(
            0,
            '4517',
            $dtoBillingAddress,
            $dtoShippingAddress,
            $dtoPayment,
            $dtoShippingMethod,
            'on-hold',
            '-999',
            397.00,
            309.95,
            'CZK',
            $items,
            Chronos::createFromFormat('Y-m-d H:i:s', '2022-12-12 00:00:00'),
            $dtoNotes,
            $coupon
        );
    }
}
