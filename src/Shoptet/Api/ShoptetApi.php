<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Api;

final class ShoptetApi
{
    public const API_URL = 'https://api.myshoptet.com';

    private AccessTokenApiService $accessTokenApiService;

    public function __construct(AccessTokenApiService $accessTokenApiService)
    {
        $this->accessTokenApiService = $accessTokenApiService;
    }

    /**
     * @return string[]
     */
    public function getHeaders(int $eshopId): array
    {
        $accessToken = $this->accessTokenApiService->getAccessToken($eshopId)->getAccessToken();

        return ['Shoptet-Access-Token: ' . $accessToken, 'Content-Type: application/vnd.shoptet.v1.0'];
    }
}
