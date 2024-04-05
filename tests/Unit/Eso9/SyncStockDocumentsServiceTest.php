<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\Eso9Client;
use Elasticr\ServiceBus\Eso9\Client\Eso9ClientFactory;
use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureResponseData;
use Elasticr\ServiceBus\Eso9\Factory\StockDocumentDtoFactory;
use Elasticr\ServiceBus\Eso9\Repository\Eso9SyncStatusRepository;
use Elasticr\ServiceBus\Eso9\Service\SyncStockDocumentsService;
use Elasticr\ServiceBus\Eso9\ValueObject\Eso9Config;
use Elasticr\ServiceBus\ServiceBus\Command\SyncStockDocumentCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Model\StockDocumentDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SyncStockDocumentsServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_dispatch_sync_stock_documents_command(): void
    {
        $clientFactoryMock = $this->createMock(Eso9ClientFactory::class);
        $procedureDataFactoryMock = $this->createMock(Eso9ProcedureDataFactory::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);
        $clientMock = $this->createMock(Eso9Client::class);
        $syncStatusRepositoryMock = $this->createMock(Eso9SyncStatusRepository::class);

        /** @var StockDocumentDtoFactory $stockDocumentFactory */
        $stockDocumentFactory = self::getContainer()->get(StockDocumentDtoFactory::class);

        $customerConfigFinder = $this->createMock(CustomerConfigFinder::class);
        $customerRepository = $this->createMock(CustomerRepository::class);

        $service = new SyncStockDocumentsService(
            $clientFactoryMock,
            $procedureDataFactoryMock,
            $serviceBusMock,
            $syncStatusRepositoryMock,
            $stockDocumentFactory,
            $customerConfigFinder,
            $customerRepository
        );

        $customerRepository->expects($this->once())->method('findByCode')->willReturn(new Customer('Testing customer', '123456'));
        $customerConfigFinder->expects($this->once())->method('find')->willReturn(new Eso9Config('', 'testingUser', ['VzSpf97PV05', 'VzSpf98PV05']));
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);

        /** @var string $expectedEsoData */
        $expectedEsoData = file_get_contents(__DIR__ . '/../../expectations/eso9_han_move_data.json');
        $clientMock->expects($this->atLeastOnce())->method('callProcedure')
            ->willReturn(new Eso9ProcedureResponseData($expectedEsoData));

        $syncStatusRepositoryMock->expects($this->exactly(3))->method('add');

        $serviceBusMock->expects($this->exactly(3))->method('dispatch')->willReturnOnConsecutiveCalls(
            $this->returnCallback(function (SyncStockDocumentCommand $command, $commandBusId): void {
                $this->assertNotNull($command->documentDto());
                $this->assertEquals('VZSPF05NAT', $command->documentDto()->type());
            }),
            $this->returnCallback(function (SyncStockDocumentCommand $command, $commandBusId): void {
                $this->assertNotNull($command->documentDto());
                $this->assertNotNull($command->documentDto()->relatedDoc());
                $this->assertEquals('VZSVF05PV97', $command->documentDto()->type());
                $this->assertNotNull($command->documentDto()->relatedDoc());
                /** @var StockDocumentDto $relatedDoc */
                $relatedDoc = $command->documentDto()->relatedDoc();
                $this->assertEquals('VZSPF97PV05', $relatedDoc->type());
            }),
            $this->returnCallback(function (SyncStockDocumentCommand $command, $commandBusId): void {
                $this->assertNotNull($command->documentDto());
                $this->assertEquals('VZSVF97PKS_STORNO', $command->documentDto()->type());
                $incomeDoc = $command->documentDto();
                $this->assertEquals(null, $incomeDoc->order());
                $this->assertEquals(2, count($incomeDoc->items()));
                $this->assertEquals(1, $incomeDoc->items()[0]->quantity());
                $this->assertEquals(1, $incomeDoc->items()[1]->quantity());
                $this->assertEquals('97', $incomeDoc->stock());
            })
        );

        $service->execute('123456');
    }
}
