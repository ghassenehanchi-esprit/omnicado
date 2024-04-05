<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Entity;

class Customer
{
    private int $id;

    private string $name;

    private string $code;

    public function __construct(string $name, string $code)
    {
        $this->id = 0;
        $this->name = $name;
        $this->code = $code;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function code(): string
    {
        return $this->code;
    }
}
