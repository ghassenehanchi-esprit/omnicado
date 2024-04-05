<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Shoptet\Facade;

use Cake\Chronos\Chronos;
use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Entity\OrderTransferRule;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\Repository\OrderTransferRuleRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\Shoptet\Api\OrdersApiService;
use Elasticr\ServiceBus\Shoptet\Facade\OrdersFacade;
use Elasticr\ServiceBus\Shoptet\Repository\SyncStatusRepository;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\TestingAddressTrait;

final class OrdersFacadeTest extends KernelTestCase
{
    use TestingAddressTrait;

    /**
     * @test
     */
    public function transfer_orders_by_transfer_rules(): void
    {
        $ordersApiServiceMock = $this->createMock(OrdersApiService::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);
        $ordersSyncStatusRepositoryMock = $this->createMock(SyncStatusRepository::class);
        $orderTransferRuleRepositoryMock = $this->createMock(OrderTransferRuleRepository::class);

        $orderTransferRuleRepositoryMock->expects($this->once())->method('rulesByCustomerId')->willReturn([
            new OrderTransferRule(1, '1', null, 'zaplaceno'),
            new OrderTransferRule(1, '2', 'dobirka', 'nezaplaceno'),
        ]);

        $ordersApiServiceMock->expects($this->exactly(2))->method('listOfOrders')->will($this->onConsecutiveCalls(
            [
                $this->createOrder('1', $this->createDobirkaPaymentMethod(), 'zaplaceno'),
                $this->createOrder('1', $this->createCreditCardPaymentMethod(), 'zaplaceno'),
                $this->createOrder('3', $this->createCreditCardPaymentMethod(), 'zaplaceno'),
            ],
            [
                $this->createOrder('2', $this->createDobirkaPaymentMethod(), 'nezaplaceno'),
                $this->createOrder('2', $this->createDobirkaPaymentMethod(), 'nezaplaceno'),
                $this->createOrder('2', $this->createCreditCardPaymentMethod(), 'nezaplaceno'),
                $this->createOrder('3', $this->createCreditCardPaymentMethod(), 'zaplaceno'),
            ],
        ));

        $customerRepository = $this->createMock(CustomerRepository::class);
        $customerRepository->expects($this->once())->method('findByCode')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);

        $customerConfigFinder->expects($this->atLeastOnce())->method('find')->willReturn(new ShoptetConfig(12345, 20, 30, 'paid'));

        $logger = self::getContainer()->get(ElasticrLogger::class);

        $serviceBusMock->expects($this->exactly(4))->method('dispatch');

        $ordersFacade = new OrdersFacade(
            $ordersApiServiceMock,
            $ordersSyncStatusRepositoryMock,
            $orderTransferRuleRepositoryMock,
            $serviceBusMock,
            $customerRepository,
            $customerConfigFinder,
            $logger
        );

        $ordersFacade->transferOrdersByTransferRules('1');
    }

    private function createOrder(string $status, PaymentMethodDto $paymentMethod, string $paymentStatus): OrderDto
    {
        return new OrderDto(
            0,
            'OBJ123456',
            $this->createAddress(),
            $this->createAddress(),
            $paymentMethod,
            $this->createShippingMethodDto(),
            $status,
            $paymentStatus,
            1000.0,
            790.0,
            'CZK',
            $this->createItemsDto(),
            Chronos::parse('2021-09-16 12:51:33'),
            $this->createNoteDto(),
        );
    }

    private function createShippingMethodDto(): ShippingMethodDto
    {
        return new ShippingMethodDto('shipping-method', 'Shipping method', new VatRateAwareValueDto(66.12, 80, 21.0), '10001');
    }

    private function createDobirkaPaymentMethod(): PaymentMethodDto
    {
        return new PaymentMethodDto('dobirka', 'Dobírka', new VatRateAwareValueDto(0, 0, 21.0));
    }

    private function createCreditCardPaymentMethod(): PaymentMethodDto
    {
        return new PaymentMethodDto('credit-card', 'Kartou online', new VatRateAwareValueDto(0, 0, 21.0));
    }

    /**
     * @return OrderItemDto[]
     */
    private function createItemsDto(): array
    {
        $items = [];
        $items[] = new OrderItemDto(1, 'SKU-1', 'Product 1', 1, 'PIECE', 400, 330.58, 21, 69.42, 'supplier-test-1', 0.5000, 1);
        $items[] = new OrderItemDto(2, 'SKU-2', 'Product 2', 2, 'PIECE', 250, 206.61, 21, 43.39, 'supplier-test-2', 0.2000, 2);

        return $items;
    }

    private function createNoteDto(): OrderNoteDto
    {
        return new OrderNoteDto('testing note');
    }
}
