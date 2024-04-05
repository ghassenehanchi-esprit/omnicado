<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Api;

use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderTrackingInfoDto;
use Elasticr\ServiceBus\Shoptet\Api\Filter\ShoptetOrdersListFilter;
use Elasticr\ServiceBus\Shoptet\Exception\OrderApiException;
use Elasticr\ServiceBus\Shoptet\Factory\ShoptetOrderDtoFactory;
use Elasticr\ServiceBus\Shoptet\Model\OrderStatusDto as ShoptetOrderStatusDto;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderStatusTransformer;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderTrackingInfoTransformer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OrdersApiService
{
    /**
     * @var string
     */
    public const API_URL = ShoptetApi::API_URL . '/api/orders';

    /**
     * @var array<int, string>
     */
    private const EXTRA_FIELDS = ['shippingDetails', 'notes'];

    private HttpClientInterface $httpClient;

    private ShoptetApi $shoptetApi;

    private ShoptetOrderDtoFactory $orderDtoFactory;

    private OrderTrackingInfoTransformer $orderTrackingInfoTransformer;

    private OrderStatusTransformer $orderStatusTransformer;

    public function __construct(
        ShoptetApi $shoptetApi,
        HttpClientInterface $httpClient,
        ShoptetOrderDtoFactory $orderDtoFactory,
        OrderTrackingInfoTransformer $orderTrackingInfoTransformer,
        OrderStatusTransformer $orderStatusTransformer
    ) {
        $this->httpClient = $httpClient;
        $this->shoptetApi = $shoptetApi;
        $this->orderDtoFactory = $orderDtoFactory;
        $this->orderTrackingInfoTransformer = $orderTrackingInfoTransformer;
        $this->orderStatusTransformer = $orderStatusTransformer;
    }

    public function getOrderDetail(int $eshopId, string $orderId): OrderDto
    {
        $url = sprintf(self::API_URL . '/%s', $orderId . '?include=' . implode(',', self::EXTRA_FIELDS));
        $response = $this->httpClient->request('GET', $url, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
        ]);

        $responseArr = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new OrderApiException($responseArr['errors'][0]['message']);
        }

        return $this->orderDtoFactory->createFromApiResponse($responseArr['data']['order']);
    }

    /**
     * @return OrderDto[]
     */
    public function listOfOrders(int $eshopId, ShoptetOrdersListFilter $filter = null): array
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
            'query' => $filter === null ? [] : $filter->toArray(),
        ]);

        $responseAsArray = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new OrderApiException($responseAsArray['errors'][0]['message']);
        }

        $ordersDataArray = $responseAsArray['data']['orders'];
        $orders = [];

        foreach ($ordersDataArray as $orderAsArray) {
            $orders[] = $this->getOrderDetail($eshopId, $orderAsArray['code']);
        }

        return $orders;
    }

    public function updateOrderStatus(int $eshopId, string $orderNumber, ShoptetOrderStatusDto $orderStatusDto): void
    {
        $url = sprintf('%s/%s/status', self::API_URL, $orderNumber);
        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
            'json' => [
                'data' => $this->orderStatusTransformer->transform($eshopId, $orderStatusDto),
            ],
        ]);

        $responseAsArray = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new OrderApiException($responseAsArray['errors'][0]['message']);
        }
    }

    public function updateTrackingInfo(int $eshopId, OrderTrackingInfoDto $trackingInfoDto): void
    {
        $url = sprintf('%s/%s/notes', self::API_URL, $trackingInfoDto->orderNumber());
        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
            'json' => [
                'data' => $this->orderTrackingInfoTransformer->transform($trackingInfoDto),
            ],
        ]);

        $responseAsArray = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new OrderApiException($responseAsArray['errors'][0]['message']);
        }
    }

    /**
     * @return ShoptetOrderStatusDto[]
     */
    public function listOfOrderStatuses(int $eshopId): array
    {
        $url = sprintf('%s/statuses', self::API_URL);
        $response = $this->httpClient->request('GET', $url, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
        ]);

        $responseAsArray = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new OrderApiException($responseAsArray['errors'][0]['message']);
        }

        $statusesDataArray = $responseAsArray['data']['statuses'];
        $statuses = [];

        foreach ($statusesDataArray as $statusAsArray) {
            $statuses[] = new ShoptetOrderStatusDto((int) $statusAsArray['id'], $eshopId, $statusAsArray['name'], $statusAsArray['markAsPaid']);
        }

        return $statuses;
    }
}
