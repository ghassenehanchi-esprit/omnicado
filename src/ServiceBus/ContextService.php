<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus;

final class ContextService
{
    private ?string $context = null;

    public function context(): string
    {
        return $this->context ?: '';
    }

    public function setContext(string $context): void
    {
        $this->context = $context;
    }
}
