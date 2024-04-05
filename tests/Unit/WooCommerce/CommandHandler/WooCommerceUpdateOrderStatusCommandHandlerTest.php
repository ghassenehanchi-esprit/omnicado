<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\CommandHandler;

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClient;
use Automattic\WooCommerce\HttpClient\Response;
use Elasticr\ServiceBus\ServiceBus\Command\UpdateOrderStatusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\WooCommerce\Api\ApiClientFactory;
use Elasticr\ServiceBus\WooCommerce\Api\OrdersApiService;
use Elasticr\ServiceBus\WooCommerce\Api\PaymentGatewayApiService;
use Elasticr\ServiceBus\WooCommerce\CommandBus\CommandHandler\UpdateOrderStatusCommandHandler;
use Elasticr\ServiceBus\WooCommerce\Factory\WooCommerceOrderDtoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api\WooCommerceConfigTrait;

final class WooCommerceUpdateOrderStatusCommandHandlerTest extends KernelTestCase
{
    use WooCommerceConfigTrait;

    /**
     * @test
     */
    public function it_handles_update_order_status_command(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;
        $clientMock->expects($this->once())->method('put');

        /** @var WooCommerceOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(WooCommerceOrderDtoFactory::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->once())->method('find')->willReturn($this->getSimpleConfig());

        $paymentApiServiceMock = $this->createMock(PaymentGatewayApiService::class);
        $paymentApiServiceMock->method('getPaymentGateways')->willReturn([]);

        $ordersApiServiceMock = new OrdersApiService($clientFactoryMock, $orderDtoFactory, $paymentApiServiceMock);

        $commandHandler = new UpdateOrderStatusCommandHandler($ordersApiServiceMock, $customerConfigFinder);

        $updateOrderStatusCommand = $this->createMock(UpdateOrderStatusCommand::class);

        $commandHandler->__invoke($updateOrderStatusCommand);
    }
}
