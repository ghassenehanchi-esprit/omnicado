<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\ValueObject;

final class OrderMask
{
    private string $status;

    private ?string $payment;

    private ?string $paymentStatus;

    public function __construct(string $status, ?string $payment, ?string $paymentStatus)
    {
        $this->status = $status;
        $this->payment = $payment;
        $this->paymentStatus = $paymentStatus;
    }

    public function match(self $mask): bool
    {
        return $this->matchStatus($mask->status) && $this->matchPayment($mask->payment) && $this->matchPaymentStatus($mask->paymentStatus);
    }

    private function matchStatus(string $status): bool
    {
        return $this->status === $status;
    }

    private function matchPayment(?string $payment): bool
    {
        if ($payment === null || $this->payment === null) {
            return true;
        }

        return $this->payment === $payment;
    }

    private function matchPaymentStatus(?string $paymentStatus): bool
    {
        if ($paymentStatus === null || $this->paymentStatus === null) {
            return true;
        }

        return $this->paymentStatus === $paymentStatus;
    }
}
