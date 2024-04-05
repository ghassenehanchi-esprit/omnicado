<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Cake\Chronos\Chronos;
use DeepCopy\DeepCopy;
use Elasticr\ServiceBus\MoneyS5\Api\CompaniesApiService;
use Elasticr\ServiceBus\MoneyS5\Api\IssuedOrdersApiService;
use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\Entity\MoneyS5SyncStatus;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5IssuedOrdersFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\DocumentDtoFactoryFromIssuedOrderDocumentDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\MoneyS5DocumentDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\MoneyS5\Service\IssuedOrdersService;
use Elasticr\ServiceBus\MoneyS5\Service\ReceivedDeliveryNotesService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\SupplierDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\Service\DataTransferLogService;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use Elasticr\Support\Collection\ImmutableCollection;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class MoneyS5IssuedOrdersFacadeTest extends KernelTestCase
{
    private MoneyS5IssuedOrdersFacade $facade;

    private MockObject $issuedOrdersApiService;

    private MockObject $issuedOrdersServiceMock;

    private MockObject $receivedDeliveryNotesService;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config =
            TestingMoneyS5ConfigFactory::create([]);

        $customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $customerRepositoryMock->expects($this->once())->method('findByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));
        $customerProvider = new CustomerProvider($customerRepositoryMock);

        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinderMock->method('find')->willReturn($this->moneyS5Config);

        $moneyS5SyncStatus = new MoneyS5SyncStatus(0);
        $moneyS5SyncStatus->updateLastProcessedArticleTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatus->updateLastProcessedIssuedDeliveryNoteTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatus->updateLastProcessedStockDocumentTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatus->updateLastProcessedStockDocumentSampleTime(DateHelper::convertStringToChronos('2023-09-01T00:00:01'));
        $moneyS5SyncStatusRepositoryMock = $this->createMock(MoneyS5SyncStatusRepository::class);
        $moneyS5SyncStatusRepositoryMock->expects($this->exactly(0))->method('findByCustomerId')->willReturn($moneyS5SyncStatus);

        $companiesApiServiceMock = $this->createMock(CompaniesApiService::class);
        $companiesApiServiceMock->method('getCompanies')->with('supplierCode', $this->moneyS5Config)->willReturn(new ImmutableCollection([new SupplierDto('id', 'supplier')]));

        $this->issuedOrdersApiService = $this->createMock(IssuedOrdersApiService::class);
        $this->issuedOrdersApiService->expects($this->exactly(1))->method('getIssuedOrder')->willReturn($this->expectedIssuedOrders());

        $this->issuedOrdersServiceMock = $this->createMock(IssuedOrdersService::class);
        $this->issuedOrdersServiceMock->expects($this->exactly(1))->method('checkIssuedOrder')->with($this->moneyS5Config, $this->expectedIssuedOrders());

        $this->receivedDeliveryNotesService = $this->createMock(ReceivedDeliveryNotesService::class);

        /** @var DocumentDtoFactoryFromIssuedOrderDocumentDtoFactory $documentFactory */
        $documentFactory = self::getContainer()->get(DocumentDtoFactoryFromIssuedOrderDocumentDtoFactory::class);

        /** @var MoneyS5DocumentDtoFactory $documentDtoFactory */
        $documentDtoFactory = self::getContainer()->get(MoneyS5DocumentDtoFactory::class);

        /** @var DeepCopy $deepCopy */
        $deepCopy = self::getContainer()->get(DeepCopy::class);

        $serviceBusMock = $this->createMock(ServiceBus::class);

        $this->facade = new MoneyS5IssuedOrdersFacade(
            $this->issuedOrdersApiService,
            $customerProvider,
            $customerConfigFinderMock,
            $moneyS5SyncStatusRepositoryMock,
            $serviceBusMock,
            $companiesApiServiceMock,
            $documentDtoFactory,
            $this->issuedOrdersServiceMock,
            $this->receivedDeliveryNotesService,
            $documentFactory,
            $deepCopy,
            $this->createMock(DataTransferLogService::class),
        );
    }

    /**
     * @test
     **/
    public function update_issued_order(): void
    {
        $this->receivedDeliveryNotesService
            ->expects($this->exactly(1))
            ->method('createReceivedDeliveryNote')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->getDocumentDto()),
                $this->equalTo($this->expectedIssuedOrders()),
            );

        $this->issuedOrdersServiceMock->expects($this->exactly(2))->method('coverIssueOrder')->willReturn($this->expectedIssuedOrder());

        $this->issuedOrdersApiService->expects($this->exactly(2))->method('updateIssuedOrder');

        $this->facade->updateIssuedOrder($this->getDocumentDto(), '123456');
    }

    private function getDocumentDto(): DocumentDto
    {
        $documentItemsDto = [];

        $items = [
            'HLCRIO173' => 28,
            'HLCRIO172' => 10,
            'HLCRIO161' => 10,
            'HLCRIO157' => 10,
            'HLCRIO171AM' => 10,
        ];

        foreach ($items as $sku => $quantity) {
            $documentItemsDto[] = $this->createDocumentItemDto($sku, (float) $quantity);
        }

        return new DocumentDto('1', 'NUMBER123456', null, null, null, null, null, $documentItemsDto, Chronos::createFromFormat(
            'Y-m-d H:i:s',
            '2022-12-12 00:00:00'
        ), null, 'supplierCode');
    }

    private function createDocumentItemDto(string $sku, float $quantity): DocumentItemDto
    {
        return new DocumentItemDto('1', $sku, 'name', $quantity, 'ks', null, null, 0);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedIssuedOrders(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/issued_orders.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @return array<string, mixed>
     */
    private function expectedIssuedOrder(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/issued_order.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
