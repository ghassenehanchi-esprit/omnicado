<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Cake\Chronos\Chronos;

final class DocumentDto
{
    private string $id;

    private string $number;

    private ?AddressDto $billingAddress;

    private ?AddressDto $shippingAddress;

    private ?PaymentMethodDto $paymentMethod;

    private ?ShippingMethodDto $shippingMethod;

    private ?PriceDto $price;

    private ?string $state;

    private Chronos $createdAt;

    /**
     * @var DocumentItemDto[]
     */
    private array $items;

    private ?string $paymentState;

    private string $note;

    /**
     * @var AttachmentDto[]
     */
    private array $attachments;

    private string $externalNumber;

    private ?string $stockroomId;

    private ?DocumentReferenceDto $origin;

    private string $supplier;

    private ?Chronos $sentAt;

    /**
     * @param DocumentItemDto[] $items
     * @param AttachmentDto[] $attachments
     */
    public function __construct(
        string $id,
        string $number,
        ?AddressDto $billingAddress,
        ?AddressDto $shippingAddress,
        ?PaymentMethodDto $paymentMethod,
        ?ShippingMethodDto $shippingMethod,
        ?PriceDto $price,
        array $items,
        Chronos $createdAt,
        ?DocumentReferenceDto $origin = null,
        string $supplier = '',
        string $note = '',
        ?string $state = null,
        ?string $paymentState = null,
        array $attachments = [],
        string $externalNumber = '',
        ?string $stockroomId = null,
        Chronos $sentAt = null,
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
        $this->paymentMethod = $paymentMethod;
        $this->shippingMethod = $shippingMethod;
        $this->price = $price;
        $this->items = $items;
        $this->state = $state;
        $this->paymentState = $paymentState;
        $this->createdAt = $createdAt;
        $this->note = $note;
        $this->attachments = $attachments;
        $this->externalNumber = $externalNumber;
        $this->stockroomId = $stockroomId;
        $this->origin = $origin;
        $this->supplier = $supplier;
        $this->sentAt = $sentAt;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function billingAddress(): ?AddressDto
    {
        return $this->billingAddress;
    }

    public function changeBillingAddress(?AddressDto $address): void
    {
        $this->billingAddress = $address;
    }

    public function shippingAddress(): ?AddressDto
    {
        return $this->shippingAddress;
    }

    public function changeShippingAddress(?AddressDto $address): void
    {
        $this->shippingAddress = $address;
    }

    public function paymentMethod(): ?PaymentMethodDto
    {
        return $this->paymentMethod;
    }

    public function shippingMethod(): ?ShippingMethodDto
    {
        return $this->shippingMethod;
    }

    public function price(): ?PriceDto
    {
        return $this->price;
    }

    /**
     * @return DocumentItemDto[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function paymentState(): ?string
    {
        return $this->paymentState;
    }

    public function createdAt(): Chronos
    {
        return $this->createdAt;
    }

    public function note(): string
    {
        return $this->note;
    }

    /**
     * @return AttachmentDto[]
     */
    public function attachments(): array
    {
        return $this->attachments;
    }

    public function addAttachment(AttachmentDto $attachmentDto): void
    {
        $this->attachments[] = $attachmentDto;
    }

    public function externalNumber(): string
    {
        return $this->externalNumber;
    }

    public function stockroomId(): ?string
    {
        return $this->stockroomId;
    }

    public function changeStockroomId(?string $stockroomId): void
    {
        $this->stockroomId = $stockroomId;
    }

    public function origin(): ?DocumentReferenceDto
    {
        return $this->origin;
    }

    public function supplier(): string
    {
        return $this->supplier;
    }

    public function sentAt(): ?Chronos
    {
        return $this->sentAt;
    }
}
