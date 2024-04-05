<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Entity;

use Elasticr\ServiceBus\ServiceBus\Contract\ConfigContract;
use Exception;

class CustomerConfig
{
    private int $id;

    private int $customerId;

    /**
     * @var ConfigContract[]
     */
    private array $configs;

    /**
     * @param ConfigContract[] $configs
     */
    public function __construct(int $customerId, array $configs)
    {
        $this->id = 0;
        $this->customerId = $customerId;
        $this->configs = $configs;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return ConfigContract[]
     */
    public function configs(): array
    {
        return $this->configs;
    }

    public function config(string $configName): ConfigContract
    {
        foreach ($this->configs as $config) {
            if ($config->name() === $configName) {
                return $config;
            }
        }

        throw new Exception('Config: ' . $configName . ' for customerId: ' . $this->customerId . ' was not found');
    }
}
