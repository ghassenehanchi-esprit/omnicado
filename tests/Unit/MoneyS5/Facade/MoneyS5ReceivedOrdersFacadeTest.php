<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Api\ReceiverOrderApiService;
use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\Entity\MoneyS5SyncStatus;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5ReceivedOrdersFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\ReceivedOrderFromStockTransferDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\AttachmentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\PriceDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\StockTransferDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class MoneyS5ReceivedOrdersFacadeTest extends KernelTestCase
{
    private MoneyS5ReceivedOrdersFacade $facade;

    private MockObject $receivedOrderFromStockTransferDtoFactory;

    private MockObject $receiverOrderApiService;

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
        $moneyS5SyncStatusRepositoryMock->expects($this->exactly(0))->method('findByCustomerId')->willReturn($moneyS5SyncStatus);

        $this->receivedOrderFromStockTransferDtoFactory = $this->createMock(ReceivedOrderFromStockTransferDtoFactory::class);
        $this->receiverOrderApiService = $this->createMock(ReceiverOrderApiService::class);

        $this->facade = new MoneyS5ReceivedOrdersFacade(
            $customerProvider,
            $moneyS5SyncStatusRepositoryMock,
            $customerConfigFinderMock,
            $this->receivedOrderFromStockTransferDtoFactory,
            $this->receiverOrderApiService,
        );
    }

    /**
     * @test
     **/
    public function create_received_orders(): void
    {
        $this->receivedOrderFromStockTransferDtoFactory
            ->expects($this->exactly(1))
            ->method('create')
            ->with(
                $this->equalTo($this->getStockTransferDto()),
                $this->equalTo($this->moneyS5Config)
            )
            ->willReturn(
                $this->createReceiveOrder()
            );

        $this->receiverOrderApiService
            ->expects($this->exactly(1))
            ->method('createReceiverOrder')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->createReceiveOrder())
            );

        $this->facade->createReceivedOrders($this->getStockTransferDto(), '123456');
    }

    private function getStockTransferDto(): StockTransferDto
    {
        return new StockTransferDto(
            '1',
            'T23100351',
            $this->createPutAway(),
            $this->createReceipts(),
            Chronos::createFromFormat(
                'Y-m-d H:i:s',
                '2023-10-06 18:07:00'
            ),
            null,
            '',
            null,
            []
        );
    }

    private function createPutAway(): DocumentDto
    {
        $documentItemsDto = [];

        $items = [
            'KI1301MA' => ['name' => 'Pitaka Air Case, black/grey - iPhone 13',
                'quantity' => 18],
            'HLCRIO172' => ['name' => 'iOttie Easy One Touch 5 Air Vent Mount',
                'quantity' => 10],
        ];

        foreach ($items as $sku => $item) {
            $documentItemsDto[] = $this->createPutAwayItem($sku, $item['name'], (float) $item['quantity']);
        }

        return new DocumentDto(
            '1',
            'V23100904',
            new AddressDto(
                'Ingredi Europa s.r.o.',
                '',
                '',
                'Formanská 257',
                '',
                'Praha',
                '14900',
                'Česko',
                '',
                '',
                '28544668',
                'CZ28544668',
            ),
            new AddressDto(
                'Ingredi Europa s.r.o.',
                '',
                '',
                'Tř. 3. května 910',
                '',
                'Zlín-Malenovice',
                '76302',
                'Česko',
                '',
                '',
                '28544668',
                'CZ28544668',
            ),
            new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
            new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), ''),
            null,
            $documentItemsDto,
            Chronos::createFromFormat('Y-m-d H:i:s', '2022-12-12 00:00:00'),
            null,
            'ADR02977',
            '',
            null,
            null,
            [],
            '',
            '01',
            null
        );
    }

    private function createPutAwayItem(string $sku, string $name, float $quantity): DocumentItemDto
    {
        return new DocumentItemDto('1', $sku, $name, $quantity, 'ks', null, null, 0);
    }

    /**
     * @return DocumentDto[]
     */
    private function createReceipts(): array
    {
        return [
            $this->createReceipt(
                '1',
                'P23100402',
                new AddressDto(
                    'Ingredi Europa s.r.o.',
                    '',
                    '',
                    'Formanská 257',
                    '',
                    'Praha',
                    '14900',
                    'Česko',
                    '',
                    '',
                    '28544668',
                    'CZ28544668',
                ),
                new AddressDto(
                    'Ingredi Europa s.r.o.',
                    '',
                    '',
                    'Tř. 3. května 910',
                    '',
                    'Zlín-Malenovice',
                    '76302',
                    'Česko',
                    '',
                    '',
                    '28544668',
                    'CZ28544668',
                ),
                new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
                new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), ''),
                null,
                $this->createReceiptItems([
                    'KI1301MA' => ['name' => 'Pitaka Air Case, black/grey - iPhone 13',
                        'quantity' => 18],
                ]),
                Chronos::createFromFormat('Y-m-d H:i:s', '2022-12-12 00:00:00'),
                null,
                'ADR02977',
                '',
                null,
                null,
                [],
                '',
                '48',
            ),
            $this->createReceipt(
                '1',
                'P23100403',
                new AddressDto(
                    'Ingredi Europa s.r.o.',
                    '',
                    '',
                    'Formanská 257',
                    '',
                    'Praha',
                    '14900',
                    'Česko',
                    '',
                    '',
                    '28544668',
                    'CZ28544668',
                ),
                new AddressDto(
                    'Ingredi Europa s.r.o.',
                    '',
                    '',
                    'Tř. 3. května 910',
                    '',
                    'Zlín-Malenovice',
                    '76302',
                    'Česko',
                    '',
                    '',
                    '28544668',
                    'CZ28544668',
                ),
                new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
                new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), ''),
                null,
                $this->createReceiptItems([
                    'HLCRIO172' => ['name' => 'iOttie Easy One Touch 5 Air Vent Mount',
                        'quantity' => 10],
                ]),
                Chronos::createFromFormat('Y-m-d H:i:s', '2022-12-12 00:00:00'),
                null,
                'ADR02977',
                '',
                null,
                null,
                [],
                '',
                '48',
            ),
        ];
    }

    /**
     * @param DocumentItemDto[]    $items
     * @param AttachmentDto[] $attachments
     * /
     */
    private function createReceipt(
        string $id,
        string $number,
        ?AddressDto $billingAddress,
        ?AddressDto $shippingAddress,
        ?PaymentMethodDto $paymentMethod,
        ?ShippingMethodDto $shippingMethod,
        ?PriceDto $price,
        array $items,
        Chronos $createdAt,
        ?DocumentReferenceDto $origin = null,
        string $supplier = '',
        string $note = '',
        ?string $state = null,
        ?string $paymentState = null,
        array $attachments = [],
        string $externalNumber = '',
        ?string $stockroomId = null,
        Chronos $sentAt = null,
    ): DocumentDto {
        return new DocumentDto(
            $id,
            $number,
            $billingAddress,
            $shippingAddress,
            $paymentMethod,
            $shippingMethod,
            $price,
            $items,
            $createdAt,
            $origin,
            $supplier,
            $note,
            $state,
            $paymentState,
            $attachments,
            $externalNumber,
            $stockroomId,
            $sentAt
        );
    }

    /**
     * @param array<string, array<string, mixed>> $items
     * @return DocumentItemDto[]
     */
    private function createReceiptItems(array $items): array
    {
        $documentItemsDto = [];

        foreach ($items as $sku => $item) {
            $documentItemsDto[] = new DocumentItemDto('1', $sku, $item['name'], (float) $item['quantity'], 'ks', null, null, 0);
        }

        return $documentItemsDto;
    }

    /**
     * @return array<string, mixed>
     */
    private function createReceiveOrder(): array
    {
        return [
            'PriznakVyrizeno' => true,
            'Nazev' => 'Převodka',
            'Odkaz' => '48',
            'DatumVystaveni' => Chronos::now()->format('Y-m-d H:i:s'),
            'Group_ID' => 'FE884C42-0733-4862-9F5D-CC33AFB7B394',
            'Poznamka' => 'Objednávka přijatá',
            'Priorita' => 5,
            'DIC' => 'CZ28544668',
            'IC' => '28544668',

            'Firma_ID' => '69c08f56-4fcc-439d-ae0d-17d643cdf668',
            'AdresaNazev' => 'Ingredi Europa s.r.o.',
            'AdresaUlice' => 'Formanská 257',
            'AdresaMisto' => 'Praha',
            'AdresaPSC' => '14900',

            'FakturacniAdresaFirma_ID' => '69c08f56-4fcc-439d-ae0d-17d643cdf668',
            'FakturacniAdresaNazev' => 'Ingredi Europa s.r.o.',
            'FakturacniAdresaUlice' => 'Formanská 257',
            'FakturacniAdresaMisto' => 'Praha',
            'FakturacniAdresaPSC' => '14900',
            'FakturacniAdresaStat' => 'Česko',

            'DodaciAdresaFirma_ID' => '69c08f56-4fcc-439d-ae0d-17d643cdf668',
            'DodaciAdresaNazev' => 'Ingredi Europa s.r.o.',
            'DodaciAdresaUlice' => 'Tř. 3. května 910',
            'DodaciAdresaMisto' => 'Zlín-Malenovice',
            'DodaciAdresaPSC' => '76302',
            'DodaciAdresaStat' => 'Česko',
            'Polozky' =>
             [

                 '0' =>
                     [
                         'TypObsahu' => 1,
                         'Katalog' => 'HLCRIO173',
                         'Nazev' => 'HLCRIO173',
                         'Jednotka' => 'ks',
                         'Mnozstvi' => 28,
                         'ObsahPolozky' => [
                             'Artikl_ID' => 'ca2cbfc1-4940-45d5-9488-57d0d095ce61',
                             'Sklad_ID' => '3400ab52-75dc-4ca4-9af1-543a20b925df',
                         ],
                     ],
                 '1' =>
                     [
                         'TypObsahu' => 1,
                         'Katalog' => 'HLCRIO173',
                         'Nazev' => 'HLCRIO173',
                         'Jednotka' => 'ks',
                         'Mnozstvi' => 28,
                         'ObsahPolozky' => [
                             'Artikl_ID' => 'ca2cbfc1-4940-45d5-9488-57d0d095ce61',
                             'Sklad_ID' => '3400ab52-75dc-4ca4-9af1-543a20b925df',
                         ],
                     ],

             ],
        ];
    }
}
