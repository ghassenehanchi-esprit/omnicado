<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Flexibee\CommandHandler;

use Elasticr\ServiceBus\Flexibee\Api\Filter\FlexibeeOrdersListFilter;
use Elasticr\ServiceBus\Flexibee\Api\PriceListApiService;
use Elasticr\ServiceBus\Flexibee\Api\PurchaseOrderApiService;
use Elasticr\ServiceBus\Flexibee\CommandBus\CommandHandler\UpdatePurchaseOrderStatusCommandHandler;
use Elasticr\ServiceBus\Flexibee\Factory\FlexibeeOrderDtoFactory;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeeConfig;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeePurchaseOrdersConfig;
use Elasticr\ServiceBus\ServiceBus\Command\UpdateOrderStatusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Model\OrderStatusDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FlexibeeUpdateOrderStatusCommandHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_handles_update_order_status_command(): void
    {
        $priceListApi = $this->createMock(PriceListApiService::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturnOnConsecutiveCalls(201, 200);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(
            new FlexibeeConfig('', '', [], null, new FlexibeePurchaseOrdersConfig(new FlexibeeOrdersListFilter(['TSE']), 'TSS', 'TSC'), [], [], [])
        );

        $ordersApiServiceMock = new PurchaseOrderApiService($httpClientMock, $orderDtoFactory, $priceListApi);
        $httpClientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $commandHandler = new UpdatePurchaseOrderStatusCommandHandler($ordersApiServiceMock, $customerConfigFinder);

        $updateOrderStatusCommand = $this->createMock(UpdateOrderStatusCommand::class);
        $updateOrderStatusCommand->method('orderStatusDto')->willReturn(new OrderStatusDto('AAA', 'uzavrena'));

        $commandHandler->__invoke($updateOrderStatusCommand);
    }
}
