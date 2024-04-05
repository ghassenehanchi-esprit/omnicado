<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Factory\ReceiptStockDocumentDataFromDocumentDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ReceiptStockDocumentDataFromDocumentDtoFactoryTest extends KernelTestCase
{
    private ReceiptStockDocumentDataFromDocumentDtoFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ReceiptStockDocumentDataFromDocumentDtoFactory $factory */
        $factory = self::getContainer()->get(ReceiptStockDocumentDataFromDocumentDtoFactory::class);
        $this->factory = $factory;
    }

    /**
     * @test
     **/
    public function create(): void
    {
        $this->assertSame($this->loadContentFromFile('/../../../expectations/moneys5/receipt_stock_document_data.json'), $this->factory->create($this->getDocumentDto()));
    }

    private function getDocumentDto(): DocumentDto
    {
        $documentItemsDto = [];

        $documentItemsDto[] = new DocumentItemDto('1', 'sku-1', 'Nazev produktu 1', 1, 'ks', null, null, 0, null, [], null, 'stock-item-1');
        $documentItemsDto[] = new DocumentItemDto('2', 'sku-2', 'Nazev produktu 2', 2, 'ks', null, null, 0, null, [], null, 'stock-item-2');

        return new DocumentDto('1', 'NUMBER123456', null, null, null, null, null, $documentItemsDto, Chronos::createFromFormat(
            'Y-m-d H:i:s',
            '2022-12-12 00:00:00'
        ), null, 'supplierCode', '', null, null, [], '', 'stockroom-id');
    }

    /**
     * @return array<string, mixed>>
     */
    private function loadContentFromFile(string $path): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . $path);

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
