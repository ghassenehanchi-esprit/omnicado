<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Shoptet;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Exception\CustomerNotFoundException;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\Shoptet\Api\PaymentMethodsApiService;
use Elasticr\ServiceBus\Shoptet\Api\ShippingMethodsApiService;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:shoptet:list-methods')]
final class ListShippingAndPaymentMethodsCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly ShippingMethodsApiService $shippingMethodsApiService,
        private readonly PaymentMethodsApiService $paymentMethodsApiService,
        private readonly CustomerRepository $customerRepository,
        private readonly CustomerConfigFinder $customerConfigFinder,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command lists shipping and payment methods')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Shoptet customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $customer = $this->customerRepository->findByCode($input->getArgument(self::CUSTOMER_CODE));

        if ($customer === null) {
            throw new CustomerNotFoundException('Customer with code: ' . $customer . ' does not exist');
        }

        /** @var ShoptetConfig $config */
        $config = $this->customerConfigFinder->find($customer->id(), ShoptetConfig::NAME);

        $shippingMethods = $this->shippingMethodsApiService->listOfShippingMethods($config->eshopId());
        $paymentMethods = $this->paymentMethodsApiService->listOfPaymentMethods($config->eshopId());
    }

    protected function failedMessage(InputInterface $input): string
    {
        return '';
    }

    protected function successfulMessage(): string
    {
        return '';
    }

    protected function context(): string
    {
        return '';
    }
}
