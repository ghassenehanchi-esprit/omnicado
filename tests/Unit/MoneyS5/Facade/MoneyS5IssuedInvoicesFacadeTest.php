<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Elasticr\ServiceBus\MoneyS5\Api\IssuedInvoicesApiService;
use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\Entity\MoneyS5SyncStatus;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5IssuedInvoicesFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\MoneyS5DocumentDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\MoneyS5\Service\IssuedInvoicesService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5IssuedInvoicesFilters;
use Elasticr\ServiceBus\ServiceBus\Command\CreateReceiptCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\Service\DataTransferLogService;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class MoneyS5IssuedInvoicesFacadeTest extends KernelTestCase
{
    private MoneyS5IssuedInvoicesFilters $expectedFilter;

    private MoneyS5IssuedInvoicesFacade $facade;

    private MockObject $serviceBus;

    private MockObject $issuedInvoiceApiService;

    private IssuedInvoicesService $issuedInvoicesService;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config =
            TestingMoneyS5ConfigFactory::create(
                [
                    'issuedInvoiceTransferConfig' => [
                        'namesForCreditNote' => ['opravný', 'daňový', 'doklad'],
                        'warehouseCodesForCreditNote' => ['01'],
                    ],
                ]
            );

        $customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $customerRepositoryMock->expects($this->once())->method('findByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $customerProvider = new CustomerProvider($customerRepositoryMock);

        $customerConfigFinderMock->expects($this->exactly(1))->method('find')->willReturn($this->moneyS5Config);

        $moneyS5SyncStatus = new MoneyS5SyncStatus(0);
        $moneyS5SyncStatus->updateLastProcessedArticleTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatus->updateLastProcessedIssuedDeliveryNoteTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatus->updateLastProcessedStockDocumentTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatus->updateLastProcessedStockDocumentSampleTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatus->updateLastProcessedIssuedInvoiceTime(DateHelper::convertStringToChronos('2023-06-22T10:19:00.00'));
        $moneyS5SyncStatusRepositoryMock = $this->createMock(MoneyS5SyncStatusRepository::class);
        $moneyS5SyncStatusRepositoryMock->expects($this->exactly(1))->method('findByCustomerId')->willReturn($moneyS5SyncStatus);

        $this->expectedFilter = new MoneyS5IssuedInvoicesFilters(
            $this->moneyS5Config->issuedInvoiceTransferConfig()->namesForCreditNote(),
            DateHelper::convertStringToChronos('2023-06-22T10:19:00.00'),
        );

        /** @var MoneyS5DocumentDtoFactory $documentDtoFactory */
        $documentDtoFactory = self::getContainer()->get(MoneyS5DocumentDtoFactory::class);
        $this->issuedInvoiceApiService = $this->createMock(IssuedInvoicesApiService::class);
        $this->issuedInvoicesService = self::getContainer()->get(IssuedInvoicesService::class);

        $this->serviceBus = $this->createMock(ServiceBus::class);

        $this->facade = new MoneyS5IssuedInvoicesFacade(
            $customerProvider,
            $moneyS5SyncStatusRepositoryMock,
            $customerConfigFinderMock,
            $this->issuedInvoiceApiService,
            $this->issuedInvoicesService,
            $this->serviceBus,
            $documentDtoFactory,
            $this->createMock(DataTransferLogService::class)
        );
    }

    /**
     * @test
     **/
    public function transfer_issued_invoices(): void
    {
        $this->issuedInvoiceApiService
            ->expects($this->exactly(1))
            ->method('getIssuedInvoices')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->expectedFilter)
            )
            ->willReturn(
                $this->expectedArray()
            );

        $counter = $this->exactly(2);
        $this->serviceBus
            ->expects($counter)
            ->method('dispatch')
            ->with(
                $this->callback(
                    fn ($command): bool =>
                        $command instanceof CreateReceiptCommand
                        &&

                    match ($counter->getInvocationCount()) {
                        1 => $command->document()->number() === '239204',
                        2 => $command->document()->number() === '239205',
                        default => false
                    }
                )
            );

        $this->facade->transferIssuedInvoices('123456');

        $this->assertEquals(
            $this->expectedFilteredStockArray(),
            $this->issuedInvoicesService->getIssuedInvoicesFromTransfers(
                $this->expectedArray(),
                $this->moneyS5Config->issuedInvoiceTransferConfig()->warehouseCodesForCreditNote()
            )
        );
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedFilteredStockArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/issued_invoices_filtered_stock_transfers.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
