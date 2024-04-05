<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Support\Provider;

use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Exception\CustomerNotFoundException;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;

final class CustomerProvider
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {
    }

    public function provideByCode(string $customerCode): Customer
    {
        $customer = $this->customerRepository->findByCode($customerCode);

        if ($customer === null) {
            throw new CustomerNotFoundException('Customer with code: ' . $customerCode . ' does not exist');
        }

        return $customer;
    }

    public function provideById(int $customerId): Customer
    {
        $customer = $this->customerRepository->findById($customerId);

        if ($customer === null) {
            throw new CustomerNotFoundException('Customer with id: ' . $customerId . ' does not exist');
        }

        return $customer;
    }
}
