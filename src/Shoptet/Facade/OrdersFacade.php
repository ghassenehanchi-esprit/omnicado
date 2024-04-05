<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Facade;

use Cake\Chronos\Chronos;
use Elasticr\Logger\ElasticrLogger;
use Elasticr\Logger\ValueObject\AuditLogExceptionErrorData;
use Elasticr\ServiceBus\ServiceBus\Command\CreateOrderCommand;
use Elasticr\ServiceBus\ServiceBus\Command\GetAndUpdateOrderStatusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Entity\OrderTransferRule;
use Elasticr\ServiceBus\ServiceBus\Exception\CreateOrderDataException;
use Elasticr\ServiceBus\ServiceBus\Exception\CreateOrderException;
use Elasticr\ServiceBus\ServiceBus\Exception\CustomerNotFoundException;
use Elasticr\ServiceBus\ServiceBus\Exception\OrderExistsException;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\Repository\OrderTransferRuleRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\ServiceBus\ValueObject\OrderMask;
use Elasticr\ServiceBus\Shoptet\Api\Filter\ShoptetOrdersListFilter;
use Elasticr\ServiceBus\Shoptet\Api\OrdersApiService;
use Elasticr\ServiceBus\Shoptet\Entity\OrdersSyncStatus;
use Elasticr\ServiceBus\Shoptet\Model\OrderStatusDto;
use Elasticr\ServiceBus\Shoptet\Repository\SyncStatusRepository;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Exception;

final class OrdersFacade
{
    private OrdersApiService $ordersApiService;

    private SyncStatusRepository $ordersSyncStatusRepository;

    private ServiceBus $serviceBus;

    private OrderTransferRuleRepository $orderTransferRuleRepository;

    private CustomerRepository $customerRepository;

    private CustomerConfigFinder $customerConfigFinder;

    private ElasticrLogger $logger;

    public function __construct(
        OrdersApiService $ordersApiService,
        SyncStatusRepository $ordersSyncStatusRepository,
        OrderTransferRuleRepository $orderTransferRuleRepository,
        ServiceBus $serviceBus,
        CustomerRepository $customerRepository,
        CustomerConfigFinder $customerConfigFinder,
        ElasticrLogger $logger
    ) {
        $this->ordersApiService = $ordersApiService;
        $this->ordersSyncStatusRepository = $ordersSyncStatusRepository;
        $this->serviceBus = $serviceBus;
        $this->customerRepository = $customerRepository;
        $this->customerConfigFinder = $customerConfigFinder;
        $this->orderTransferRuleRepository = $orderTransferRuleRepository;
        $this->logger = $logger;
    }

    /**
     * @return OrderDto[]
     */
    public function listNewOrders(string $customerCode): array
    {
        $customer = $this->customerRepository->findByCode($customerCode);

        if ($customer === null) {
            throw new CreateOrderException('Customer with code: ' . $customerCode . ' does not exist');
        }

        $syncStatus = $this->ordersSyncStatusRepository->findByCustomerId($customer->id());

        /** @var ShoptetConfig $config */
        $config = $this->customerConfigFinder->find($customer->id(), ShoptetConfig::NAME);

        // no sync has run yet
        if ($syncStatus === null || $syncStatus->lastProcessedOrderCreationTime() === null) {
            $ordersFilter = new ShoptetOrdersListFilter(null, Chronos::now());
        } else {
            $ordersFilter = new ShoptetOrdersListFilter($syncStatus->lastProcessedOrderCreationTime(), null);
        }

        return array_reverse($this->ordersApiService->listOfOrders($config->eshopId(), $ordersFilter));
    }

    public function transferNewOrders(string $customerCode): void
    {
        /** @var OrdersSyncStatus|null $syncStatus */
        $syncStatus = null;
        /** @var Chronos|null $lastProcessedOrderCreationTime */
        $lastProcessedOrderCreationTime = null;

        try {
            $customer = $this->customerRepository->findByCode($customerCode);

            if ($customer === null) {
                throw new CustomerNotFoundException('Customer with code: ' . $customerCode . ' does not exist');
            }

            $syncStatus = $this->ordersSyncStatusRepository->findByCustomerId($customer->id());

            if ($syncStatus === null) {
                $syncStatus = new OrdersSyncStatus($customer->id());
            }

            $orders = $this->listNewOrders($customer->code());

            $lastProcessedOrderCreationTime = $syncStatus->lastProcessedOrderCreationTime();

            foreach ($orders as $order) {
                $this->serviceBus->dispatch(new CreateOrderCommand($order, $customer->id()), 'shoptet');
                $lastProcessedOrderCreationTime = $order->creationTime();
            }

            $syncStatus->updateLastProcessedOrderCreationTime($lastProcessedOrderCreationTime);
            $this->ordersSyncStatusRepository->save($syncStatus);
        } catch (Exception $exception) {
            if ($syncStatus && $lastProcessedOrderCreationTime) {
                $syncStatus->updateLastProcessedOrderCreationTime($lastProcessedOrderCreationTime);
                $this->ordersSyncStatusRepository->save($syncStatus);
            }
            throw new CreateOrderException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function transferOrdersByTransferRules(string $customerCode): void
    {
        try {
            $customer = $this->customerRepository->findByCode($customerCode);

            if ($customer === null) {
                throw new CreateOrderException('Customer with code: ' . $customerCode . ' does not exist');
            }

            $syncRules = $this->orderTransferRuleRepository->rulesByCustomerId($customer->id());

            foreach ($syncRules as $syncRule) {
                $this->transferOrdersByTransferRule($syncRule, $customer);
            }
        } catch (Exception $e) {
            throw new CreateOrderException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function transferOrdersByTransferRule(OrderTransferRule $syncRule, Customer $customer): void
    {
        $status = $syncRule->status();

        /** @var ShoptetConfig $config */
        $config = $this->customerConfigFinder->find($syncRule->customerId(), ShoptetConfig::NAME);

        $ordersFilter = new ShoptetOrdersListFilter(null, null, (int) $status);
        $orders = array_reverse($this->ordersApiService->listOfOrders($config->eshopId(), $ordersFilter));
        $orderMask = new OrderMask($status, $syncRule->payment(), $syncRule->paymentStatus());

        foreach ($orders as $order) {
            try {
                if ($orderMask->match(new OrderMask($order->status(), $order->paymentMethod()->code(), $order->paymentStatus()))) {
                    $this->serviceBus->dispatch(new CreateOrderCommand($order, $syncRule->customerId()), 'shoptet');
                    $this->ordersApiService->updateOrderStatus(
                        $config->eshopId(),
                        $order->number(),
                        new OrderStatusDto($config->transferredOrderStatus(), $config->eshopId(), 'Transferred', $order->paymentStatus() === $config->paidStatusName())
                    );
                }
            } catch (OrderExistsException $orderExistsException) {
                $this->serviceBus->dispatch(new GetAndUpdateOrderStatusCommand($orderExistsException->orderNumber(), $syncRule->customerId()), 'shoptet');
            } catch (CreateOrderDataException $createOrderDataException) {
                $this->ordersApiService->updateOrderStatus(
                    $config->eshopId(),
                    $createOrderDataException->orderNumber(),
                    new OrderStatusDto($config->errorOrderStatus(), $config->eshopId(), 'Updated', null)
                );
            } catch (Exception $exception) {
                $this->logger->critical(
                    "Sync order {$order->number()} from Shoptet failed {$customer->code()}",
                    'elasticr.actions.service_bus.transfer_orders',
                    '',
                    null,
                    new AuditLogExceptionErrorData($exception)
                );

                continue;
            }
        }
    }
}
