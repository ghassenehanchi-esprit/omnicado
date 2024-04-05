<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api;

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClient;
use Automattic\WooCommerce\HttpClient\Response;
use Elasticr\ServiceBus\ServiceBus\CustomerService;
use Elasticr\ServiceBus\WooCommerce\Api\ApiClientFactory;
use Elasticr\ServiceBus\WooCommerce\Api\Filter\WooCommerceOrdersListFilter;
use Elasticr\ServiceBus\WooCommerce\Api\OrdersApiService;
use Elasticr\ServiceBus\WooCommerce\Api\PaymentGatewayApiService;
use Elasticr\ServiceBus\WooCommerce\Contract\PaymentMapperContract;
use Elasticr\ServiceBus\WooCommerce\Exception\WooCommerceApiException;
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

final class OrdersApiServiceTest extends KernelTestCase
{
    use WooCommerceConfigTrait;

    /**
     * @test
     */
    public function list_of_orders(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $productsApiData = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/list_orders.json') ?: '', false, 512, JSON_THROW_ON_ERROR);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $clientMock->method('get')->willReturnOnConsecutiveCalls($productsApiData, []);
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;

        $paymentApiServiceMock = $this->createMock(PaymentGatewayApiService::class);
        $paymentApiServiceMock->method('getPaymentGateways')->willReturn([]);

        $factory = $this->createOrderDtoFactory();
        $orderApiService = new OrdersApiService($clientFactoryMock, $factory, $paymentApiServiceMock);

        $products = $orderApiService->getOrders($this->getSimpleConfig(), new WooCommerceOrdersListFilter());

        $this->assertEquals(10, count($products));
    }

    /**
     * @test
     */
    public function update_order_status(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;

        $clientMock->method('put')->willReturnCallback(function (string $endpoint, array $data) {
            $this->assertEquals(['status' => 'finished'], $data);
        });

        $paymentApiServiceMock = $this->createMock(PaymentGatewayApiService::class);
        $paymentApiServiceMock->method('getPaymentGateways')->willReturn([]);

        $factory = $this->createOrderDtoFactory();
        $orderApiService = new OrdersApiService($clientFactoryMock, $factory, $paymentApiServiceMock);

        $orderApiService->updateOrderStatus($this->getSimpleConfig(), 1, 'finished');
    }

    /**
     * @test
     */
    public function update_order_status_failed(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $responseMock->method('getCode')->willReturn(400);
        $responseMock->method('getBody')->willReturn('{
            "code": "woocommerce_rest_shop_order_invalid_id",
            "message": "Neplatné ID.",
            "data": {
                "status": 400
            }
        }');
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;

        $clientMock->method('put')->willReturnCallback(function (string $endpoint, array $data) {
            $this->assertEquals(['status' => 'finished'], $data);
        });

        $paymentApiServiceMock = $this->createMock(PaymentGatewayApiService::class);
        $paymentApiServiceMock->method('getPaymentGateways')->willReturn([]);

        $factory = $this->createOrderDtoFactory();
        $orderApiService = new OrdersApiService($clientFactoryMock, $factory, $paymentApiServiceMock);

        $this->expectException(WooCommerceApiException::class);
        $this->expectExceptionMessage('Update order status failed: Neplatné ID.');

        $orderApiService->updateOrderStatus($this->getSimpleConfig(), 1, 'finished');
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
