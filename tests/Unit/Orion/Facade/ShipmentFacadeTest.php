<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Orion\Facade;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\Orion\Factory\ShipmentHashFactory;
use Elasticr\ServiceBus\Orion\Factory\ShipmentToOrionOrdersFactory;
use Elasticr\ServiceBus\Orion\Factory\ShipmentXmlFactory;
use Elasticr\ServiceBus\Orion\ValueObject\OrionConfig;
use Elasticr\ServiceBus\Orion\ValueObject\ShipmentTransferConfig;
use Elasticr\ServiceBus\Orion\ValueObject\SupplierTransferConfig;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShipmentDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ShipmentFacadeTest extends KernelTestCase
{
    private OrionConfig $orionConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orionConfig = new OrionConfig(
            'xxx',
            'xxx',
            'xxx',
            'xxx',
            'xxx',
            new SupplierTransferConfig(
                'Ingredi Europa s.r.o.',
                'Společnost je zapsána v Obchodním rejstříku vedeném pod spisovou značkou C 149265 vedená u Městského soudu v Praze',
                'Formanská 257',
                'Praha',
                '14900',
                'CZE',
                '28544668',
                'CZ28544668',
                '8594184310007',
            ),
            new ShipmentTransferConfig(
                [
                    '0' => [
                        'sourceCountryCode' => 'CZE',
                        'destinationCountryCode' => 'CZ',
                        'destinationCountryName' => 'Česká republika',
                    ],
                    '1' => [
                        'sourceCountryCode' => 'SVK',
                        'destinationCountryCode' => 'SK',
                        'destinationCountryName' => 'Slovenská republika',
                    ],
                ]
            )
        );
    }

    /**
     * @test
     **/
    public function send_xml_file_to_api(): void
    {
        /** @var ShipmentToOrionOrdersFactory $shipmentToOrdersFactory */
        $shipmentToOrdersFactory = self::getContainer()->get(ShipmentToOrionOrdersFactory::class);

        /** @var ShipmentXmlFactory $shipmentXmlFactory */
        $shipmentXmlFactory = self::getContainer()->get(ShipmentXmlFactory::class);

        /** @var ShipmentHashFactory $shipmentHashFactory */
        $shipmentHashFactory = self::getContainer()->get(ShipmentHashFactory::class);

        $createdAt = Chronos::now();
        $this->assertEquals($this->expectedOrders(), $shipmentToOrdersFactory->create($this->getDocumentDto($createdAt)));

        foreach ($this->expectedOrders() as $orderNumber => $orderItems) {
            $creationDate = Chronos::now();

            $externalOrderNumber = 'OB23193136';
            $receivedOrderNumber = 'SO2405830';
            $createDate = '2023-07-03T13:10:01.01';

            $issuedDeliveryNote = [
                'CustomerOrderNumber' => $externalOrderNumber,
                'ReceivedOrderNumber' => $receivedOrderNumber,
                'CreateDate' => $createDate,
            ];

            $this->assertEquals(
                $this->expectedXmlFile($orderNumber, $creationDate, $issuedDeliveryNote),
                $shipmentXmlFactory->create(
                    $this->getDocumentDto($creationDate),
                    $creationDate,
                    $orderNumber,
                    $orderItems,
                    $this->orionConfig,
                    new ShipmentDto($orderNumber, $externalOrderNumber, $receivedOrderNumber, DateHelper::convertStringToChronos('2023-07-03T13:10:01.01'))
                )
            );
            $this->assertEquals(
                $this->expectedHash($orderNumber, $creationDate, $issuedDeliveryNote),
                $shipmentHashFactory->create($this->expectedXmlFile($orderNumber, $creationDate, $issuedDeliveryNote))
            );
        }
    }

    /**
     * @return array <string, mixed>
     */
    private function expectedOrders(): array
    {
        return [
            'DV2314923' =>
                [
                    'GE972233976CZ' =>

                    [
                        0 =>
                    $this->createDocumentItemDto(
                        '1af8ce99-be07-4604-8252-514164d4ebae',
                        '214121117272',
                        'UAG Civilian, olive - Samsung Galaxy S23',
                        1.0,
                        'ks',
                        'GE972233976CZ',
                        'DV2314923',
                        ['800700601']
                    ),
                    ],

                    'GE972233977CZ' =>

                        [
                            0 =>
                        $this->createDocumentItemDto(
                            '1af8ce99-be07-4604-8252-514164d4ebac',
                            'LO1219',
                            'LEGO Minecraft 21126',
                            5.0,
                            'ks',
                            'GE972233977CZ',
                            'DV2314923',
                            ['800700602']
                        ),
                        ],
                ],

            'DV2314924' =>
                [
                    'GE972233978CZ' =>

                        [
                            0 =>
                    $this->createDocumentItemDto(
                        '1e1f1cd1-315f-40fe-bcef-cc4aeee7e2e8',
                        '114278113940',
                        'UAG Monarch, kevlar black - iPhone 15 Pro',
                        7.0,
                        'ks',
                        'GE972233978CZ',
                        'DV2314924',
                        ['800700603']
                    ),
                        ],

                    'GE972233979CZ' =>

                        [
                            0 =>
                    $this->createDocumentItemDto('1e1f1cd1-315f-40fe-bcef-cc4aeee7e2e9', 'LO1218', 'LEGO Minecraft 21125', 10.0, 'ks', 'GE972233979CZ', 'DV2314924', ['800700604']),
                        ],
                ],
        ];
    }

    /**
     * @param array<string, string> $issuedDeliveryNote
     */
    private function expectedXmlFile(string $number, Chronos $creationDate, array $issuedDeliveryNote): string
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/orion/' . $number . '.xml');

        $fileContent = str_replace('CREATION_DATE', $creationDate->format('Y-m-d'), $fileContent);
        $fileContent = str_replace('CREATION_TIME', $creationDate->format('H:i'), $fileContent);

        $fileContent = str_replace('CREATED_AT_DATE', $creationDate->format('Y-m-d'), $fileContent);
        $fileContent = str_replace('CREATED_AT_TIME', $creationDate->format('H:i'), $fileContent);

        $fileContent = str_replace('ORDER_NUMBER', $issuedDeliveryNote['CustomerOrderNumber'], $fileContent);
        $fileContent = str_replace('ORDER_DATE', DateHelper::convertStringToChronos($issuedDeliveryNote['CreateDate'])->format('Y-m-d'), $fileContent);
        $fileContent = str_replace('SUPPLIER_NUMBER', $issuedDeliveryNote['ReceivedOrderNumber'], $fileContent);
        $fileContent = str_replace('SUPPLIER_DATE', DateHelper::convertStringToChronos($issuedDeliveryNote['CreateDate'])->format('Y-m-d'), $fileContent);

        $xmlShipment = new SimpleXmlElement($fileContent);

        $xmlWellFormedShipment = $xmlShipment->asXML();

        if ($xmlWellFormedShipment) {
            return $xmlWellFormedShipment;
        }

        return '';
    }

    /**
     * @param array<string, string> $issuedDeliveryNote
     */
    private function expectedHash(string $number, Chronos $creationDate, array $issuedDeliveryNote): string
    {
        $shipmentHash = hash('sha256', $this->expectedXmlFile($number, $creationDate, $issuedDeliveryNote), true);

        return bin2hex($shipmentHash);
    }

    private function getDocumentDto(Chronos $createdAt): DocumentDto
    {
        return new DocumentDto(
            '2045',
            'SH0123100637',
            new AddressDto(
                'Alza.cz a.s.',
                '',
                '',
                'Jankovcova 1522/53',
                '',
                'Praha 7',
                '17000',
                'CZE',
                '',
                '',
                '',
                '',
                '8594177950005'
            ),
            new AddressDto(
                'Alza.cz a.s. Chrášťany',
                '',
                '',
                'Severní 255',
                '',
                'Chráštany u Prahy',
                '252 19',
                'CZE',
                '',
                '',
                '',
                '',
                '8594177950005'
            ),
            null,
            null,
            null,
            [
                $this->createDocumentItemDto(
                    '1af8ce99-be07-4604-8252-514164d4ebae',
                    '214121117272',
                    'UAG Civilian, olive - Samsung Galaxy S23',
                    1.0,
                    'ks',
                    'GE972233976CZ',
                    'DV2314923',
                    ['800700601'],
                ),
                $this->createDocumentItemDto('1af8ce99-be07-4604-8252-514164d4ebac', 'LO1219', 'LEGO Minecraft 21126', 5.0, 'ks', 'GE972233977CZ', 'DV2314923', ['800700602']),
                $this->createDocumentItemDto(
                    '1e1f1cd1-315f-40fe-bcef-cc4aeee7e2e8',
                    '114278113940',
                    'UAG Monarch, kevlar black - iPhone 15 Pro',
                    7.0,
                    'ks',
                    'GE972233978CZ',
                    'DV2314924',
                    ['800700603'],
                ),
                $this->createDocumentItemDto('1e1f1cd1-315f-40fe-bcef-cc4aeee7e2e9', 'LO1218', 'LEGO Minecraft 21125', 10.0, 'ks', 'GE972233979CZ', 'DV2314924', ['800700604']),
            ],
            $createdAt,
            new DocumentReferenceDto('order', '6270', ''),
            '',
            '',
            null,
            null,
            [],
            '',
            null,
            Chronos::createFromFormat('Y-m-d H:i:s', '2023-10-19 07:27:23')
        );
    }

    /**
     * @param array<int,string> $serialNumbers
     */
    private function createDocumentItemDto(
        string $id,
        string $sku,
        string $name,
        float $quantity,
        string $unit,
        string $serialShippingContainerCode,
        string $orderNumber,
        array $serialNumbers
    ): DocumentItemDto {
        return new DocumentItemDto(
            $id,
            $sku,
            $name,
            $quantity,
            $unit,
            new VatRateAwareValueDto(19.55, 19.55, 0.0),
            '',
            0.05,
            new DocumentItemReferenceDto(
                new DocumentReferenceDto(
                    'order',
                    '6270',
                    $orderNumber
                ),
                '101785'
            ),
            [0 => $serialShippingContainerCode],
            null,
            null,
            null,
            $serialNumbers,
            null,
            null
        );
    }
}
