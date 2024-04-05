<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Shoptet\CommandHandler;

use Elasticr\ServiceBus\ServiceBus\Command\UpdateOrderStatusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\Shoptet\Api\OrdersApiService;
use Elasticr\ServiceBus\Shoptet\Api\ShoptetApi;
use Elasticr\ServiceBus\Shoptet\CommandBus\CommandHandler\UpdateOrderStatusCommandHandler;
use Elasticr\ServiceBus\Shoptet\Factory\ShoptetOrderDtoFactory;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderStatusTransformer;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderTrackingInfoTransformer;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ShoptetUpdateOrderStatusCommandHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_handles_update_order_status_command(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $orderDtoFactory = self::getContainer()->get(ShoptetOrderDtoFactory::class);
        $orderTrackingInfoTransformer = new OrderTrackingInfoTransformer();
        $orderStatusTransformer = new OrderStatusTransformer();

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(200);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new ShoptetConfig(12345, 20, 30, 'paid'));

        $ordersApiServiceMock = new OrdersApiService($shoptetApiMock, $httpClientMock, $orderDtoFactory, $orderTrackingInfoTransformer, $orderStatusTransformer);
        $httpClientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $commandHandler = new UpdateOrderStatusCommandHandler($ordersApiServiceMock, $customerConfigFinder);

        $updateOrderStatusCommand = $this->createMock(UpdateOrderStatusCommand::class);

        $commandHandler->__invoke($updateOrderStatusCommand);
    }
}
