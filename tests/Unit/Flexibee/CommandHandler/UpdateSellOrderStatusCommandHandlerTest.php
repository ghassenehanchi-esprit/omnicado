<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Flexibee\CommandHandler;

use Elasticr\ServiceBus\Flexibee\Api\SellOrderApiService;
use Elasticr\ServiceBus\Flexibee\CommandBus\CommandHandler\UpdateSellOrderStatusCommandHandler;
use Elasticr\ServiceBus\ServiceBus\Command\UpdateSellOrderStatusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Model\OrderStatusDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\Flexibee\Api\FlexibeeConfigTrait;

final class UpdateSellOrderStatusCommandHandlerTest extends KernelTestCase
{
    use FlexibeeConfigTrait;

    /**
     * @test
     */
    public function it_handles_update_status_fullfilled(): void
    {
        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $commandHandler = new UpdateSellOrderStatusCommandHandler($sellOrderApiServiceMock, $customerConfigFinder);

        $commandMock = $this->createMock(UpdateSellOrderStatusCommand::class);
        $commandMock->expects($this->exactly(2))
            ->method('orderStatusDto')
            ->willReturn(new OrderStatusDto('ABC123', 'splněno'));

        $customerConfigFinder->expects($this->once())->method('find')->willReturn($this->getConfig());
        $sellOrderApiServiceMock->expects($this->once())->method('updateOrderLabels');

        $commandHandler->__invoke($commandMock);
    }

    /**
     * @test
     */
    public function it_handles_update_status_other(): void
    {
        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $commandHandler = new UpdateSellOrderStatusCommandHandler($sellOrderApiServiceMock, $customerConfigFinder);

        $commandMock = $this->createMock(UpdateSellOrderStatusCommand::class);
        $commandMock->expects($this->exactly(1))
            ->method('orderStatusDto')
            ->willReturn(new OrderStatusDto('ABC123', 'částečně splněno'));

        $customerConfigFinder->expects($this->once())->method('find')->willReturn($this->getConfig());
        $sellOrderApiServiceMock->expects($this->never())->method('updateOrderLabels');

        $commandHandler->__invoke($commandMock);
    }
}
