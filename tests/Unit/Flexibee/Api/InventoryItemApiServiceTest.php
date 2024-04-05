<?php

declare(strict_types=1);

namespace Unit\Flexibee\Api;

use Elasticr\ServiceBus\Flexibee\Api\InventoryItemApiService;
use Elasticr\ServiceBus\Flexibee\Factory\InventoryReportItemDtoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\Elasticr\ServiceBus\Unit\Flexibee\Api\FlexibeeConfigTrait;

final class InventoryItemApiServiceTest extends KernelTestCase
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
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/inventory_items_single_page.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responseMock->method('getStatusCode')->willReturn(200);
        $httpClientMock->expects($this->exactly(1))->method('request')->willReturn($responseMock);

        /** @var InventoryReportItemDtoFactory $inventoryItemDtoFactory */
        $inventoryItemDtoFactory = self::getContainer()->get(InventoryReportItemDtoFactory::class);
        $service = new InventoryItemApiService($httpClientMock, $inventoryItemDtoFactory);

        $items = $service->getInventoryItemsForReport($this->getConfig());

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
            json_decode(file_get_contents(__DIR__ . '/../../../expectations/flexibee/inventory_items_page_1.json') ?: '', true, 512, JSON_THROW_ON_ERROR),
        );
        $responseMock->method('getStatusCode')->willReturn(200);
        $httpClientMock->expects($this->exactly(1))->method('request')->willReturn($responseMock);

        /** @var InventoryReportItemDtoFactory $inventoryItemDtoFactory */
        $inventoryItemDtoFactory = self::getContainer()->get(InventoryReportItemDtoFactory::class);
        $service = new InventoryItemApiService($httpClientMock, $inventoryItemDtoFactory);

        $items = $service->getInventoryItemsForReport($this->getConfig());

        $this->assertEquals(100, count($items));
    }
}
