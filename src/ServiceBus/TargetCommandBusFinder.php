<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus;

use Elasticr\ServiceBus\ServiceBus\Exception\FindTargetCommandBusException;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerConfigRepository;

final class TargetCommandBusFinder
{
    private CustomerConfigRepository $customerConfigRepository;

    public function __construct(CustomerConfigRepository $customerConfigRepository)
    {
        $this->customerConfigRepository = $customerConfigRepository;
    }

    public function find(int $customerId, string $commandBusId): string
    {
        $customerConfig = $this->customerConfigRepository->findByCustomerId($customerId);

        if (!$customerConfig) {
            throw new FindTargetCommandBusException('Customer: ' . $customerId . ' config was not found');
        }

        if (count($customerConfig->configs()) !== 2) {
            throw new FindTargetCommandBusException('Customer: ' . $customerId . ' config doest not exists');
        }

        $isValid = false;
        foreach ($customerConfig->configs() as $config) {
            if ($config->name() === $commandBusId) {
                $isValid = true;
            }
        }

        if ($isValid === false) {
            throw new FindTargetCommandBusException('Command bus ' . $commandBusId . ' for customer: ' . $customerId . ' was not found');
        }

        foreach ($customerConfig->configs() as $config) {
            if ($config->name() !== $commandBusId) {
                return $config->name() . '.command.bus';
            }
        }

        throw new FindTargetCommandBusException('Target command bus for customer: ' . $customerId . ' was not found');
    }
}
