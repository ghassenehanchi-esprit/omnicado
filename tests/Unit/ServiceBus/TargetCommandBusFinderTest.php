<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\ServiceBus;

use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\Entity\CustomerConfig;
use Elasticr\ServiceBus\ServiceBus\Exception\FindTargetCommandBusException;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerConfigRepository;
use Elasticr\ServiceBus\ServiceBus\TargetCommandBusFinder;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TargetCommandBusFinderTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_finds_target_command_bus(): void
    {
        $customerConfigRepository = $this->createMock(CustomerConfigRepository::class);

        $targetCommandBusFinder = new TargetCommandBusFinder($customerConfigRepository);

        $customerConfigRepository->expects($this->once())->method('findByCustomerId')->willReturn(new CustomerConfig(123456, [
            new Eso9Config('', 'testingUser'),
            new ShoptetConfig(99008, 20, 30, 'paid'),
        ]));

        $this->assertSame('shoptet.command.bus', $targetCommandBusFinder->find(123456, 'eso9'));
    }

    /**
     * @test
     */
    public function it_throws_exceptions_if_customer_id_does_not_exist(): void
    {
        $customerConfigRepository = $this->createMock(CustomerConfigRepository::class);

        $targetCommandBusFinder = new TargetCommandBusFinder($customerConfigRepository);

        $customerConfigRepository->expects($this->once())->method('findByCustomerId')->willReturn(null);

        $this->expectException(FindTargetCommandBusException::class);
        $this->expectExceptionMessage('Customer: 666666 config was not found');

        $this->assertSame('shoptet.command.bus', $targetCommandBusFinder->find(666666, 'eso9'));
    }

    /**
     * @test
     */
    public function it_throws_exceptions_if_customer_does_not_have_configs(): void
    {
        $customerConfigRepository = $this->createMock(CustomerConfigRepository::class);

        $targetCommandBusFinder = new TargetCommandBusFinder($customerConfigRepository);

        $customerConfigRepository->expects($this->once())->method('findByCustomerId')->willReturn(new CustomerConfig(123456, []));

        $this->expectException(FindTargetCommandBusException::class);
        $this->expectExceptionMessage('Customer: 123456 config doest not exists');

        $this->assertSame('shoptet.command.bus', $targetCommandBusFinder->find(123456, 'eso9'));
    }

    /**
     * @test
     */
    public function it_throws_exceptions_if_customer_does_not_have_command_bus(): void
    {
        $customerConfigRepository = $this->createMock(CustomerConfigRepository::class);

        $targetCommandBusFinder = new TargetCommandBusFinder($customerConfigRepository);

        $customerConfigRepository->expects($this->once())->method('findByCustomerId')->willReturn(new CustomerConfig(123456, [
            new Eso9Config('', 'testingUser'),
            new ShoptetConfig(99008, 20, 30, 'paid'),
        ]));

        $this->expectException(FindTargetCommandBusException::class);
        $this->expectExceptionMessage('Command bus non-existing-command-bus for customer: 123456 was not found');

        $this->assertSame('shoptet.command.bus', $targetCommandBusFinder->find(123456, 'non-existing-command-bus'));
    }

    /**
     * @test
     */
    public function it_throws_exceptions_if_customer_does_not_have_config_for_two_different_systems(): void
    {
        $customerConfigRepository = $this->createMock(CustomerConfigRepository::class);

        $targetCommandBusFinder = new TargetCommandBusFinder($customerConfigRepository);

        $customerConfigRepository->expects($this->once())->method('findByCustomerId')->willReturn(new CustomerConfig(123456, [
            new Eso9Config('', 'testingUser'),
            new Eso9Config('', 'testingUser2'),
        ]));

        $this->expectException(FindTargetCommandBusException::class);
        $this->expectExceptionMessage('Target command bus for customer: 123456 was not found');

        $this->assertSame('shoptet.command.bus', $targetCommandBusFinder->find(123456, 'eso9'));
    }
}
