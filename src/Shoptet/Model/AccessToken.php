<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Model;

final class AccessToken
{
    private int $eshopId;

    private string $accessToken;

    public function __construct(int $eshopId, string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->eshopId = $eshopId;
    }

    public function getEshopId(): int
    {
        return $this->eshopId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
