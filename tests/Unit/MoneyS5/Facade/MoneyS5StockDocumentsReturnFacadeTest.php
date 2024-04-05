<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Elasticr\ServiceBus\MoneyS5\Api\StockDocumentsApiService;
use Elasticr\ServiceBus\MoneyS5\Constant\GraphQLOperators;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5StockDocumentsReturnFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\MoneyS5DocumentDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\MoneyS5\Service\StockDocumentTransferService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5StockDocumentFilters;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Service\DataTransferLogService;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Api\MoneyS5SyncStatusTrait;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class MoneyS5StockDocumentsReturnFacadeTest extends KernelTestCase
{
    use MoneyS5SyncStatusTrait;

    private MoneyS5StockDocumentFilters $expectedFilter;

    private MoneyS5StockDocumentsReturnFacade $facade;

    private MockObject $serviceBus;

    private MockObject $stockDocumentsApiServiceMock;

    private MockObject $documentDtoFactoryMock;

    private StockDocumentTransferService $stockDocumentsTransferService;

    private MockObject $stockDocumentsTransferServiceMock;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config =
            TestingMoneyS5ConfigFactory::create(
                [
                    'stockDocumentReturnTransferConfig' => [
                        'warehouseCodesForPutAway' => ['12',
                            '19',
                            '33',
                            '39',
                            '41',
                            '46',
                            '48',
                            '53'],
                        'warehouseCodesForReceipt' => ['01'],
                        'allowedDocumentSeriesForPutAway' => ['VJ'],
                        'allowedDocumentSeriesForReceipt' => ['PJ'],
                        'referenceForTransmission' => 'PX',
                        'nameForReturn' => 'vratka',
                    ],
                ]
            );

        $customerProviderMock = $this->createMock(CustomerProvider::class);
        $customerProviderMock->expects($this->exactly(1))->method('provideByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));

        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinderMock->expects($this->exactly(1))->method('find')->willReturn($this->moneyS5Config);

        $moneyS5SyncStatusRepositoryMock = $this->createMock(MoneyS5SyncStatusRepository::class);
        $moneyS5SyncStatusRepositoryMock->expects($this->exactly(1))->method('findByCustomerId')->willReturn($this->getSyncStatus());

        $this->expectedFilter = new MoneyS5StockDocumentFilters(
            $this->moneyS5Config->stockDocumentReturnTransferConfig()->referenceForTransmission(),
            $this->moneyS5Config->stockDocumentReturnTransferConfig()->nameForReturn(),
            null,
            $this->getSyncStatus()->lastProcessedStockDocumentReturnTime(),
            GraphQLOperators::START_WITH,
            GraphQLOperators::CONTAINS,
            GraphQLOperators::EQUAL
        );

        $this->stockDocumentsApiServiceMock = $this->createMock(StockDocumentsApiService::class);

        $this->stockDocumentsTransferService = new StockDocumentTransferService();
        $this->stockDocumentsTransferServiceMock = $this->createMock(StockDocumentTransferService::class);

        $this->serviceBus = $this->createMock(ServiceBus::class);

        $this->documentDtoFactoryMock = $this->createMock(MoneyS5DocumentDtoFactory::class);

        $this->facade = new MoneyS5StockDocumentsReturnFacade(
            $customerProviderMock,
            $moneyS5SyncStatusRepositoryMock,
            $customerConfigFinderMock,
            $this->stockDocumentsApiServiceMock,
            $this->serviceBus,
            $this->documentDtoFactoryMock,
            $this->stockDocumentsTransferServiceMock,
            $this->createMock(DataTransferLogService::class)
        );
    }

    /**
     * @test
     **/
    public function transfer_return_documents(): void
    {
        $this->stockDocumentsApiServiceMock
            ->expects($this->exactly(1))
            ->method('getStockDocuments')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->expectedFilter)
            )
            ->willReturn(
                $this->expectedArray()
            );

        $this->stockDocumentsTransferServiceMock
            ->expects($this->exactly(1))
            ->method('getReceiptsFromTransfers')
            ->with(
                $this->equalTo($this->expectedDocumentByReferenceArray()),
                $this->equalTo($this->moneyS5Config->stockDocumentReturnTransferConfig())
            )
            ->willReturn(
                $this->expectedDocumentReceiptsArray()
            );

        $this->facade->transferStockDocumentsReturn('123456');

        $this->assertEquals(
            $this->expectedDocumentReceiptsArray(),
            $this->stockDocumentsTransferService->getReceiptsFromTransfers(
                $this->expectedDocumentByReferenceArray(),
                $this->moneyS5Config->stockDocumentReturnTransferConfig()
            )
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/stock_documents_return_transfer.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function expectedDocumentByReferenceArray(): array
    {
        $documentsByReference = [];

        foreach ($this->expectedArray() as $document) {
            $referenceNumber = (string) $document['Odkaz'];
            $documentsByReference[$referenceNumber][] = $document;
        }

        return $documentsByReference;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedDocumentReceiptsArray(): array
    {
        return [
            0 => [
                'Firma_ID' => '05c9f18b-aa3a-40ff-b260-743db6a192fb',
                'Odkaz' => 'PX231015',
                'TypDokladu' => 1,
                'Modify_Date' => '2023-10-24T14:04:19.91',
                'Create_Date' => '2023-10-24T14:04:19.91',
                'CisloDokladu' => 'PX231015',
                'Nazev' => 'Vratka',
                'FakturacniAdresaNazev' => 'Ingredi Europa s.r.o.',
                'AdresaKoncovehoPrijemceKontaktniOsobaNazev' => '',
                'FakturacniAdresaUlice' => 'Formanská 257',
                'FakturacniAdresaMisto' => 'Praha',
                'FakturacniAdresaPSC' => '14900',
                'AdresaPrijemceFakturyStat' => ['KodISO3' => 'CZE'],
                'AdresaKoncovehoPrijemceEmail' => '',
                'AdresaKoncovehoPrijemceTelefon' => '',
                'IC' => '28544668',
                'DIC' => 'CZ28544668',
                'DodaciAdresaNazev' => 'Ingredi Europa s.r.o.',
                'DodaciAdresaUlice' => 'Tř. 3. května 910',
                'DodaciAdresaMisto' => 'Zlín-Malenovice',
                'DodaciAdresaPSC' => '76302',
                'AdresaKoncovehoPrijemceStat' => ['KodISO3' => 'CZE'],
                'ZpusobPlatby' => null,
                'ZpusobDopravy' => ['Nazev' => 'Přepravní služba',
                    'Kod' => 'P'],
                'SumaCelkemCM' => 7978.74,
                'SumaDanCM' => 1384.74,
                'Mena' => ['Kod' => 'CZK'],
                'Poznamka' => '',
                'DodaciAdresaFirma' => ['FaktStat' => ['KodISO3' => 'CZE']],
                'Polozky' =>
                    [
                        0 =>
                            [
                                'CisloPolozky' => 1,
                                'Katalog' => 'ADJD4S',
                                'Nazev' => 'Adonit Dash 4, silver',
                                'Mnozstvi' => 6.0,
                                'Jednotka' => 'ks',
                                'JednCenaCM' => 1099.0,
                                'CelkovaCenaCM' => 6594.0,
                                'DphCelkemCM' => 7978.74,
                                'DphSazba' => 21.0,
                                'DphDanCM' => 1384.74,
                                'IPHmotnost' => 0.0,
                                'ObsahPolozky' => ['Artikl' => ['Navod_UserData' => ''],
                                    'Sklad' => ['Kod' => '01'],
                                ],
                            ],
                    ],

            ],

            1 => [
                'Firma_ID' => '05c9f18b-aa3a-40ff-b260-743db6a192fb',
                'Odkaz' => 'PX231016',
                'TypDokladu' => 1,
                'Modify_Date' => '2023-10-24T14:05:33.07',
                'Create_Date' => '2023-10-24T14:05:33.07',
                'CisloDokladu' => 'PX231016',
                'Nazev' => 'Vratka',
                'FakturacniAdresaNazev' => 'Ingredi Europa s.r.o.',
                'AdresaKoncovehoPrijemceKontaktniOsobaNazev' => '',
                'FakturacniAdresaUlice' => 'Formanská 257',
                'FakturacniAdresaMisto' => 'Praha',
                'FakturacniAdresaPSC' => '14900',
                'AdresaPrijemceFakturyStat' => ['KodISO3' => 'CZE'],
                'AdresaKoncovehoPrijemceEmail' => '',
                'AdresaKoncovehoPrijemceTelefon' => '',
                'IC' => '28544668',
                'DIC' => 'CZ28544668',
                'DodaciAdresaNazev' => 'Ingredi Europa s.r.o.',
                'DodaciAdresaUlice' => 'Tř. 3. května 910',
                'DodaciAdresaMisto' => 'Zlín-Malenovice',
                'DodaciAdresaPSC' => '76302',
                'AdresaKoncovehoPrijemceStat' => ['KodISO3' => 'CZE'],
                'ZpusobPlatby' => null,
                'ZpusobDopravy' => ['Nazev' => 'Přepravní služba',
                    'Kod' => 'P'],
                'SumaCelkemCM' => 4896.87,
                'SumaDanCM' => 849.87,
                'Mena' => ['Kod' => 'CZK'],
                'Poznamka' => '',
                'DodaciAdresaFirma' => ['FaktStat' => ['KodISO3' => 'CZE']],
                'Polozky' =>
                    [
                        0 =>
                            [
                                'CisloPolozky' => 1,
                                'Katalog' => 'ACS03763',
                                'Nazev' => 'Spigen Smart Fold, black - iPad mini 6 2021',
                                'Mnozstvi' => 19.0,
                                'Jednotka' => 'ks',
                                'JednCenaCM' => 213.0,
                                'CelkovaCenaCM' => 4047.0,
                                'DphCelkemCM' => 4896.87,
                                'DphSazba' => 21.0,
                                'DphDanCM' => 849.87,
                                'IPHmotnost' => 0.0,
                                'ObsahPolozky' => ['Artikl' => ['Navod_UserData' => ''],
                                    'Sklad' => ['Kod' => '01'],
                                ],
                            ],
                    ],

            ],
        ];
    }
}
