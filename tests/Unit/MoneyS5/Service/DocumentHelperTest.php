<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Service;

use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\DocumentHelper;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DocumentHelperTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     **/
    public function complete_modify_date(): void
    {
        $dateModifiedArray = DocumentHelper::completeModifyDate($this->expectedArray());

        $expectedDateModifiedArray = [
            '2023-06-22T10:37:27.723',
            '2023-06-22T10:18:38.82',
            '2023-06-22T10:24:51.69',
            '2023-06-22T10:42:00.00',
        ];

        foreach ($dateModifiedArray as $index => $document) {
            $this->assertSame($expectedDateModifiedArray[$index], $document['Modify_Date']);
        }
    }

    /**
     * @test
     **/
    public function sort_by_modify_date(): void
    {
        $dateSortedArray = DocumentHelper::sortByModifyDate(DocumentHelper::completeModifyDate($this->expectedArray()));

        $expectedDateSortedArray = [
            '239202',
            '239203',
            '239204',
            '239205',
        ];

        foreach ($dateSortedArray as $index => $document) {
            $this->assertSame($expectedDateSortedArray[$index], $document['CisloDokladu']);
        }
    }

    /**
     * @test
     **/
    public function actual_documents(): void
    {
        $dateActualArray = DocumentHelper::actualDocuments(
            DocumentHelper::sortByModifyDate(DocumentHelper::completeModifyDate($this->expectedArray())),
            DateHelper::convertStringToChronos('2023-06-22T10:19:00.00')
        );

        $expecteddateActualArray = [
            '239203',
            '239204',
            '239205',
        ];

        foreach ($dateActualArray as $index => $document) {
            $this->assertSame($expecteddateActualArray[$index], $document['CisloDokladu']);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/issued_invoices_transfers.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
