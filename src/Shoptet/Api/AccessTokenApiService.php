<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Api;

use Elasticr\ServiceBus\Shoptet\Exception\AccessTokenException;
use Elasticr\ServiceBus\Shoptet\Model\AccessToken;
use Elasticr\ServiceBus\Shoptet\Provider\Contract\AuthTokenProviderContract;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AccessTokenApiService
{
    private string $accessTokenServerUrl;

    private AuthTokenProviderContract $authTokenProvider;

    private HttpClientInterface $httpClient;

    private CacheInterface $cache;

    public function __construct(string $accessTokenServerUrl, AuthTokenProviderContract $authTokenProvider, HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->accessTokenServerUrl = $accessTokenServerUrl;
        $this->authTokenProvider = $authTokenProvider;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function getAccessToken(int $eshopId): AccessToken
    {
        $accessToken = $this->cache->get(sprintf('shoptet.%s', $eshopId), function (ItemInterface $item) use ($eshopId) {
            $authToken = $this->authTokenProvider->getToken($eshopId);

            $response = $this->httpClient->request('GET', $this->accessTokenServerUrl, [
                'headers' => ['Authorization: Bearer ' . $authToken],
            ]);

            $responseArr = $response->toArray(false);
            if ($response->getStatusCode() !== 200) {
                throw new AccessTokenException($responseArr['error'], $responseArr['error_description']);
            }

            $item->expiresAfter($responseArr['expires_in']);

            return $responseArr['access_token'];
        });

        return new AccessToken($eshopId, $accessToken);
    }
}
