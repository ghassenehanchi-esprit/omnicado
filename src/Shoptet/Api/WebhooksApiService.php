<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Api;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\Shoptet\Exception\WebhookApiException;
use Elasticr\ServiceBus\Shoptet\Model\Webhook;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebhooksApiService
{
    public const API_URL = ShoptetApi::API_URL . '/api/webhooks';

    private HttpClientInterface $httpClient;

    private ShoptetApi $shoptetApi;

    public function __construct(ShoptetApi $shoptetApi, HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->shoptetApi = $shoptetApi;
    }

    public function registerWebhook(int $eshopId, string $event, string $url): int
    {
        $response = $this->httpClient->request('POST', self::API_URL, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
            'json' => [
                'data' => [
                    [
                        'event' => $event,
                        'url' => $url,
                    ],
                ],
            ],
        ]);

        $responseArr = $response->toArray(false);
        if ($response->getStatusCode() !== 201) {
            throw new WebhookApiException($responseArr['errors'][0]['message']);
        }

        return $responseArr['data']['webhooks'][0]['id'];
    }

    /**
     * @return Webhook[]
     */
    public function listWebhooks(int $eshopId): array
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
        ]);

        $responseArr = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new WebhookApiException($responseArr['errors'][0]['message']);
        }

        $webhooks = [];

        foreach ($responseArr['data']['webhooks'] as $item) {
            $webhooks[] = new Webhook($item['id'], $item['event'], $item['url'], new Chronos($item['created']));
        }

        return $webhooks;
    }

    public function deleteWebhook(int $eshopId, int $webhookId): void
    {
        $url = sprintf(self::API_URL . '/%s', $webhookId);
        $response = $this->httpClient->request('DELETE', $url, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
        ]);

        $responseArr = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new WebhookApiException($responseArr['errors'][0]['message']);
        }
    }
}
