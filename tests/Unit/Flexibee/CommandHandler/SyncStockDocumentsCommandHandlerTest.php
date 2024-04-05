<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Flexibee\CommandHandler;

use Cake\Chronos\Chronos;
use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\Flexibee\Api\StockMovementsApiService;
use Elasticr\ServiceBus\Flexibee\CommandBus\CommandHandler\SyncStockDocumentsCommandHandler;
use Elasticr\ServiceBus\Flexibee\Mapping\StockDocumentMapping;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeeConfig;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeeStockConfig;
use Elasticr\ServiceBus\ServiceBus\Command\SyncStockDocumentCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Model\StockDocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\StockItemDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SyncStockDocumentsCommandHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_handles_sync_income_document_command(): void
    {
        $stockMovementsApi = $this->createMock(StockMovementsApiService::class);
        /** @var StockDocumentMapping $stockDocumentMapping */
        $stockDocumentMapping = $this->getContainer()->get(StockDocumentMapping::class);

        /** @var ElasticrLogger $logger */
        $logger = $this->getContainer()->get(ElasticrLogger::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->once())->method('find')
            ->willReturn(new FlexibeeConfig('', '', [], null, null, [
                '05' => new FlexibeeStockConfig(1, 0, 0, 'SKLAD'),
            ], [], []));

        $stockMovementsApi->expects($this->once())->method('processDocument');

        $commandHandler = new SyncStockDocumentsCommandHandler($stockMovementsApi, $customerConfigFinder, $stockDocumentMapping, $logger);

        $syncStockDocumentCommand = new SyncStockDocumentCommand(new StockDocumentDto('1', 'VZSPF05NAT', '05', 'ORDER111', [
            new StockItemDto('AAA', '05', 10),
        ], Chronos::now(), null), 1);

        $commandHandler->__invoke($syncStockDocumentCommand);
    }

    /**
     * @test
     */
    public function it_handles_sync_outcome_document_command(): void
    {
        $stockMovementsApi = $this->createMock(StockMovementsApiService::class);
        /** @var StockDocumentMapping $stockDocumentMapping */
        $stockDocumentMapping = $this->getContainer()->get(StockDocumentMapping::class);

        /** @var ElasticrLogger $logger */
        $logger = $this->getContainer()->get(ElasticrLogger::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->once())->method('find')
            ->willReturn(new FlexibeeConfig('', '', [], null, null, [
                '05' => new FlexibeeStockConfig(0, 1, 0, 'SKLAD'),
            ], [], []));

        $stockMovementsApi->expects($this->once())->method('processDocument');

        $commandHandler = new SyncStockDocumentsCommandHandler($stockMovementsApi, $customerConfigFinder, $stockDocumentMapping, $logger);

        $syncStockDocumentCommand = new SyncStockDocumentCommand(new StockDocumentDto('1', 'VZSVF97LZ', '05', 'ORDER111', [
            new StockItemDto('AAA', '05', 10),
        ], Chronos::now(), null), 1);

        $commandHandler->__invoke($syncStockDocumentCommand);
    }

    /**
     * @test
     */
    public function it_handles_sync_transfer_document_command(): void
    {
        $stockMovementsApi = $this->createMock(StockMovementsApiService::class);
        /** @var StockDocumentMapping $stockDocumentMapping */
        $stockDocumentMapping = $this->getContainer()->get(StockDocumentMapping::class);

        /** @var ElasticrLogger $logger */
        $logger = $this->getContainer()->get(ElasticrLogger::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinder->expects($this->once())->method('find')
            ->willReturn(new FlexibeeConfig('', '', [], null, null, [
                '05' => new FlexibeeStockConfig(0, 0, 1, 'SKLAD'),
            ], [], []));

        $stockMovementsApi->expects($this->once())->method('processDocument');

        $commandHandler = new SyncStockDocumentsCommandHandler($stockMovementsApi, $customerConfigFinder, $stockDocumentMapping, $logger);

        $syncStockDocumentCommand = new SyncStockDocumentCommand(
            new StockDocumentDto(
                '2',
                'VZSVF05PV97',
                '05',
                'ORDER111',
                [new StockItemDto('AAA', '05', 10)],
                Chronos::now(),
                new StockDocumentDto('1', 'VZSPF97PV05', '05', 'ORDER111', [new StockItemDto('AAA', '05', 10)], Chronos::now(), null),
            ),
            1
        );

        $commandHandler->__invoke($syncStockDocumentCommand);
    }
}
