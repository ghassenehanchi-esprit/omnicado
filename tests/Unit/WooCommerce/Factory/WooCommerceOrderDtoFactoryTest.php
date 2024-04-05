<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Factory;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\ServiceBus\CustomerService;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderCouponDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Elasticr\ServiceBus\WooCommerce\Api\Dto\PaymentGateway;
use Elasticr\ServiceBus\WooCommerce\Contract\PaymentMapperContract;
use Elasticr\ServiceBus\WooCommerce\Factory\AddressDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\BaseShippingAddressDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\BaseShippingMethodDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\CouponDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\ShippingAddressDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommerceOrderDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommerceOrderItemDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommercePaymentMethodDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommerceShippingMethodDtoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WooCommerceOrderDtoFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function create_dto_from_data_array(): void
    {
        $factory = $this->createOrderDtoFactory();

        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/order.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        $dtoBillingAddress = new AddressDto('', 'Test', 'Test', 'test 1234', null, 'test', '10000', 'CZ', 'test@test.cz', '+420 1234567', '45535663', 'CZ45535663');
        $dtoShippingAddress = new AddressDto('', 'Test', 'Test', 'test 1234', null, 'test', '10000', 'CZ', null, '+420 1234567', null, null);
        $dtoPayment = new PaymentMethodDto('comgate', 'Comgate platební karta', new VatRateAwareValueDto(0, 0, 0));
        $dtoShippingMethod = new ShippingMethodDto('toret_tcp_doruky', 'Česká pošta - Balík do ruky do 30 Kg', new VatRateAwareValueDto(60, 72.6, 21), null);
        $dtoNotes = new OrderNoteDto('');
        $items = [];
        $items[] = new OrderItemDto(49, '6779290', 'Frosch EKO dárková sada Oase Pomeranč', 1.0, 'ks', 379.90, 313.97, 21.0, 65.93, null, 0, 0);

        $coupon = new OrderCouponDto('zabunato', 'zabunato', -95, -78.51, 21);

        $dto = new OrderDto(
            4486,
            '4486',
            $dtoBillingAddress,
            $dtoShippingAddress,
            $dtoPayment,
            $dtoShippingMethod,
            'processing',
            'paid',
            357.00,
            295.00,
            'CZK',
            $items,
            Chronos::createFromFormat('Y-m-d\TH:i:s', '2022-12-02T08:25:49'),
            $dtoNotes,
            $coupon
        );

        $this->assertEquals($dto, $factory->createFromApiResponse($data, []));
    }

    /**
     * @test
     */
    public function create_dto_from_data_array_1(): void
    {
        $factory = $this->createOrderDtoFactory();

        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/order_4734.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        $dtoBillingAddress = new AddressDto(
            'ERGOTEP, družstvo invalidů',
            'Milan',
            'Resl',
            'Zábořská 93 1234',
            null,
            'Proseč',
            '53944',
            'CZ',
            'resl.m@ergotep.cz',
            '702247341',
            '',
            ''
        );
        $dtoShippingAddress = new AddressDto('ERGOTEP, družstvo invalidů', 'Milan', 'Resl', 'Zábořská 93 1234', null, 'Proseč', '53944', 'CZ', null, '702247341', null, null);
        $dtoPayment = new PaymentMethodDto('dobirka', 'Platba na dobírku', new VatRateAwareValueDto(29, 35.09, 21));
        $dtoShippingMethod = new ShippingMethodDto('dpd_kuryr', 'DPD Private: Zdarma', new VatRateAwareValueDto(0, 0, 0), null);
        $dtoNotes = new OrderNoteDto('');
        $items = [];
        $items[] = new OrderItemDto(75, '6768170', 'Frosch EKO Oase Levandule 90 ml', 1.0, 'ks', 226.9, 187.52, 21.0, 39.38, null, 0, 0);
        $items[] = new OrderItemDto(76, '6768197', 'Frosch EKO Univerzální čistič Levandule 1000 ml', 1.0, 'ks', 79.90, 66.03, 21.0, 13.87, null, 0, 0);
        $items[] = new OrderItemDto(77, '6771655', 'Frosch EKO Oase Levandule – náhradní náplň 90 ml', 2.0, 'ks', 345.8, 285.79, 21.0, 60.01, null, 0, 0);
        $items[] = new OrderItemDto(78, '6768171', 'Frosch EKO WC gel Levandule 750 ml', 1.0, 'ks', 85.90, 70.99, 21.0, 14.91, null, 0, 0);
        $items[] = new OrderItemDto(79, '6768179', 'Frosch EKO Levandulový hygienický čistič 500 ml', 1.0, 'ks', 107.9, 89.17, 21.0, 18.73, null, 0, 0);
        $items[] = new OrderItemDto(80, '6768206', 'Frosch EKO Tekuté mýdlo Levandule – náhradní náplň 500 ml', 1.0, 'ks', 88.9, 73.47, 21.0, 15.43, null, 0, 0);
        $items[] = new OrderItemDto(81, '6774573', 'Frosch EKO Senses Sprchový gel pánský 3v1 – náhradní náplň 500 ml', 1.0, 'ks', 107.9, 89.17, 21.0, 18.73, null, 0, 0);

        $dto = new OrderDto(
            4734,
            '4734',
            $dtoBillingAddress,
            $dtoShippingAddress,
            $dtoPayment,
            $dtoShippingMethod,
            'processing',
            'paid',
            1078.00,
            891.00,
            'CZK',
            $items,
            Chronos::createFromFormat('Y-m-d\TH:i:s', '2022-12-13T07:29:55'),
            $dtoNotes,
            null
        );

        $paymentGateways = [
            'dobirka' => new PaymentGateway('dobirka', 'Platba na dobírku', 'Dobírka', true),
        ];

        $this->assertEquals($dto, $factory->createFromApiResponse($data, $paymentGateways));
    }

    /**
     * @test
     */
    public function create_dto_from_data_array_order_items_with_quantity_more_than_one(): void
    {
        $factory = $this->createOrderDtoFactory();

        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/order_5129.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        $dtoBillingAddress = new AddressDto(
            'Vladimír Fischer',
            'Vladimír',
            'Fischer',
            'Nerudova 765/10 ',
            null,
            'Říčany',
            '25101',
            'CZ',
            'webovybalicek@gmail.com',
            '723933774',
            '03051820',
            ''
        );
        $dtoShippingAddress = new AddressDto('Vladimír Fischer', 'Vladimír', 'Fischer', 'Nerudova 765/10 ', null, 'Říčany', '25101', 'CZ', null, '723933774', null, null);
        $dtoPayment = new PaymentMethodDto('comgate', 'Comgate platební karta', new VatRateAwareValueDto(0, 0, 0));
        $dtoShippingMethod = new ShippingMethodDto('toret_tcp_doruky', 'Česká pošta - Balík do ruky do 30 Kg: Zdarma', new VatRateAwareValueDto(0, 0, 0), null);
        $dtoNotes = new OrderNoteDto('');
        $items = [];
        $items[] = new OrderItemDto(162, '6768765', 'Frosch EKO Senses Sprchový gel Aloe vera 300 ml', 3.0, 'ks', 257.7, 212.98, 21.0, 44.72, null, 0, 0);
        $items[] = new OrderItemDto(163, '6779290', 'Frosch EKO dárková sada Oase Pomeranč', 1.0, 'ks', 379.90, 313.97, 21.0, 65.93, null, 0, 0);
        $items[] = new OrderItemDto(164, '6775874', 'Frosch EKO dárková sada pánská 3v1', 2.0, 'ks', 407.8, 337.02, 21.0, 70.78, null, 0, 0);

        $dto = new OrderDto(
            5129,
            '5129',
            $dtoBillingAddress,
            $dtoShippingAddress,
            $dtoPayment,
            $dtoShippingMethod,
            'processing',
            'paid',
            1045.4,
            863.97,
            'CZK',
            $items,
            Chronos::createFromFormat('Y-m-d\TH:i:s', '2022-12-20T14:06:48'),
            $dtoNotes,
            null
        );

        $this->assertEquals($dto, $factory->createFromApiResponse($data, []));
    }

    /**
     * @test
     */
    public function create_dto_from_data_array_order_with_discount_coupon(): void
    {
        $factory = $this->createOrderDtoFactory();

        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/order_5024.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        $dtoBillingAddress = new AddressDto(
            'KomTeSa spol. s r.o.',
            'Jan',
            'Novák',
            'Masarykova 2853 43',
            null,
            'Havlíčkův Brod',
            '58001',
            'CZ',
            'Roundred115@seznam.cz',
            '+420776366366',
            '',
            ''
        );
        $dtoShippingAddress = new AddressDto(
            'KomTeSa spol. s r.o.',
            'Jan',
            'Novák',
            'Masarykova 2853 43',
            null,
            'Havlíčkův Brod',
            '58001',
            'CZ',
            null,
            '+420776366366',
            null,
            null
        );
        $dtoPayment = new PaymentMethodDto('comgate', 'Comgate platební karta', new VatRateAwareValueDto(0, 0, 0));
        $dtoShippingMethod = new ShippingMethodDto('dpd_kuryr', 'DPD Private: Zdarma', new VatRateAwareValueDto(0, 0, 0), null);
        $dtoNotes = new OrderNoteDto('Ano - Testovací objednávka 4');
        $items = [];
        $items[] = new OrderItemDto(144, '6768160', 'Frosch EKO Aloe vera lotion na mytí nádobí 750 ml', 2.0, 'ks', 151.8, 125.45, 21.0, 26.35, null, 0, 0);
        $items[] = new OrderItemDto(145, '6768163', 'Frosch EKO Čistič na kuchyně s přírodní sodou 500 ml', 1.0, 'ks', 92.90, 76.78, 21.0, 16.12, null, 0, 0);
        $items[] = new OrderItemDto(146, '6769146', 'Frosch EKO Oase Květ pomeranče – náhradní náplň 90 ml', 3.0, 'ks', 518.7, 428.68, 21.0, 90.02, null, 0, 0);
        $items[] = new OrderItemDto(147, '6775874', 'Frosch EKO dárková sada pánská 3v1', 1.0, 'ks', 203.90, 168.51, 21.0, 35.39, null, 0, 0);
        $items[] = new OrderItemDto(148, '6779290', 'Frosch EKO dárková sada Oase Pomeranč', 1.0, 'ks', 379.90, 313.97, 21.0, 65.93, null, 0, 0);

        $coupon = new OrderCouponDto('zabunato', 'zabunato', -336.8, -278.35, 21);

        $dto = new OrderDto(
            5024,
            '5024',
            $dtoBillingAddress,
            $dtoShippingAddress,
            $dtoPayment,
            $dtoShippingMethod,
            'cancelled',
            '',
            1010.4,
            835.04,
            'CZK',
            $items,
            Chronos::createFromFormat('Y-m-d\TH:i:s', '2022-12-19T18:14:22'),
            $dtoNotes,
            $coupon
        );

        $this->assertEquals($dto, $factory->createFromApiResponse($data, []));
    }

    private function createOrderDtoFactory(): WooCommerceOrderDtoFactory
    {
        /** @var AddressDtoFactory $addressDtoFactory */
        $addressDtoFactory = self::getContainer()->get(AddressDtoFactory::class);
        /** @var WooCommerceOrderItemDtoFactory $orderItemDtoFactory */
        $orderItemDtoFactory = self::getContainer()->get(WooCommerceOrderItemDtoFactory::class);

        /** @var WooCommercePaymentMethodDtoFactory $paymentMethodDtoFactory */
        $paymentMethodDtoFactory = self::getContainer()->get(WooCommercePaymentMethodDtoFactory::class);

        /** @var CustomerService $customerService */
        $customerService = self::getContainer()->get(CustomerService::class);
        $customerService->setCustomerId(1);

        /** @var BaseShippingMethodDtoFactory $baseShippingMethodDtoFactory */
        $baseShippingMethodDtoFactory = self::getContainer()->get(BaseShippingMethodDtoFactory::class);

        $shippingMethodDtoFactory = new WooCommerceShippingMethodDtoFactory([$baseShippingMethodDtoFactory], $customerService);

        /** @var PaymentMapperContract $paymentMapper */
        $paymentMapper = self::getContainer()->get(PaymentMapperContract::class);

        /** @var CouponDtoFactory $couponMethodDtoFactory */
        $couponMethodDtoFactory = self::getContainer()->get(CouponDtoFactory::class);

        /** @var BaseShippingAddressDtoFactory $baseShippingAddressDtoFactory */
        $baseShippingAddressDtoFactory = self::getContainer()->get(BaseShippingAddressDtoFactory::class);

        $shippingAddressDtoFactory = new ShippingAddressDtoFactory([$baseShippingAddressDtoFactory], $customerService);

        return new WooCommerceOrderDtoFactory(
            $addressDtoFactory,
            $orderItemDtoFactory,
            $paymentMethodDtoFactory,
            $shippingMethodDtoFactory,
            $couponMethodDtoFactory,
            $paymentMapper,
            $shippingAddressDtoFactory
        );
    }
}
