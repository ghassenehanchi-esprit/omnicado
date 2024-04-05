<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class ProductDto
{
    private int $id;

    private string $sku;

    private string $name;

    private string $type;

    private string $group;

    private float $price;

    private float $priceWithoutVat;

    private float $taxRate;

    private float $weight;

    private string $unit;

    private float $stockQuantity;

    private string $ean;

    /**
     * @var ProductParameterDto[]
     */
    private array $parameters;

    /**
     * @var ProductAttributeDto[]
     */
    private array $attributes;

    /**
     * @param ProductParameterDto[] $parameters
     * @param ProductAttributeDto[] $attributes
     */
    public function __construct(
        int $id,
        string $sku,
        string $name,
        float $price,
        float $priceWithoutVat,
        float $taxRate,
        float $weight,
        string $unit,
        float $stockQuantity,
        string $type = '',
        string $group = '',
        string $ean = '',
        array $parameters = [],
        array $attributes = [],
    ) {
        $this->id = $id;
        $this->sku = $sku;
        $this->name = $name;
        $this->price = $price;
        $this->priceWithoutVat = $priceWithoutVat;
        $this->taxRate = $taxRate;
        $this->type = $type;
        $this->group = $group;
        $this->weight = $weight;
        $this->unit = $unit;
        $this->stockQuantity = $stockQuantity;
        $this->ean = $ean;
        $this->parameters = $parameters;
        $this->attributes = $attributes;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function group(): string
    {
        return $this->group;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function priceWithoutVat(): float
    {
        return $this->priceWithoutVat;
    }

    public function taxRate(): float
    {
        return $this->taxRate;
    }

    public function weight(): float
    {
        return $this->weight;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function stockQuantity(): float
    {
        return $this->stockQuantity;
    }

    public function ean(): string
    {
        return $this->ean;
    }

    /**
     * @return ProductParameterDto[]
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    public function addProductParameter(ProductParameterDto $productParameterDto): void
    {
        $this->parameters[] = $productParameterDto;
    }

    /**
     * @return ProductAttributeDto[]
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function addProductAttribute(ProductAttributeDto $productAttributeDto): void
    {
        $this->attributes[] = $productAttributeDto;
    }

    public function getProductParameter(string $name): ?ProductParameterDto
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->name() === $name) {
                return $parameter;
            }
        }

        return null;
    }
}
