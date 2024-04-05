<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\Eso9Client;
use Elasticr\ServiceBus\Eso9\Client\Eso9ClientFactory;
use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureResponseData;
use Elasticr\ServiceBus\Eso9\CommandBus\CommandHandler\GetAndUpdateAdviceStatusCommandHandler;
use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\Command\GetAndUpdateAdviceStatusCommand;
use Elasticr\ServiceBus\ServiceBus\Command\UpdateSellOrderStatusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Exception\GetAdviceStatusException;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GetAndUpdateAdviceStatusCommandHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_handles_get_and_update_advice_status(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactoryMock = $this->createMock(Eso9ProcedureDataFactory::class);
        $clientMock = $this->createMock(Eso9Client::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $commandHandler = new GetAndUpdateAdviceStatusCommandHandler($clientFactoryMock, $procedureDataFactoryMock, $customerConfigFinder, $serviceBusMock);

        $commandMock = $this->createMock(GetAndUpdateAdviceStatusCommand::class);

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser'));
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $clientMock->expects($this->once())->method('callProcedure')->willReturn(
            new Eso9ProcedureResponseData('
                { 
                   "Advice":[ 
                      { 
                         "Advice_Id":"AV20190091",
                         "Dodavatel":"Firma ABC",
                         "Poznamka":"",
                         "StavDokl": "splněno"
                      }
                   ]
                }
            ')
        );

        $serviceBusMock->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(
                function (UpdateSellOrderStatusCommand $command, $type) {
                    $this->assertEquals('AV20190091', $command->orderStatusDto()->orderNumber());
                    $this->assertEquals('splněno', $command->orderStatusDto()->status());
                }
            );

        $commandHandler->__invoke($commandMock);
    }

    /**
     * @test
     */
    public function it_handles_get_and_update_advice_status_with_exception(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactoryMock = $this->createMock(Eso9ProcedureDataFactory::class);
        $clientMock = $this->createMock(Eso9Client::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $commandHandler = new GetAndUpdateAdviceStatusCommandHandler($clientFactoryMock, $procedureDataFactoryMock, $customerConfigFinder, $serviceBusMock);

        $commandMock = $this->createMock(GetAndUpdateAdviceStatusCommand::class);

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser'));
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $clientMock->expects($this->once())->method('callProcedure')->willReturn(new Eso9ProcedureResponseData(null));

        $this->expectException(GetAdviceStatusException::class);

        $commandHandler->__invoke($commandMock);
    }
}
