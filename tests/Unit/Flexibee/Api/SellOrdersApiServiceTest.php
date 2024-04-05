<?php

declare(strict_types=1);

namespace Unit\Flexibee\Api;

use Elasticr\ServiceBus\Flexibee\Api\PriceListApiService;
use Elasticr\ServiceBus\Flexibee\Api\SellOrderApiService;
use Elasticr\ServiceBus\Flexibee\Factory\FlexibeeOrderDtoFactory;
use Elasticr\ServiceBus\Flexibee\Factory\PriceListItemDtoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\Elasticr\ServiceBus\Unit\Flexibee\Api\FlexibeeConfigTrait;

final class SellOrdersApiServiceTest extends KernelTestCase
{
    use FlexibeeConfigTrait;

    /**
     * @test
     */
    public function list_of_orders(): void
    {
        $httpClientPriceListMock = $this->createMock(HttpClientInterface::class);
        $responsePriceListMock = $this->createMock(ResponseInterface::class);
        $responsePriceListMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_11.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responsePriceListMock->method('getStatusCode')->willReturn(200);
        $httpClientPriceListMock->expects($this->exactly(1))->method('request')->willReturn($responsePriceListMock);
        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientPriceListMock, $priceListItemDtoFactory);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/sell_order_list.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/sell_order_12088.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responseMock->method('getStatusCode')->willReturn(200);

        $service = new SellOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(2))->method('request')->willReturn($responseMock);

        $orders = $service->getOrders($this->getConfig());
        $this->assertEquals(1, count($orders));
        $this->assertEquals('210222000036', $orders[0]->number());
        $this->assertEquals(1, count($orders[0]->items()));
        $this->assertEquals(0.0, $orders[0]->paymentMethod()->price());
        $this->assertEquals(0.0, $orders[0]->shippingMethod()->price());
    }

    /**
     * @test
     */
    public function order_detail(): void
    {
        $httpClientPriceListMock = $this->createMock(HttpClientInterface::class);
        $responsePriceListMock = $this->createMock(ResponseInterface::class);
        $responsePriceListMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_11.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responsePriceListMock->method('getStatusCode')->willReturn(200);
        $httpClientPriceListMock->expects($this->exactly(1))->method('request')->willReturn($responsePriceListMock);
        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientPriceListMock, $priceListItemDtoFactory);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/sell_order_detail.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/sell_order_12088.json') ?: '', true, 512, JSON_THROW_ON_ERROR)
        );
        $responseMock->method('getStatusCode')->willReturn(200);

        $service = new SellOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(2))->method('request')->willReturn($responseMock);

        $order = $service->getOrderDetail($this->getConfig(), '210222000036');
        $this->assertNotNull($order);
        $this->assertEquals('210222000036', $order->number());
        $this->assertEquals(1, count($order->items()));
        $this->assertEquals(0.0, $order->paymentMethod()->price());
        $this->assertEquals(0.0, $order->shippingMethod()->price());
    }

    /**
     * @test
     */
    public function update_order_labels(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientMock, $priceListItemDtoFactory);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(201);

        $service = new SellOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(1))
            ->method('request')
            ->will($this->returnCallback(function ($method, $url, $options) use ($responseMock) {
                $this->assertEquals([
                    'winstrom' => [
                        'objednavka-vydana' => [
                            'id' => 'code:210222000036',
                            'stitky' => 'PRIJATO-NA-SKLAD',
                        ],
                    ],
                ], $options['json']);

                return $responseMock;
            }));

        $service->updateOrderLabels($this->getConfig(), '210222000036', 'PRIJATO-NA-SKLAD');
    }

    /**
     * @test
     */
    public function update_order_labels_with_note(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientMock, $priceListItemDtoFactory);

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/sell_order_detail.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            ['winstrom' => ['objednavka-prijata-polozka' => []]],
            []
        );
        $responseMock->method('getStatusCode')->willReturnOnConsecutiveCalls(200, 201);

        $service = new SellOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(3))
            ->method('request')
            ->willReturn($responseMock);

        $service->updateOrderLabels($this->getConfig(), '210222000036', 'PRIJATO-NA-SKLAD', 'NOTE');
    }
}
