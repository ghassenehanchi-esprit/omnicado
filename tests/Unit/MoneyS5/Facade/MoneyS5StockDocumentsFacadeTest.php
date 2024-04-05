<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Elasticr\ServiceBus\MoneyS5\Api\StockDocumentsApiService;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5StockDocumentsFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\OrderDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\MoneyS5\Service\StockDocumentTransferService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5StockDocumentTransferConfig;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\Service\DataTransferLogService;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MoneyS5StockDocumentsFacadeTest extends KernelTestCase
{
    private MoneyS5StockDocumentsFacade $facade;

    private MockObject $serviceBus;

    private MockObject $stockDocumentsApiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockDocumentsApiService = $this->createMock(StockDocumentsApiService::class);
        $customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $moneyS5SyncStatusRepositoryMock = $this->createMock(MoneyS5SyncStatusRepository::class);
        $this->serviceBus = $this->createMock(ServiceBus::class);
        $customerRepositoryMock->expects($this->once())->method('findByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));

        $customerProvider = new CustomerProvider($customerRepositoryMock);

        $mockedConfig = $this->createMock(MoneyS5Config::class);
        $mockedConfig->method('stockDocumentTransferConfig')->willReturn(
            new MoneyS5StockDocumentTransferConfig(['01'], ['12', '19', '33', '39', '41', '46', '48', '53'], ['VJ'], ['PJ'])
        );
        $customerConfigFinderMock->method('find')->willReturn($mockedConfig);

        /** @var StockDocumentTransferService $stockDocumentTransferService */
        $stockDocumentTransferService = self::getContainer()->get(StockDocumentTransferService::class);

        /** @var OrderDtoFactory $orderDtoFactory */
        $orderDtoFactory = self::getContainer()->get(OrderDtoFactory::class);

        $this->facade = new MoneyS5StockDocumentsFacade(
            $customerProvider,
            $moneyS5SyncStatusRepositoryMock,
            $customerConfigFinderMock,
            $this->stockDocumentsApiService,
            $this->serviceBus,
            $orderDtoFactory,
            $stockDocumentTransferService,
            $this->createMock(DataTransferLogService::class)
        );
    }

    /**
     * @test
     **/
    public function transfer_stock_documents(): void
    {
        $this->stockDocumentsApiService->expects($this->exactly(1))->method('getStockDocuments')->willReturn($this->expectedArray());

        $this->serviceBus->expects($this->exactly(4))->method('dispatch');
        $this->facade->transferStockDocuments('123456');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/stock_documents_transfers.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
