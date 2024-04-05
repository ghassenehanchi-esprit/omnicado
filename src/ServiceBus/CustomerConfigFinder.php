<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus;

use Elasticr\ServiceBus\ServiceBus\Contract\ConfigContract;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerConfigRepository;
use Exception;

final class CustomerConfigFinder
{
    private CustomerConfigRepository $customerConfigRepository;

    public function __construct(CustomerConfigRepository $customerConfigRepository)
    {
        $this->customerConfigRepository = $customerConfigRepository;
    }

    public function find(int $customerId, string $configName): ConfigContract
    {
        $configs = $this->customerConfigRepository->findByCustomerId($customerId);

        if ($configs === null) {
            throw new Exception('Customer: ' . $customerId . ' config was not found');
        }

        return $configs->config($configName);
    }
}
