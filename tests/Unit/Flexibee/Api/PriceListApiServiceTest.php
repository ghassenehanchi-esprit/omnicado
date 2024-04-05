<?php

declare(strict_types=1);

namespace Unit\Flexibee\Api;

use Elasticr\ServiceBus\Flexibee\Api\PriceListApiService;
use Elasticr\ServiceBus\Flexibee\Factory\PriceListItemDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\Elasticr\ServiceBus\Unit\Flexibee\Api\FlexibeeConfigTrait;

final class PriceListApiServiceTest extends KernelTestCase
{
    use FlexibeeConfigTrait;

    /**
     * @test
     */
    public function get_items_single_page(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_single_page.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responseMock->method('getStatusCode')->willReturn(200);
        $httpClientMock->expects($this->exactly(1))->method('request')->willReturn($responseMock);

        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $service = new PriceListApiService($httpClientMock, $priceListItemDtoFactory);

        $items = $service->getItems($this->getConfig());

        $this->assertEquals(10, count($items));
    }

    /**
     * @test
     */
    public function get_items_multiple_pages(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_page_1.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_page_2.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_page_3.json') ?: '', true, 512, JSON_THROW_ON_ERROR)
        );
        $responseMock->method('getStatusCode')->willReturn(200);
        $httpClientMock->expects($this->exactly(3))->method('request')->willReturn($responseMock);

        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $service = new PriceListApiService($httpClientMock, $priceListItemDtoFactory);

        $items = $service->getItems($this->getConfig());

        $this->assertEquals(250, count($items));
    }

    /**
     * @test
     */
    public function get_item(): void
    {
        $httpClientPriceListMock = $this->createMock(HttpClientInterface::class);
        $responsePriceListMock = $this->createMock(ResponseInterface::class);
        $responsePriceListMock->method('toArray')->willReturnOnConsecutiveCalls(
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/price_list_item_109.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responsePriceListMock->method('getStatusCode')->willReturn(200);
        $httpClientPriceListMock->expects($this->exactly(1))->method('request')->willReturn($responsePriceListMock);
        /** @var PriceListItemDtoFactory $priceListItemDtoFactory */
        $priceListItemDtoFactory = self::getContainer()->get(PriceListItemDtoFactory::class);
        $priceListApiService = new PriceListApiService($httpClientPriceListMock, $priceListItemDtoFactory);

        /** @var ProductDto $item */
        $item = $priceListApiService->getItem($this->getConfig(), 109);

        $this->assertNotNull($item);
        $this->assertEquals(109, $item->id());
        $this->assertEquals('42120053', $item->sku());
        $this->assertEquals('Krémová polévka s kuřecí příchutí', $item->name());
        $this->assertEquals(59.0, $item->price());
        $this->assertEquals(27.0, $item->weight());
        $this->assertEquals('KS', $item->unit());
    }
}
