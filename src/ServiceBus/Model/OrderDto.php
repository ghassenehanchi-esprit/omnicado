<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Cake\Chronos\Chronos;

final class OrderDto
{
    private int $id;

    private string $number;

    private AddressDto $billingAddress;

    private ?AddressDto $shippingAddress;

    private PaymentMethodDto $paymentMethod;

    private ShippingMethodDto $shippingMethod;

    private float $price;

    private float $priceWithoutVat;

    private string $currency;

    private string $status;

    private Chronos $creationTime;

    /**
     * @var OrderItemDto[]
     */
    private array $items;

    private string $paymentStatus;

    private OrderNoteDto $note;

    private ?OrderCouponDto $coupon;

    /**
     * @var AttachmentDto[]
     */
    private array $attachments;

    private string $externalNumber;

    private int $channel;

    /**
     * @param OrderItemDto[] $items
     * @param AttachmentDto[] $attachments
     */
    public function __construct(
        int $id,
        string $number,
        AddressDto $billingAddress,
        ?AddressDto $shippingAddress,
        PaymentMethodDto $paymentMethod,
        ShippingMethodDto $shippingMethod,
        string $status,
        string $paymentStatus,
        float $price,
        float $priceWithoutVat,
        string $currency,
        array $items,
        Chronos $creationTime,
        OrderNoteDto $note,
        ?OrderCouponDto $coupon = null,
        array $attachments = [],
        string $externalNumber = '',
        int $channel = 0
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
        $this->paymentMethod = $paymentMethod;
        $this->shippingMethod = $shippingMethod;
        $this->price = $price;
        $this->priceWithoutVat = $priceWithoutVat;
        $this->currency = $currency;
        $this->items = $items;
        $this->status = $status;
        $this->paymentStatus = $paymentStatus;
        $this->creationTime = $creationTime;
        $this->note = $note;
        $this->coupon = $coupon;
        $this->attachments = $attachments;
        $this->externalNumber = $externalNumber;
        $this->channel = $channel;
    }

    public function __toString()
    {
        return $this->number();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function billingAddress(): AddressDto
    {
        return $this->billingAddress;
    }

    public function shippingAddress(): ?AddressDto
    {
        return $this->shippingAddress;
    }

    public function paymentMethod(): PaymentMethodDto
    {
        return $this->paymentMethod;
    }

    public function shippingMethod(): ShippingMethodDto
    {
        return $this->shippingMethod;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function priceWithoutVat(): float
    {
        return $this->priceWithoutVat;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @return OrderItemDto[]
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @param OrderItemDto[] $items
     */
    public function addItems(array $items): void
    {
        $this->items = $items;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function paymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function creationTime(): Chronos
    {
        return $this->creationTime;
    }

    public function note(): OrderNoteDto
    {
        return $this->note;
    }

    public function coupon(): ?OrderCouponDto
    {
        return $this->coupon;
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

    public function channel(): int
    {
        return $this->channel;
    }

    public function addChannel(int $channel): void
    {
        $this->channel = $channel;
    }

    public function changeShippingMethod(ShippingMethodDto $shippingMethodDto): void
    {
        $this->shippingMethod = $shippingMethodDto;
    }
}
