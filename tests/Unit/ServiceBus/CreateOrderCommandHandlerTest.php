<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\ServiceBus;

use Elasticr\ServiceBus\Eso9\Client\Eso9Client;
use Elasticr\ServiceBus\Eso9\Client\Eso9ClientFactory;
use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\CommandBus\CommandHandler\CreateOrderCommandHandler;
use Elasticr\ServiceBus\Eso9\Transforming\Eso9OrderTransformer;
use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\Command\CreateOrderCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\CustomerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CreateOrderCommandHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_handles_create_order_command(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactoryMock = $this->createMock(Eso9ProcedureDataFactory::class);
        $transformerMock = $this->createMock(Eso9OrderTransformer::class);
        $clientMock = $this->createMock(Eso9Client::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $customerService = $this->createMock(CustomerService::class);

        $commandHandler = new CreateOrderCommandHandler($clientFactoryMock, $procedureDataFactoryMock, $transformerMock, $customerConfigFinder, $customerService);

        $createOrderCommandMock = $this->createMock(CreateOrderCommand::class);

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser'));
        $transformerMock->expects($this->once())->method('transform');
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $clientMock->expects($this->once())->method('callProcedure');

        $commandHandler->__invoke($createOrderCommandMock);
    }
}
