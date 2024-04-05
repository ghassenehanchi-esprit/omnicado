<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Service;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Service\IssuedOrdersService;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IssuedOrdersServiceTest extends KernelTestCase
{
    private IssuedOrdersService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var IssuedOrdersService $issuedOrdersService */
        $issuedOrdersService = self::getContainer()->get(IssuedOrdersService::class);

        $this->service = $issuedOrdersService;
    }

    /**
     * @test
     *
     * @dataProvider data
     * @param array<string, float> $items
     * @param array<string, float> $processedItems
     **/
    public function cover_issue_order(array $items, string $expectedDataPath, string $issuedDataPath, array $processedItems): void
    {
        $document = $this->getDocumentDto($items);
        $this->assertSame($this->loadContentFromFile($expectedDataPath), $this->service->coverIssueOrder($this->loadContentFromFile($issuedDataPath), $document));

        foreach ($document->items() as $documentItem) {
            foreach ($processedItems as $sku => $quantity) {
                if ($sku === $documentItem->sku()) {
                    $this->assertSame((float) $quantity, $documentItem->quantity());
                }
            }
        }
    }

    /**
     * @return array<int, mixed>
     */
    public function data(): array
    {
        return [
            [
                'items' => [],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_order_with_0_document_items.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_order.json',
                'processedItems' => [],
            ],
            [
                'items' => [
                    'HLCRIO173' => 24,
                    'HLCRIO172' => 48,
                    'HLCRIO161' => 24,
                    'HLCRIO157' => 60,
                    'HLCRIO171AM' => 200,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_order_with_all_covered_items.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_order.json',
                'processedItems' => [
                    'HLCRIO173' => 0,
                    'HLCRIO172' => 0,
                    'HLCRIO161' => 0,
                    'HLCRIO157' => 0,
                    'HLCRIO171AM' => 0,
                ],
            ],
            [
                'items' => [
                    'HLCRIO173' => 1000,
                    'HLCRIO172' => 1000,
                    'HLCRIO161' => 1000,
                    'HLCRIO157' => 1000,
                    'HLCRIO171AM' => 1000,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_order_with_all_covered_items.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_order.json',
                'processedItems' => [
                    'HLCRIO173' => 976,
                    'HLCRIO172' => 952,
                    'HLCRIO161' => 976,
                    'HLCRIO157' => 940,
                    'HLCRIO171AM' => 800,
                ],
            ],
            [
                'items' => [
                    'HLCRIO173' => 24,
                    'HLCRIO172' => 48,
                    'HLCRIO161' => 0,
                    'HLCRIO157' => 0,
                    'HLCRIO171AM' => 0,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_order_with_partly_covered_items.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_order.json',
                'processedItems' => [
                    'HLCRIO173' => 0,
                    'HLCRIO172' => 0,
                    'HLCRIO161' => 0,
                    'HLCRIO157' => 0,
                    'HLCRIO171AM' => 0,
                ],
            ],
            [
                'items' => [
                    'HLCRIO173' => 24,
                    'HLCRIO172' => 48,
                    'HLCRIO161' => 20,
                    'HLCRIO157' => 30,
                    'HLCRIO171AM' => 50,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_order_with_partly_covered_items_2.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_order.json',
                'processedItems' => [
                    'HLCRIO173' => 0,
                    'HLCRIO172' => 0,
                    'HLCRIO161' => 0,
                    'HLCRIO157' => 0,
                    'HLCRIO171AM' => 0,
                ],
            ],
            [
                'items' => [
                    'HLCRIO173' => 100,
                    'HLCRIO172' => 100,
                    'HLCRIO161' => 100,
                    'HLCRIO157' => 100,
                    'HLCRIO171AM' => 200,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_order_with_all_covered_items.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_order_with_partly_covered_items.json',
                'processedItems' => [
                    'HLCRIO173' => 100,
                    'HLCRIO172' => 100,
                    'HLCRIO161' => 76,
                    'HLCRIO157' => 40,
                    'HLCRIO171AM' => 0,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadContentFromFile(string $path): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . $path);

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @param array<string, float> $items
     */
    private function getDocumentDto(array $items): DocumentDto
    {
        $documentItemsDto = [];

        foreach ($items as $sku => $quantity) {
            $documentItemsDto[] = $this->createDocumentItemDto($sku, (float) $quantity);
        }

        return new DocumentDto('1', 'NUMBER123456', null, null, null, null, null, $documentItemsDto, Chronos::createFromFormat(
            'Y-m-d H:i:s',
            '2022-12-12 00:00:00'
        ), null, 'supplierCode');
    }

    private function createDocumentItemDto(string $sku, float $quantity): DocumentItemDto
    {
        return new DocumentItemDto('1', $sku, 'name', $quantity, 'ks', null, null, 0);
    }
}
