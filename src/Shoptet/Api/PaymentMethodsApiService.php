<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Api;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PaymentMethodsApiService
{
    /**
     * @var string
     */
    private const API_URL = ShoptetApi::API_URL . '/api/payment-methods';

    private HttpClientInterface $httpClient;

    private ShoptetApi $shoptetApi;

    public function __construct(ShoptetApi $shoptetApi, HttpClientInterface $httpClient)
    {
        $this->shoptetApi = $shoptetApi;
        $this->httpClient = $httpClient;
    }

    /**
     * @return array<int, mixed>
     */
    public function listOfPaymentMethods(int $eshopId): array
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
        ]);

        $responseAsArray = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new Exception($responseAsArray['errors'][0]['message']);
        }

        return $responseAsArray['data']['paymentMethods'];
    }
}
