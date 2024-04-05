<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Factory\ReceivedDeliveryNoteDataFromIssuedOrderDataFactory;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

final class ReceivedDeliveryNoteDataFromIssuedOrderDataFactoryTest extends KernelTestCase
{
    private ReceivedDeliveryNoteDataFromIssuedOrderDataFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ReceivedDeliveryNoteDataFromIssuedOrderDataFactory $factory */
        $factory = self::getContainer()->get(ReceivedDeliveryNoteDataFromIssuedOrderDataFactory::class);

        $this->factory = $factory;
    }

    /**
     * @test
     *
     * @dataProvider data
     * @param array<string, float> $items
     **/
    public function create(array $items, string $expectedDataPath, string $issuedDataPath, bool $itThrowsException = false, ?string $expectedExceptionMessage = null): void
    {
        $document = $this->getDocumentDto($items);

        /** @var array<int, array<string, mixed>> $issuedOrdersData */
        $issuedOrdersData = $this->loadContentFromFile($issuedDataPath);

        if ($itThrowsException) {
            $this->expectException(UnexpectedValueException::class);

            if ($expectedExceptionMessage) {
                $this->expectExceptionMessage($expectedExceptionMessage);
            }

            $this->factory->create($document, $issuedOrdersData);

            return;
        }

        $this->assertSame($this->loadContentFromFile($expectedDataPath), $this->factory->create($document, $issuedOrdersData));
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
                'issuedDataPath' => '/../../../expectations/moneys5/issued_orders_for_received_delivery_note.json',
                'itThrowsException' => true,
                'expectedExceptionMessage' => 'Received delivery note has no items from document: NUMBER123456',
            ],
            [
                'items' => [
                    'HLCRIO173' => 9999,
                    'HLCRIO172' => 0,
                    'HLCRIO161' => 0,
                    'HLCRIO157' => 0,
                    'HLCRIO171AM' => 0,
                ],
                'expectedDataPath' => '',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_orders.json',
                'itThrowsException' => true,
                'expectedExceptionMessage' => 'Item: HLCRIO173 has not been covered',
            ],
            [
                'items' => [
                    'HLCRIO173' => 48,
                    'HLCRIO172' => 96,
                    'HLCRIO161' => 48,
                    'HLCRIO157' => 120,
                    'HLCRIO171AM' => 400,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/received_delivery_note_from_two_issued_orders.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_orders_for_received_delivery_note.json',
            ],
            [
                'items' => [
                    'HLCRIO173' => 24,
                    'HLCRIO172' => 48,
                    'HLCRIO161' => 24,
                    'HLCRIO157' => 60,
                    'HLCRIO171AM' => 200,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/received_delivery_note_from_two_issued_orders_2.json',
                'issuedDataPath' => '/../../../expectations/moneys5/partly_covered_issued_orders_for_received_delivery_note.json',
            ],
            [
                'items' => [
                    'HLCRIO173' => 24,
                    'HLCRIO172' => 48,
                    'HLCRIO161' => 24,
                    'HLCRIO157' => 60,
                    'HLCRIO171AM' => 200,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/received_delivery_note_from_two_issued_orders_2.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_orders_for_received_delivery_note_2.json',
            ],
            [
                'items' => [
                    'HLCRIO173' => 1,
                    'HLCRIO172' => 0,
                    'HLCRIO161' => 0,
                    'HLCRIO157' => 0,
                    'HLCRIO171AM' => 0,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/received_delivery_note_with_one_item.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_orders_for_received_delivery_note.json',
            ],
            [
                'items' => [
                    'HLCRIO173' => 24,
                    'HLCRIO172' => 48,
                    'HLCRIO161' => 24,
                    'HLCRIO157' => 60,
                    'HLCRIO171AM' => 200,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/received_delivery_note_from_two_issued_orders_2.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_orders_for_received_delivery_note_2.json',
            ],
            [
                'items' => [
                    'HLCRIO173' => 48,
                    'HLCRIO172' => 96,
                    'HLCRIO161' => 48,
                    'HLCRIO157' => 120,
                    'HLCRIO171AM' => 400,
                ],
                'expectedDataPath' => '/../../../expectations/moneys5/received_delivery_note_from_two_issued_orders_3.json',
                'issuedDataPath' => '/../../../expectations/moneys5/issued_orders_for_received_delivery_note_3.json',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|array<int, array<string, mixed>>
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
        ), null, 'supplierCode', 'Dodací list přijatý');
    }

    private function createDocumentItemDto(string $sku, float $quantity): DocumentItemDto
    {
        return new DocumentItemDto('1', $sku, 'name', $quantity, 'ks', null, null, 0);
    }
}
