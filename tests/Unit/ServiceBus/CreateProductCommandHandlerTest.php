<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\ServiceBus;

use Elasticr\ServiceBus\Eso9\Client\Eso9Client;
use Elasticr\ServiceBus\Eso9\Client\Eso9ClientFactory;
use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\CommandBus\CommandHandler\CreateOrUpdateProductCommandHandler;
use Elasticr\ServiceBus\Eso9\Transforming\Eso9ProductTransformer;
use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\Command\CreateProductCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CreateProductCommandHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_handles_create_product_command(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactoryMock = $this->createMock(Eso9ProcedureDataFactory::class);
        $transformerMock = $this->createMock(Eso9ProductTransformer::class);
        $clientMock = $this->createMock(Eso9Client::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $commandHandler = new CreateOrUpdateProductCommandHandler($clientFactoryMock, $procedureDataFactoryMock, $transformerMock, $customerConfigFinder);

        $createOrderCommandMock = $this->createMock(CreateProductCommand::class);

        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser'));
        $transformerMock->expects($this->once())->method('transform');
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $clientMock->expects($this->once())->method('callProcedure');

        $commandHandler->__invoke($createOrderCommandMock);
    }
}
