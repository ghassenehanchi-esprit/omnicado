<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class DocumentItemDto
{
    private string $id;

    private ?string $sku;

    private string $name;

    private float $quantity;

    private ?string $unit;

    private ?VatRateAwareValueDto $price;

    private ?string $supplier;

    private float $weight;

    private ?int $rowNumber;

    private ?string $stockItemId;

    private ?BatchIdentifierDto $batchIdentifier;

    /**
     * @var array<int,string>
     */
    private array $serialNumbers;

    private ?ProductConfigurationReferenceDto $productConfigurationReferenceDto;

    /**
     * @var array<string, mixed>
     */
    private ?array $coverPositionData;

    private ?DocumentItemReferenceDto $originItem;

    /**
     * @var string[]
     */
    private array $serialShippingContainerCodes;

    /**
     * @param array<int,string> $serialNumbers
     * @param array<string, mixed> $coverPositionData
     * @param string[] $serialShippingContainerCodes
     */
    public function __construct(
        string $id,
        ?string $sku,
        string $name,
        float $quantity,
        ?string $unit = '',
        ?VatRateAwareValueDto $price = null,
        ?string $supplier = null,
        float $weight = 0,
        ?DocumentItemReferenceDto $originItem = null,
        array $serialShippingContainerCodes = [],
        ?int $rowNumber = null,
        ?string $stockItemId = null,
        ?BatchIdentifierDto $batchIdentifier = null,
        array $serialNumbers = [],
        ?ProductConfigurationReferenceDto $productConfigurationReferenceDto = null,
        ?array $coverPositionData = null,
    ) {
        $this->id = $id;
        $this->sku = $sku;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->unit = $unit;
        $this->price = $price;
        $this->supplier = $supplier;
        $this->weight = $weight;
        $this->rowNumber = $rowNumber;
        $this->stockItemId = $stockItemId;
        $this->batchIdentifier = $batchIdentifier;
        $this->serialNumbers = $serialNumbers;
        $this->productConfigurationReferenceDto = $productConfigurationReferenceDto;
        $this->coverPositionData = $coverPositionData;
        $this->originItem = $originItem;
        $this->serialShippingContainerCodes = $serialShippingContainerCodes;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function sku(): ?string
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    public function unit(): ?string
    {
        return $this->unit;
    }

    public function price(): ?VatRateAwareValueDto
    {
        return $this->price;
    }

    public function supplier(): ?string
    {
        return $this->supplier;
    }

    public function weight(): float
    {
        return $this->weight;
    }

    public function rowNumber(): ?int
    {
        return $this->rowNumber;
    }

    public function stockItemId(): ?string
    {
        return $this->stockItemId;
    }

    public function changeStockItemId(string $stockItemId): void
    {
        $this->stockItemId = $stockItemId;
    }

    public function batchIdentifier(): ?BatchIdentifierDto
    {
        return $this->batchIdentifier;
    }

    /**
     * @return array<int,string>
     */
    public function serialNumbers(): array
    {
        return $this->serialNumbers;
    }

    public function productConfigurationReferenceDto(): ?ProductConfigurationReferenceDto
    {
        return $this->productConfigurationReferenceDto;
    }

    /**
     * @return ?array<string, mixed>
     */
    public function coverPositionData(): ?array
    {
        return $this->coverPositionData;
    }

    public function changeQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function originItem(): ?DocumentItemReferenceDto
    {
        return $this->originItem;
    }

    /**
     * @return string[]
     */
    public function serialShippingContainerCodes(): array
    {
        return $this->serialShippingContainerCodes;
    }

    public function serialShippingContainerCodesAsString(): string
    {
        return implode(',', $this->serialShippingContainerCodes);
    }
}
