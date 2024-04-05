<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Service;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Service\IssuedDeliveryNoteService;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Exception;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IssuedDeliveryNoteServiceTest extends KernelTestCase
{
    private IssuedDeliveryNoteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var IssuedDeliveryNoteService $issuedOrdersService */
        $issuedOrdersService = self::getContainer()->get(IssuedDeliveryNoteService::class);

        $this->service = $issuedOrdersService;
    }

    /**
     * @test
     *
     * @dataProvider data
     * @param array<int,array{
     *      sku: string,
     *      quantity: float,
     *     sscc: string,
     * }> $items
     **/
    public function cover_delivery_note(array $items, string $expectedDataPath, string $issuedDataPath, bool $itThrowsException, ?Chronos $sentAt = null): void
    {
        if ($itThrowsException) {
            $this->expectException(Exception::class);

            $this->service->coverDeliveryNote($this->loadContentFromFile($issuedDataPath), $this->getDocumentItemsDto($items), 'ORIGIN-NUMBER');

            return;
        }

        $loadContentFromFile = $this->loadContentFromFile($expectedDataPath);
        unset($loadContentFromFile['Polozky']);

        $this->assertSame(
            $loadContentFromFile,
            $this->service->coverDeliveryNote($this->loadContentFromFile($issuedDataPath), $this->getDocumentItemsDto($items), 'DV239764', $sentAt)
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function data(): array
    {
        return [
            [
                'items' => [],
                'expectedDataPath' => '',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_0_document_items.json',
                true,
            ],
            [
                'items' => [
                    ['sku' => 'PL2A-11-18',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AGL05556',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                ],
                '',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_0_document_items.json',
                true,
            ],
            [
                'items' => [
                    ['sku' => 'PL2A-11-18',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AGL05556',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01060',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01061',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS00427',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 2,
                        'sscc' => 'SSCC-1'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_all_covered_items_for_one_sscc.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note.json',
                false,
            ],
            [
                'items' => [
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-2'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_two_covered_items_for_multiple_sscc.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_one_item.json',
                false,
            ],
            [
                'items' => [
                    ['sku' => 'PL2A-11-18',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AGL05556',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01060',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01061',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS00427',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-2'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_all_covered_items_for_multiple_sscc.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note.json',
                false,
            ],
            [
                'items' => [
                    ['sku' => 'AFL01220',
                        'quantity' => 2,
                        'sscc' => 'SSCC-1'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_two_covered_items_for_one_sscc.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_seperated_items.json',
                false,
            ],
            [
                'items' => [
                    ['sku' => 'PL2A-11-18',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AGL05556',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01060',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01061',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS00427',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 2,
                        'sscc' => 'SSCC-1'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_all_covered_items_for_one_sscc.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_partly_covered_items.json',
                false,
            ],
            [
                'items' => [
                    ['sku' => 'PL2A-11-18',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AGL05556',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01060',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS01061',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'ACS00427',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-2'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_all_covered_items_for_multiple_sscc.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_partly_covered_items_with_multiple_sscc.json',
                false,
            ],
            [
                'items' => [
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-2'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_two_covered_items_for_multiple_sscc_with_date.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_one_item.json',
                false,
                Chronos::createFromFormat('Y-m-d H:i:s', '2023-08-03 08:15:30'),
            ],
            [
                'items' => [
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01220',
                        'quantity' => 1,
                        'sscc' => 'SSCC-2'],
                    ['sku' => 'AFL01XXX',
                        'quantity' => 1,
                        'sscc' => 'SSCC-2'],
                    ['sku' => 'AFL01XXX',
                        'quantity' => 2,
                        'sscc' => ''],
                    ['sku' => 'AFL01AAA',
                        'quantity' => 10,
                        'sscc' => 'SSCC-3'],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/expected_issued_delivery_note_with_two_covered_items_for_multiple_sscc_with_date_2.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_two_covered_items_for_multiple_sscc_with_date_2.json',
                false,
                Chronos::createFromFormat('Y-m-d H:i:s', '2023-08-03 08:15:30'),
            ],
            [
                'items' => [
                    ['sku' => 'AFL01220',
                        'quantity' => 3,
                        'sscc' => ''],
                    ['sku' => 'AFL01XXX',
                        'quantity' => 1,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01XXX',
                        'quantity' => 2,
                        'sscc' => 'SSCC-1'],
                    ['sku' => 'AFL01AAA',
                        'quantity' => 10,
                        'sscc' => ''],
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/expected_issued_delivery_note_with_two_covered_items_for_multiple_sscc_with_date_3.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_delivery_note_with_two_covered_items_for_multiple_sscc_with_date_3.json',
                false,
                Chronos::createFromFormat('Y-m-d H:i:s', '2023-08-03 08:15:30'),
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
     * @param array<int, array{
     *      sku: string,
     *      quantity: float,
     *     sscc: string,
     * }> $items
     * @return DocumentItemDto[]
     */
    private function getDocumentItemsDto(array $items): array
    {
        $documentItemsDto = [];

        foreach ($items as $key => $item) {
            $documentItemsDto[] = $this->createDocumentItemDto((string) $key, $item['sku'], (float) $item['quantity'], $item['sscc']);
        }

        return $documentItemsDto;
    }

    private function createDocumentItemDto(string $id, string $sku, float $quantity, string $sscc): DocumentItemDto
    {
        return new DocumentItemDto($id, $sku, 'name', $quantity, 'ks', null, null, 0, null, [$sscc]);
    }
}
