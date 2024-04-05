<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Elasticr\ServiceBus\MoneyS5\Api\StockDocumentsApiService;
use Elasticr\ServiceBus\MoneyS5\Constant\GraphQLOperators;
use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\Entity\MoneyS5SyncStatus;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5StockDocumentsSampleFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\OrderDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\MoneyS5\Service\StockDocumentsSampleService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5StockDocumentFilters;
use Elasticr\ServiceBus\ServiceBus\Command\CreateOrderCommand;
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

final class MoneyS5StockDocumentsSampleFacadeTest extends KernelTestCase
{
    private MoneyS5StockDocumentFilters $expectedFilter;

    private MoneyS5StockDocumentsSampleFacade $facade;

    private MockObject $serviceBus;

    private MockObject $stockDocumentsApiService;

    private MockObject $stockDocumentsSampleService;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config = TestingMoneyS5ConfigFactory::create([]);

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
        $moneyS5SyncStatusRepositoryMock = $this->createMock(MoneyS5SyncStatusRepository::class);
        $moneyS5SyncStatusRepositoryMock->expects($this->exactly(1))->method('findByCustomerId')->willReturn($moneyS5SyncStatus);

        $this->expectedFilter = new MoneyS5StockDocumentFilters(
            null,
            $this->moneyS5Config->stockDocumentTransferConfig()->nameForSample(),
            '2',
            DateHelper::convertStringToChronos('2023-09-01T00:00:01'),
            GraphQLOperators::START_WITH,
            GraphQLOperators::CONTAINS,
            GraphQLOperators::EQUAL,
        );

        /** @var OrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(OrderDtoFactory::class);

        $this->stockDocumentsApiService = $this->createMock(StockDocumentsApiService::class);
        $this->stockDocumentsSampleService = $this->createMock(StockDocumentsSampleService::class);
        $this->serviceBus = $this->createMock(ServiceBus::class);

        $this->facade = new MoneyS5StockDocumentsSampleFacade(
            $customerProvider,
            $moneyS5SyncStatusRepositoryMock,
            $customerConfigFinderMock,
            $this->stockDocumentsApiService,
            $this->stockDocumentsSampleService,
            $this->serviceBus,
            $orderDtoFactory,
            $this->createMock(DataTransferLogService::class)
        );
    }

    /**
     * @test
     **/
    public function transfer_stock_documents(): void
    {
        $this->stockDocumentsApiService
            ->expects($this->exactly(1))
            ->method('getStockDocuments')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->expectedFilter)
            )
            ->willReturn(
                $this->expectedArray()
            );

        $this->stockDocumentsSampleService->expects($this->exactly(1))->method('sortStockDocuments')->willReturn($this->expectedArraySorted());

        $counter = $this->exactly(2);
        $this->serviceBus
            ->expects($counter)
            ->method('dispatch')

            ->with(
                $this->callback(
                    fn ($command): bool =>
                $command instanceof CreateOrderCommand

                && match ($counter->getInvocationCount()) {
                    1 => $command->order()->number() === 'VJ231963',
                    2 => $command->order()->number() === 'VJ231964',
                    default => false
                }
                )
            );

        $this->facade->transferStockDocumentsSample('123456');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/stock_documents_transfers_samples_unsorted.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedArraySorted(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/stock_documents_transfers_samples_sorted.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
