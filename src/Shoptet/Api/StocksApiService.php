<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Api;

use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;
use Elasticr\ServiceBus\Shoptet\Exception\StockQuantityUpdateException;
use Elasticr\ServiceBus\Shoptet\Transforming\ProductQuantityInStockTransformer;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class StocksApiService
{
    /**
     * @var string
     */
    private const API_URL = ShoptetApi::API_URL . '/api/stocks';

    /**
     * @var string
     */
    private const SHOPTET_PRODUCTS_QUANTITY_CACHE_KEY = 'shoptet_products_quantity';

    private HttpClientInterface $httpClient;

    private ShoptetApi $shoptetApi;

    private ProductQuantityInStockTransformer $quantityInStockTransformer;

    private CacheItemPoolInterface $cache;

    public function __construct(
        ShoptetApi $shoptetApi,
        HttpClientInterface $httpClient,
        ProductQuantityInStockTransformer $quantityInStockTransformer,
        CacheItemPoolInterface $cache
    ) {
        $this->shoptetApi = $shoptetApi;
        $this->httpClient = $httpClient;
        $this->quantityInStockTransformer = $quantityInStockTransformer;
        $this->cache = $cache;
    }

    public function getDefaultStockId(int $eshopId): int
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'headers' => $this->shoptetApi->getHeaders($eshopId),
        ]);

        $responseAsArray = $response->toArray(false);
        if ($response->getStatusCode() !== 200) {
            throw new Exception($responseAsArray['errors'][0]['message']);
        }

        return $responseAsArray['data']['defaultStockId'];
    }

    /**
     * @param array<ProductQuantityInStockDto> $productsQuantity
     */
    public function updateQuantityInStock(int $eshopId, int $stockId, array $productsQuantity): void
    {
        $url = sprintf('%s/%s/movements', self::API_URL, $stockId);

        $changedProductsQuantity = $this->getChangedProductsQuantity($eshopId, $productsQuantity);

        /** @var array<ProductQuantityInStockDto[]> $productsQuantityChunkedArray */
        $productsQuantityChunkedArray = array_chunk($changedProductsQuantity, 50);

        /** @var string[] $errors */
        $errors = [];
        foreach ($productsQuantityChunkedArray as $chunkedArrayItem) {
            $requestData = [];

            foreach ($chunkedArrayItem as $productQuantity) {
                $requestData[] = $this->quantityInStockTransformer->transform($productQuantity);
            }

            $response = $this->httpClient->request('PATCH', $url, [
                'headers' => $this->shoptetApi->getHeaders($eshopId),
                'json' => [
                    'data' => $requestData,
                ],
            ]);

            $responseAsArray = $response->toArray(false);
            if ($response->getStatusCode() !== 200) {
                $errors = array_merge($errors, array_map(fn (array $item) => $item['message'], $responseAsArray['errors']));
            }
        }

        if (!empty($errors)) {
            throw new StockQuantityUpdateException($errors);
        }
    }

    /**
     * @param array<ProductQuantityInStockDto> $productsQuantity
     * @return array<ProductQuantityInStockDto>
     */
    private function getChangedProductsQuantity(int $eshopId, array $productsQuantity): array
    {
        $changedProductsQuantity = [];

        /** @var CacheItem $cachedItem */
        $cachedItem = $this->cache->getItem($this->cacheId($eshopId));
        if (!$cachedItem->isHit()) {
            $this->saveProductsQuantityToCache($cachedItem, $productsQuantity);
            return $productsQuantity;
        }
        /** @var array<string, ProductQuantityInStockDto> $cachedProductsQuantity */
        $cachedProductsQuantity = unserialize($cachedItem->get());

        foreach ($productsQuantity as $productQuantity) {
            $cachedProductQuantity = $cachedProductsQuantity[$productQuantity->sku()] ?? null;

            if ($cachedProductQuantity === null || $productQuantity->realStock() !== $cachedProductQuantity->realStock()) {
                $changedProductsQuantity[] = $productQuantity;
            }
        }

        $this->saveProductsQuantityToCache($cachedItem, $productsQuantity);

        return $changedProductsQuantity;
    }

    /**
     * @param array<ProductQuantityInStockDto> $productsQuantity
     * @return array<string, ProductQuantityInStockDto>
     */
    private function getProductsQuantityIndexedBySku(array $productsQuantity): array
    {
        $indexedProductsQuantity = [];
        foreach ($productsQuantity as $product) {
            $indexedProductsQuantity[$product->sku()] = $product;
        }

        return $indexedProductsQuantity;
    }

    /**
     * @param array<ProductQuantityInStockDto> $productsQuantity
     */
    private function saveProductsQuantityToCache(CacheItem $cachedItem, array $productsQuantity): void
    {
        $cachedItem->set(serialize($this->getProductsQuantityIndexedBySku($productsQuantity)));
        $this->cache->save($cachedItem);
    }

    private function cacheId(int $eshopId): string
    {
        return self::SHOPTET_PRODUCTS_QUANTITY_CACHE_KEY . '_' . $eshopId;
    }
}
