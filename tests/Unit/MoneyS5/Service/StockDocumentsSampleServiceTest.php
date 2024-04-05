<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Service;

use Elasticr\ServiceBus\MoneyS5\Service\StockDocumentsSampleService;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StockDocumentsSampleServiceTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     **/
    public function sort_samples(): void
    {
        $actual = new StockDocumentsSampleService();

        $this->assertEquals($this->expectedArray(), $actual->sortStockDocuments($this->actualArray()));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/stock_documents_transfers_samples_sorted.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function actualArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/stock_documents_transfers_samples_unsorted.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
