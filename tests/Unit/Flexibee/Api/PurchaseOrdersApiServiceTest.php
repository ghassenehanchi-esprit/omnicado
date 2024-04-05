<?php

declare(strict_types=1);

namespace Unit\Flexibee\Api;

use Elasticr\ServiceBus\Flexibee\Api\PriceListApiService;
use Elasticr\ServiceBus\Flexibee\Api\PurchaseOrderApiService;
use Elasticr\ServiceBus\Flexibee\Exception\FlexibeeApiException;
use Elasticr\ServiceBus\Flexibee\Factory\FlexibeeOrderDtoFactory;
use Elasticr\ServiceBus\Flexibee\Factory\PriceListItemDtoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\Elasticr\ServiceBus\Unit\Flexibee\Api\FlexibeeConfigTrait;

final class PurchaseOrdersApiServiceTest extends KernelTestCase
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
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_109.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_171.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_170.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_109.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responsePriceListMock->method('getStatusCode')->willReturn(200);
        $httpClientPriceListMock->expects($this->exactly(4))->method('request')->willReturn($responsePriceListMock);
        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientPriceListMock, $priceListItemDtoFactory);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_list.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_12094.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_12095.json') ?: '', true, 512, JSON_THROW_ON_ERROR)
        );
        $responseMock->method('getStatusCode')->willReturn(200);

        $service = new PurchaseOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(3))->method('request')->willReturn($responseMock);

        $orders = $service->getOrders($this->getConfig());
        $this->assertEquals(2, count($orders));
        $this->assertEquals('110122006447', $orders[0]->number());
        $this->assertEquals(1, count($orders[0]->items()));
        $this->assertEquals(39.0, $orders[0]->paymentMethod()->price());
        $this->assertEquals(99.0, $orders[0]->shippingMethod()->price());
        $this->assertEquals('110122006448', $orders[1]->number());
        $this->assertEquals(1, count($orders[1]->items()));
        $this->assertEquals(0, $orders[1]->paymentMethod()->price());
        $this->assertEquals(0, $orders[1]->shippingMethod()->price());
    }

    /**
     * @test
     */
    public function order_detail(): void
    {
        $httpClientPriceListMock = $this->createMock(HttpClientInterface::class);
        $responsePriceListMock = $this->createMock(ResponseInterface::class);
        $responsePriceListMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_109.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_171.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_170.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responsePriceListMock->method('getStatusCode')->willReturn(200);
        $httpClientPriceListMock->expects($this->exactly(3))->method('request')->willReturn($responsePriceListMock);
        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientPriceListMock, $priceListItemDtoFactory);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_detail.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_12094.json') ?: '', true, 512, JSON_THROW_ON_ERROR)
        );
        $responseMock->method('getStatusCode')->willReturn(200);

        $service = new PurchaseOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(2))->method('request')->willReturn($responseMock);

        $order = $service->getOrder($this->getConfig(), '110122006447');
        $this->assertNotNull($order);
        $this->assertEquals('110122006447', $order->number());
        $this->assertEquals(1, count($order->items()));
        $this->assertEquals(39.0, $order->paymentMethod()->price());
        $this->assertEquals(99.0, $order->shippingMethod()->price());
    }

    /**
     * @test
     */
    public function order_with_exchangefee(): void
    {
        $httpClientPriceListMock = $this->createMock(HttpClientInterface::class);
        $responsePriceListMock = $this->createMock(ResponseInterface::class);
        $responsePriceListMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_170.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_171.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_254.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_109.json') ?: '', true, 512, JSON_THROW_ON_ERROR)
        );
        $responsePriceListMock->method('getStatusCode')->willReturn(200);
        $httpClientPriceListMock->method('request')->willReturn($responsePriceListMock);
        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientPriceListMock, $priceListItemDtoFactory);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_with_exchangefee.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_with_exchangefee.json') ?: '', true, 512, JSON_THROW_ON_ERROR)
        );
        $responseMock->method('getStatusCode')->willReturn(200);

        $service = new PurchaseOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(2))->method('request')->willReturn($responseMock);

        $order = $service->getOrder($this->getConfig(), '11122200000028');
        $this->assertNotNull($order);
        $this->assertEquals('11122200000028', $order->number());
        $this->assertEquals(2, count($order->items()));
        $this->assertEquals('EXCHANGEFEE', $order->items()[0]->sku());
        $this->assertEquals('Výměna poplatek', $order->items()[0]->name());
        $this->assertEquals(1, $order->items()[0]->amount());
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

        $service = new PurchaseOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(1))->method('request')->willReturn($responseMock);

        $service->updateOrderLabels($this->getConfig(), '110122006447', 'LABEL');
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
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/purchase_order_detail.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            ['winstrom' => ['objednavka-prijata-polozka' => []]],
            []
        );
        $responseMock->method('getStatusCode')->willReturnOnConsecutiveCalls(200, 201);

        $service = new PurchaseOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(3))->method('request')->willReturn($responseMock);

        $service->updateOrderLabels($this->getConfig(), '110122006447', 'LABEL', 'NOTE');
    }

    /**
     * @test
     */
    public function update_order_labels_exception_raised(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        /** @var FlexibeeOrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(FlexibeeOrderDtoFactory::class);

        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientMock, $priceListItemDtoFactory);

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->method('toArray')->willReturn([
            'winstrom' => [
                'results' => [
                    0 => [
                        'errors' => [['message' => 'Message 1'], ['message' => 'Message 2']],
                    ],
                ],
            ],
        ]);
        $responseMock->method('getStatusCode')->willReturn(400);

        $service = new PurchaseOrderApiService($httpClientMock, $orderDtoFactory, $priceListApiService);

        $httpClientMock->expects($this->exactly(1))->method('request')->willReturn($responseMock);

        $this->expectException(FlexibeeApiException::class);
        $this->expectExceptionMessage('Message 1; Message 2');
        $service->updateOrderLabels($this->getConfig(), '110122006447', 'LABEL');
    }
}
