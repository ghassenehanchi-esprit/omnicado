<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class ProductCategoryDto
{
    private string $id;

    private string $code;

    private string $name;

    private ?string $parentId;

    public function __construct(string $id, string $code, string $name, ?string $parentId)
    {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->parentId = $parentId;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function parentId(): ?string
    {
        return $this->parentId;
    }
}
