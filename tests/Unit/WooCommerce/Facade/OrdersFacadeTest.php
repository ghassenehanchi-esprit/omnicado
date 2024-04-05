<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Facade;

use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\CustomerService;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\WooCommerce\Api\OrdersApiService;
use Elasticr\ServiceBus\WooCommerce\Contract\PaymentMapperContract;
use Elasticr\ServiceBus\WooCommerce\Facade\OrdersFacade;
use Elasticr\ServiceBus\WooCommerce\Factory\AddressDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\BaseShippingAddressDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\BaseShippingMethodDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\CouponDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\ShippingAddressDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommerceOrderDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommerceOrderItemDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommercePaymentMethodDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommerceShippingMethodDtoFactory;
use Elasticr\ServiceBus\WooCommerce\Repository\OrderSyncStatusRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api\WooCommerceConfigTrait;

final class OrdersFacadeTest extends KernelTestCase
{
    use WooCommerceConfigTrait;

    /**
     * @test
     */
    public function transfer_new_orders(): void
    {
        $ordersApiServiceMock = $this->createMock(OrdersApiService::class);
        $ordersSyncStatusRepositoryMock = $this->createMock(OrderSyncStatusRepository::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);
        $customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $customerRepositoryMock->expects($this->once())->method('findByCode')->willReturn(new Customer('Testing customer', '123456'));
        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinderMock->expects($this->atLeastOnce())->method('find')->willReturn($this->getSimpleConfig());

        $customerServiceMock = $this->createMock(CustomerService::class);
        $customerServiceMock->method('customerId')->willReturn(1);

        $serviceBusMock->expects($this->exactly(2))->method('dispatch');

        $ordersApiServiceMock->expects($this->exactly(1))->method('getOrders')->willReturn([$this->createOrder(), $this->createOrder()]);

        $facade = new OrdersFacade(
            $ordersApiServiceMock,
            $ordersSyncStatusRepositoryMock,
            $serviceBusMock,
            $customerRepositoryMock,
            $customerConfigFinderMock,
            $customerServiceMock
        );

        $facade->transferNewOrders('123456');
    }

    private function createOrder(): OrderDto
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/order.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        $factory = $this->createOrderDtoFactory();
        return $factory->createFromApiResponse($data, []);
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
