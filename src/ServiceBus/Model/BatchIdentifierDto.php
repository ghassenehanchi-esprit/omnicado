<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Cake\Chronos\Chronos;

final class BatchIdentifierDto
{
    private string $code;

    private ?Chronos $expiration;

    public function __construct(string $code, ?Chronos $expiration)
    {
        $this->code = $code;
        $this->expiration = $expiration;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function expiration(): ?Chronos
    {
        return $this->expiration;
    }
}
