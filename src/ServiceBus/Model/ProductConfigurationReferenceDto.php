<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class ProductConfigurationReferenceDto
{
    private string $configuratorName;

    private string $configurationId;

    public function __construct(string $configuratorName, string $configurationId)
    {
        $this->configuratorName = $configuratorName;
        $this->configurationId = $configurationId;
    }

    public function configuratorName(): string
    {
        return $this->configuratorName;
    }

    public function configurationId(): string
    {
        return $this->configurationId;
    }
}
