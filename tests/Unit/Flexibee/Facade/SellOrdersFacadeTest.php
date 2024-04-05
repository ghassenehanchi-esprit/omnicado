<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Flexibee\Facade;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\Flexibee\Api\SellOrderApiService;
use Elasticr\ServiceBus\Flexibee\Facade\SellOrdersFacade;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Exception\OrderExistsException;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\Flexibee\Api\FlexibeeConfigTrait;

final class SellOrdersFacadeTest extends KernelTestCase
{
    use FlexibeeConfigTrait;

    /**
     * @test
     */
    public function transfer_sell_orders(): void
    {
        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);

        $serviceBusMock->expects($this->exactly(2))->method('dispatch');

        $sellOrderApiServiceMock->expects($this->exactly(1))->method('getOrders')->willReturn([$this->createOrder(1), $this->createOrder(2)]);
        $sellOrderApiServiceMock->expects($this->exactly(2))->method('updateOrderLabels');

        $customerRepository = $this->createMock(CustomerRepository::class);
        $customerRepository->expects($this->once())->method('findByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->atLeastOnce())->method('find')->willReturn($this->getConfig());

        /** @var LoggerInterface $logger */
        $logger = self::getContainer()->get(LoggerInterface::class);

        $ordersFacade = new SellOrdersFacade($sellOrderApiServiceMock, $serviceBusMock, $customerRepository, $customerConfigFinder);

        $ordersFacade->transfer('123456');
    }

    /**
     * @test
     */
    public function mark_exists_advice_as_transfered(): void
    {
        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);

        $serviceBusMock->expects($this->exactly(2))->method('dispatch')->willThrowException(new OrderExistsException('ABC123'));

        $sellOrderApiServiceMock->expects($this->exactly(1))->method('getOrders')->willReturn([$this->createOrder(1), $this->createOrder(2)]);
        $sellOrderApiServiceMock->expects($this->exactly(2))->method('updateOrderLabels');

        $customerRepository = $this->createMock(CustomerRepository::class);
        $customerRepository->expects($this->once())->method('findByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->atLeastOnce())->method('find')->willReturn($this->getConfig());

        /** @var LoggerInterface $logger */
        $logger = self::getContainer()->get(LoggerInterface::class);

        $ordersFacade = new SellOrdersFacade($sellOrderApiServiceMock, $serviceBusMock, $customerRepository, $customerConfigFinder);

        $ordersFacade->transfer('123456');
    }

    private function createOrder(int $id): OrderDto
    {
        return new OrderDto(
            1,
            'ABC' . $id,
            new AddressDto('', 'Karel', 'Novák', 'Ulice', '123', 'Praha', '12345', 'CZ', null, null, null, null),
            null,
            new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
            new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), null),
            '',
            '',
            0,
            0,
            '',
            [new OrderItemDto(1, 'SKU1', 'Produkt 1', 1, 'KS', 0, 0, 0, 0, '', 0, 1), new OrderItemDto(2, 'SKU2', 'Produkt 2', 10, 'KS', 0, 0, 0, 0, '', 0, 2)],
            Chronos::now(),
            new OrderNoteDto('')
        );
    }
}
