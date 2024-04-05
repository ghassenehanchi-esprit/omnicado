<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Flexibee\Api;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\Flexibee\Api\SellOrderApiService;
use Elasticr\ServiceBus\Flexibee\Api\StockMovementsApiService;
use Elasticr\ServiceBus\Flexibee\Api\StockMovementsService;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\StockDocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\StockItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class StockMovementsApiServiceTest extends KernelTestCase
{
    use FlexibeeConfigTrait;

    /**
     * @test
     */
    public function income_from_advice(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $stockDocumentApiService = $this->createMock(StockMovementsService::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(201);

        $items = [
            new OrderItemDto(1, '41120015', '', 10, '', 0, 0, 0, 0, '', 0, 1),
            new OrderItemDto(2, '41120023', '', 10, '', 0, 0, 0, 0, '', 0, 2),
            new OrderItemDto(3, '41120024', '', 10, '', 0, 0, 0, 0, '', 0, 3),
            new OrderItemDto(4, '42120040', '', 10, '', 0, 0, 0, 0, '', 0, 4),
            new OrderItemDto(5, '42120041', '', 10, '', 0, 0, 0, 0, '', 0, 5),
        ];
        $order = new OrderDto(
            1,
            '210222000039',
            new AddressDto('', '', '', '', '', '', '', '', '', '', '', ''),
            null,
            new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
            new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), null),
            '',
            '',
            0,
            0,
            'CZK',
            $items,
            Chronos::now(),
            new OrderNoteDto('')
        );
        $sellOrderApiServiceMock->method('getOrderDetail')->willReturn($order);

        $service = new StockMovementsApiService($httpClientMock, $sellOrderApiServiceMock, $stockDocumentApiService);

        $httpClientMock->expects($this->exactly(1))
            ->method('request')
            ->will($this->returnCallback(function ($method, $url, $options) use ($responseMock) {
                $this->assertEquals([
                    'winstrom' => [
                        'objednavka-vydana' => [
                            'id' => 'code:210222000039',
                            'realizaceObj@type' => 'skladovy-pohyb',
                            'realizaceObj' => [
                                'varSym' => '1',
                                'typDokl' => 74,
                                'sklad' => 'code:SKLAD-HANDLING',
                                'polozkyObchDokladu' => [
                                    [
                                        'cisRad' => 5,
                                        'mj' => '10.0',
                                    ],
                                    [
                                        'cisRad' => 4,
                                        'mj' => '5.0',
                                    ],
                                    [
                                        'cisRad' => 1,
                                        'mj' => '5.0',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], $options['json']);

                return $responseMock;
            }));

        $item1 = new StockItemDto('42120041', 'SKLAD', 10);
        $item2 = new StockItemDto('42120040', 'SKLAD', 5);
        $item3 = new StockItemDto('41120015', 'SKLAD', 5);
        $service->processDocument($this->getConfig(), new StockDocumentDto('1', 'VZSPF05PDX', 'SKLAD', '210222000039', [$item1, $item2, $item3], Chronos::now(), null));
    }

    /**
     * @test
     */
    public function income_product_not_found_in_order(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $stockDocumentApiService = $this->createMock(StockMovementsService::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(201);

        $items = [];
        $order = new OrderDto(
            1,
            '210222000039',
            new AddressDto('', '', '', '', '', '', '', '', '', '', '', ''),
            null,
            new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
            new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), null),
            '',
            '',
            0,
            0,
            'CZK',
            $items,
            Chronos::now(),
            new OrderNoteDto('')
        );
        $sellOrderApiServiceMock->method('getOrderDetail')->willReturn($order);

        $service = new StockMovementsApiService($httpClientMock, $sellOrderApiServiceMock, $stockDocumentApiService);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Polozka 42120041 nebyla nalezena v objednavce 210222000039');

        $item1 = new StockItemDto('42120041', 'SKLAD', 10);
        $item2 = new StockItemDto('42120040', 'SKLAD', 5);
        $item3 = new StockItemDto('41120015', 'SKLAD', 5);
        $service->processDocument($this->getConfig(), new StockDocumentDto('1', 'VZSPF05PDX', 'SKLAD', '210222000039', [$item1, $item2, $item3], Chronos::now(), null));
    }

    /**
     * @test
     */
    public function simple_income_document_not_exists(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $stockDocumentApiService = $this->createMock(StockMovementsService::class);
        $stockDocumentApiService->method('documentExists')->willReturn(false);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(201);

        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $service = new StockMovementsApiService($httpClientMock, $sellOrderApiServiceMock, $stockDocumentApiService);

        $httpClientMock->expects($this->exactly(1))
            ->method('request')
            ->will($this->returnCallback(function ($method, $url, $options) use ($responseMock) {
                $this->assertEquals([
                    'winstrom' => [
                        'skladovy-pohyb' => [
                            'varSym' => '1',
                            'typPohybuK' => 'typPohybu.prijem',
                            'typDokl' => 66,
                            'sklad' => 'code:SKLAD-SK',
                            'typPohyhuSkladK' => 'typPohybuSklad.prijemHoly',
                            'rada' => '49',
                            'skladovePolozky' => [
                                [
                                    'cenik' => 'code:BBB',
                                    'sklad' => 'code:SKLAD',
                                    'mnozMj' => '10.0',
                                    'cenik@previousValue' => '1',
                                ],
                                [
                                    'cenik' => 'code:AAA',
                                    'sklad' => 'code:SKLAD',
                                    'mnozMj' => '5.0',
                                    'cenik@previousValue' => '1',
                                ],
                            ],
                        ],
                    ],
                ], $options['json']);

                return $responseMock;
            }));

        $item1 = new StockItemDto('BBB', 'SKLAD', 10);
        $item2 = new StockItemDto('AAA', 'SKLAD', 5);
        $service->processDocument($this->getConfig(), new StockDocumentDto('1', 'VZSPF98ZA', 'SKLAD', null, [$item1, $item2], Chronos::now(), null));
    }

    /**
     * @test
     */
    public function simple_income_document_exists(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $stockDocumentApiService = $this->createMock(StockMovementsService::class);
        $stockDocumentApiService->method('documentExists')->willReturn(true);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(201);

        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $service = new StockMovementsApiService($httpClientMock, $sellOrderApiServiceMock, $stockDocumentApiService);

        $httpClientMock->expects($this->exactly(0))
            ->method('request')
            ->will($this->returnCallback(function ($method, $url, $options) use ($responseMock) {
                $this->assertEquals([
                    'winstrom' => [
                        'skladovy-pohyb' => [
                            'varSym' => '1',
                            'typPohybuK' => 'typPohybu.prijem',
                            'typDokl' => 66,
                            'sklad' => 'code:SKLAD-SK',
                            'typPohyhuSkladK' => 'typPohybuSklad.prijemHoly',
                            'rada' => '49',
                            'skladovePolozky' => [
                                [
                                    'cenik' => 'code:BBB',
                                    'sklad' => 'code:SKLAD',
                                    'mnozMj' => '10.0',
                                    'cenik@previousValue' => '1',
                                ],
                                [
                                    'cenik' => 'code:AAA',
                                    'sklad' => 'code:SKLAD',
                                    'mnozMj' => '5.0',
                                    'cenik@previousValue' => '1',
                                ],
                            ],
                        ],
                    ],
                ], $options['json']);

                return $responseMock;
            }));

        $item1 = new StockItemDto('BBB', 'SKLAD', 10);
        $item2 = new StockItemDto('AAA', 'SKLAD', 5);
        $service->processDocument($this->getConfig(), new StockDocumentDto('1', 'VZSPF98ZA', 'SKLAD', null, [$item1, $item2], Chronos::now(), null));
    }

    /**
     * @test
     */
    public function simple_outcome(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $stockDocumentApiService = $this->createMock(StockMovementsService::class);
        $stockDocumentApiService->method('documentExists')->willReturn(false);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(201);

        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $service = new StockMovementsApiService($httpClientMock, $sellOrderApiServiceMock, $stockDocumentApiService);

        $httpClientMock->expects($this->exactly(1))
            ->method('request')
            ->will($this->returnCallback(function ($method, $url, $options) use ($responseMock) {
                $this->assertEquals([
                    'winstrom' => [
                        'skladovy-pohyb' => [
                            'varSym' => '1',
                            'typPohybuK' => 'typPohybu.vydej',
                            'typDokl' => 66,
                            'sklad' => 'code:SKLAD-CZ',
                            'typPohyhuSkladK' => 'typPohybuSklad.vydejHoly',
                            'rada' => '49',
                            'skladovePolozky' => [
                                [
                                    'cenik' => 'code:BBB',
                                    'sklad' => 'code:SKLAD',
                                    'mnozMj' => '10.0',
                                ],
                                [
                                    'cenik' => 'code:AAA',
                                    'sklad' => 'code:SKLAD',
                                    'mnozMj' => '5.0',
                                ],
                            ],
                        ],
                    ],
                ], $options['json']);

                return $responseMock;
            }));

        $item1 = new StockItemDto('BBB', 'SKLAD', 10);
        $item2 = new StockItemDto('AAA', 'SKLAD', 5);
        $service->processDocument($this->getConfig(), new StockDocumentDto('1', 'VZSVF97ZA', 'SKLAD', 'ORDER111', [$item1, $item2], Chronos::now(), null));
    }

    /**
     * @test
     */
    public function transfer(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $stockDocumentApiService = $this->createMock(StockMovementsService::class);
        $stockDocumentApiService->method('documentExists')->willReturn(false);
        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->expects($this->exactly(2))->method('toArray')->willReturnOnConsecutiveCalls(
            [
                'winstrom' => [
                    '@version' => '1.0',
                    'success' => 'true',
                    'stats' => [
                        'created' => '1',
                        'updated' => '0',
                        'deleted' => '0',
                        'skipped' => '0',
                        'failed' => '0',
                    ],
                    'results' => [
                        [
                            'id' => '417',
                            'ref' => '/c/demo/skladovy-pohyb/417',
                        ],
                    ],
                ],
            ],
            [
                'winstrom' => [
                    '@version' => '1.0',
                    'success' => 'true',
                    'stats' => [
                        'created' => '0',
                        'updated' => '1',
                        'deleted' => '0',
                        'skipped' => '0',
                        'failed' => '0',
                    ],
                    'results' => [
                        [
                            'id' => '417',
                            'request-id' => '417',
                            'ref' => '/c/demo/skladovy-pohyb/417',
                        ],
                    ],
                ],
            ]
        );
        $responseMock->method('getStatusCode')->willReturn(201);

        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $service = new StockMovementsApiService($httpClientMock, $sellOrderApiServiceMock, $stockDocumentApiService);

        $httpClientMock->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function ($method, $url, $options) use ($responseMock) {
                    $json = $options['json'];
                    $this->assertEquals([
                        'winstrom' => [
                            'skladovy-pohyb' => [
                                [
                                    'varSym' => '2',
                                    'typPohybuK' => 'typPohybu.vydej',
                                    'typDokl' => 2,
                                    'sklad' => 'code:SKLAD',
                                    'skladCil' => 'code:SKLAD-CIL',
                                    'typPohybuSkladK' => 'typPohybuSklad.vydejPrevod',
                                    'skladovePolozky' => [
                                        'skladovy-pohyb-polozka' => [
                                            [
                                                'cenik' => 'code:BBB',
                                                'typPolozkyK' => 'typPolozky.katalog',
                                                'mnozMj' => '10.0',
                                            ],
                                            [
                                                'cenik' => 'code:AAA',
                                                'typPolozkyK' => 'typPolozky.katalog',
                                                'mnozMj' => '5.0',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ], $json);

                    return $responseMock;
                }),
                $this->returnCallback(function ($method, $url, $options) use ($responseMock) {
                    $json = $options['json'];
                    $this->assertEquals([
                        'winstrom' => [
                            'skladovy-pohyb@action' => 'dokoncit-prevodku',
                            'skladovy-pohyb' => [
                                'id' => '417',
                            ],
                        ],
                    ], $json);

                    return $responseMock;
                })
            );

        $item1 = new StockItemDto('BBB', 'SKLAD', 10);
        $item2 = new StockItemDto('AAA', 'SKLAD', 5);
        $service->processDocument(
            $this->getConfig(),
            new StockDocumentDto('2', 'VZSVF05PV97', 'SKLAD', 'ORDER111', [$item1, $item2], Chronos::now(), new StockDocumentDto('1', 'VZSPF97PV05', 'SKLAD-CIL', 'ORDER111', [
                $item1,
                $item2,
            ], Chronos::now(), null))
        );
    }

    /**
     * @test
     */
    public function process_document_no_mapping_for_doc_type(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $stockDocumentApiService = $this->createMock(StockMovementsService::class);
        $sellOrderApiServiceMock = $this->createMock(SellOrderApiService::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([]);
        $responseMock->method('getStatusCode')->willReturn(201);

        $items = [];
        $order = new OrderDto(
            1,
            '210222000039',
            new AddressDto('', '', '', '', '', '', '', '', '', '', '', ''),
            null,
            new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
            new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), null),
            '',
            '',
            0,
            0,
            'CZK',
            $items,
            Chronos::now(),
            new OrderNoteDto('')
        );
        $sellOrderApiServiceMock->method('getOrderDetail')->willReturn($order);

        $service = new StockMovementsApiService($httpClientMock, $sellOrderApiServiceMock, $stockDocumentApiService);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No mapping found for document type NEZNAMY_TYP');

        $item1 = new StockItemDto('42120041', 'SKLAD', 10);
        $item2 = new StockItemDto('42120040', 'SKLAD', 5);
        $item3 = new StockItemDto('41120015', 'SKLAD', 5);
        $service->processDocument($this->getConfig(), new StockDocumentDto('1', 'NEZNAMY_TYP', 'SKLAD', '210222000039', [$item1, $item2, $item3], Chronos::now(), null));
    }
}
