<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Entity;

class OrderTransferRule
{
    private int $id;

    private int $customerId;

    private string $status;

    private ?string $payment;

    private ?string $paymentStatus;

    public function __construct(int $customerId, string $status, ?string $payment, ?string $paymentStatus)
    {
        $this->id = 0;
        $this->customerId = $customerId;
        $this->status = $status;
        $this->payment = $payment;
        $this->paymentStatus = $paymentStatus;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function payment(): ?string
    {
        return $this->payment;
    }

    public function paymentStatus(): ?string
    {
        return $this->paymentStatus;
    }
}
