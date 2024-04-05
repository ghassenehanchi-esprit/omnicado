<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Service;

use Elasticr\ServiceBus\MoneyS5\Service\StockDocumentTransferService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5StockDocumentTransferConfig;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StockDocumentTransferServiceTest extends KernelTestCase
{
    private StockDocumentTransferService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var StockDocumentTransferService $service */
        $service = self::getContainer()->get(StockDocumentTransferService::class);

        $this->service = $service;
    }

    /**
     * @test
     *
     * @dataProvider data
     **/
    public function get_receipts_from_transfers(string $expectedDataPath, string $documentTransfersPath, MoneyS5StockDocumentTransferConfig $config): void
    {
        $this->assertSame($this->loadContentFromFile($expectedDataPath), $this->service->getReceiptsFromTransfers($this->loadContentFromFile($documentTransfersPath), $config));
    }

    /**
     * @return array<int, mixed>
     */
    public function data(): array
    {
        return [
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig([], [], [], []),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig([], ['25'], ['VJ'], ['PJ']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], [], ['VJ'], ['PJ']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], ['25'], [], ['XX']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], ['25'], ['VJ'], []),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['XX'], ['25'], ['VJ'], ['PJ']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], ['XX'], ['VJ'], ['PJ']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], ['99'], ['XX'], ['PJ']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_0_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], ['99'], ['VJ'], ['XX']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_receipt_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_document_transfers_by_transfer_number.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], ['25'], ['VJ'], ['PJ']),
            ],
            [
                'expectedDataPath' => '/../../../expectations/moneys5/expected_receipts_from_transfers.json',
                'issuedDataPath' => '/../../../expectations/moneys5/stock_documents_transfers_by_transfer_numbers.json',
                'config' => new MoneyS5StockDocumentTransferConfig(['01'], ['12', '19', '33', '39', '41', '46', '48', '53'], ['VJ'], ['PJ']),
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
}
