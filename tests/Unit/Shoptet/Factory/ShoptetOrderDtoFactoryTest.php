<?php

declare(strict_types=1);

namespace Unit\Shoptet\Factory;

use Cake\Chronos\Chronos;
use DateTime;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderCouponDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Elasticr\ServiceBus\Shoptet\Exception\OrderCreationException;
use Elasticr\ServiceBus\Shoptet\Factory\ShoptetOrderDtoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ShoptetOrderDtoFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function create_dto_from_data_array(): void
    {
        /** @var ShoptetOrderDtoFactory $factory */
        $factory = self::getContainer()->get(ShoptetOrderDtoFactory::class);

        $data = json_decode('{
            "code": "DEMO000005",
            "externalCode": null,
            "email": "test@test.cz",
            "customerGuid": null,
            "birthDate": null,
            "phone": "+420722123456",
            "addressesEqual": false,
            "creationTime": "2021-07-12T14:52:38+0200",
            "changeTime": "2021-07-12T15:52:38+0200",
            "cashDeskOrder": false,
            "stockId": 1,
            "clientCode": null,
            "companyId": null,
            "vatPayer": false,
            "paid": null,
            "vatId": null,
            "taxId": null,
            "language": "cs",
            "referer": null,
            "clientIPAddress": "127.0.0.1",
            "adminUrl": "https://ergotep.myshoptet.com/admin/objednavky-detail?id=5",
            "billingAddress": {
                "company": "Název společnosti",
                "fullName": "Jméno a příjmení",
                "street": "Ulice",
                "houseNumber": null,
                "city": "Město",
                "district": null,
                "additional": null,
                "zip": "111 00",
                "countryCode": "CZ",
                "regionName": null,
                "regionShortcut": null
            },
            "deliveryAddress": {
                "company": null,
                "fullName": null,
                "street": null,
                "houseNumber": null,
                "city": null,
                "district": null,
                "additional": null,
                "zip": null,
                "countryCode": null,
                "regionName": null,
                "regionShortcut": null
            },
            "billingMethod": null,
            "status": {
                "id": -1,
                "name": "Nevyřízená"
            },
            "price": {
                "vat": "0.00",
                "toPay": "420.00",
                "currencyCode": "CZK",
                "withVat": "420.00",
                "withoutVat": "420.00",
                "exchangeRate": "1.00000000"
            },
            "shipping": null,
            "paymentMethod": null,
            "onlinePaymentLink": null,
            "paymentMethods": [
                {
                    "paymentMethod": {
                        "guid": null,
                        "name": "Hotově"
                    },
                    "itemId": 18
                }
            ],
            "shippings": [
                {
                    "shipping": {
                        "guid": null,
                        "name": "Česká Pošta"
                    },
                    "itemId": 17
                }
            ],
            "items": [
                {
                    "productGuid": null,
                    "itemType": "billing",
                    "name": "Hotově",
                    "variantName": null,
                    "brand": null,
                    "remark": null,
                    "weight": "0.000",
                    "additionalField": null,
                    "amount": "1.000",
                    "amountUnit": null,
                    "priceRatio": "1.0000",
                    "code": null,
                    "supplierName": null,
                    "warrantyDescription": null,
                    "amountCompleted": "0.000",
                    "itemId": 18,
                    "status": {
                        "id": -1,
                        "name": "Nevyřízená"
                    },
                    "itemPrice": {
                        "withVat": "0.00",
                        "withoutVat": "0.00",
                        "vat": "0.00",
                        "vatRate": "0.00"
                    },
                    "displayPrices": [
                        {
                            "withVat": "0.00",
                            "withoutVat": "0.00",
                            "vat": "0.00",
                            "vatRate": "0.00"
                        }
                    ],
                    "buyPrice": null,
                    "recyclingFee": null
                },
                {
                    "productGuid": null,
                    "itemType": "shipping",
                    "name": "Česká Pošta",
                    "variantName": null,
                    "brand": null,
                    "remark": null,
                    "weight": "0.000",
                    "additionalField": null,
                    "amount": "1.000",
                    "amountUnit": null,
                    "priceRatio": "1.0000",
                    "code": null,
                    "supplierName": null,
                    "warrantyDescription": null,
                    "amountCompleted": "0.000",
                    "itemId": 17,
                    "status": {
                        "id": -1,
                        "name": "Nevyřízená"
                    },
                    "itemPrice": {
                        "withVat": "0.00",
                        "withoutVat": "0.00",
                        "vat": "0.00",
                        "vatRate": "0.00"
                    },
                    "displayPrices": [
                        {
                            "withVat": "0.00",
                            "withoutVat": "0.00",
                            "vat": "0.00",
                            "vatRate": "0.00"
                        }
                    ],
                    "buyPrice": null,
                    "recyclingFee": null
                },
                {
                    "productGuid": "533bb41d-d978-11e0-b04f-57a43310b768",
                    "itemType": "product",
                    "name": "Nike The Next",
                    "variantName": null,
                    "brand": "Nike",
                    "remark": null,
                    "weight": "0.000",
                    "additionalField": null,
                    "amount": "2.000",
                    "amountUnit": "ks",
                    "priceRatio": "1.0000",
                    "code": "0021",
                    "supplierName": null,
                    "warrantyDescription": null,
                    "amountCompleted": "0.000",
                    "itemId": 16,
                    "status": {
                        "id": -1,
                        "name": "Nevyřízená"
                    },
                    "itemPrice": {
                        "withVat": "520.00",
                        "withoutVat": "520.00",
                        "vat": "0.00",
                        "vatRate": "0.00"
                    },
                    "displayPrices": [
                        {
                            "withVat": "520.00",
                            "withoutVat": "520.00",
                            "vat": "0.00",
                            "vatRate": "0.00"
                        }
                    ],
                    "buyPrice": {
                        "withVat": "400.00",
                        "withoutVat": "400.00",
                        "vat": "0.00",
                        "vatRate": "0.00"
                    },
                    "recyclingFee": null
                },
                {
                    "productGuid":null,
                    "itemType":"discount-coupon",
                    "name":"Slevov\u00fd kupon \u010d. BOUT100 - Sleva 100 K\u010d",
                    "variantName":null,
                    "brand":null,
                    "remark":null,
                    "weight":"0.000",
                    "additionalField":null,
                    "amount":"1.000",
                    "amountUnit":null,
                    "priceRatio":"1.0000",
                    "code":null,
                    "supplierName":null,
                    "warrantyDescription":null,
                    "amountCompleted":"0.000",
                    "itemId":33169,
                    "status":{"id":-1,
                    "name":"Nevy\u0159\u00edzen\u00e1"},
                    "itemPrice":{"withVat":"-100.00",
                    "withoutVat":"-82.64",
                    "vat":"-17.36",
                    "vatRate":"21.00"},
                    "displayPrices":[{"withVat":"-100.00",
                    "withoutVat":"-82.64",
                    "vat":"-17.36",
                    "vatRate":"21.00"}],
                    "buyPrice":null,
                    "recyclingFee":null
                }
            ]
        }', true, 512, JSON_THROW_ON_ERROR);

        $dtoBillingAddress = new AddressDto('Název společnosti', 'Jméno', 'a příjmení', 'Ulice', '', 'Město', '111 00', 'CZ', 'test@test.cz', '+420722123456', null, null);
        $dtoShippingAddress = new AddressDto(null, '', '', '', null, '', '', '', '', '', '', '');
        $dtoPayment = new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0));
        $dtoShippingMethod = new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), null);
        $dtoNotes = new OrderNoteDto('');
        $items = [];
        $items[] = new OrderItemDto(16, '0021', 'Nike The Next', 2.0, 'ks', 520.0, 520.0, 0, 0, null, 0, 0);

        $coupon = new OrderCouponDto('', 'Slevový kupon č. BOUT100 - Sleva 100 Kč', -100.0, -82.64, 21.00);

        $dto = new OrderDto(
            0,
            'DEMO000005',
            $dtoBillingAddress,
            $dtoShippingAddress,
            $dtoPayment,
            $dtoShippingMethod,
            '-1',
            'nezaplaceno',
            420.00,
            420.00,
            'CZK',
            $items,
            Chronos::createFromFormat(DateTime::ATOM, '2021-07-12T14:52:38+0200'),
            $dtoNotes,
            $coupon
        );

        $this->assertEquals($dto, $factory->createFromApiResponse($data));
    }

    /**
     * @test
     */
    public function create_dto_from_data_array_2(): void
    {
        /** @var ShoptetOrderDtoFactory $factory */
        $factory = self::getContainer()->get(ShoptetOrderDtoFactory::class);

        $data = json_decode('{
            "code": "2021000002",
            "externalCode": null,
            "email": "test@test.cz",
            "customerGuid": null,
            "birthDate": null,
            "phone": "+420722123456",
            "addressesEqual": true,
            "creationTime": "2021-09-29T21:17:22+0200",
            "changeTime": "2021-10-09T22:23:34+0200",
            "cashDeskOrder": false,
            "stockId": 1,
            "clientCode": null,
            "companyId": null,
            "vatPayer": false,
            "paid": true,
            "vatId": "",
            "taxId": "",
            "language": "cs",
            "referer": null,
            "clientIPAddress": "85.163.24.53",
            "adminUrl": "https://ergotep.myshoptet.com/admin/objednavky-detail?id=12",
            "billingAddress": {
                "company": null,
                "fullName": "test test",
                "street": "Test",
                "houseNumber": null,
                "city": "Test",
                "district": null,
                "additional": null,
                "zip": "12345",
                "countryCode": "CZ",
                "regionName": null,
                "regionShortcut": null
            },
            "deliveryAddress": null,
            "billingMethod": {
                "name": "Hotově",
                "id": 3
            },
            "status": {
                "id": -1,
                "name": "Nevyřízená"
            },
            "price": {
                "vat": "0.00",
                "toPay": "260.00",
                "currencyCode": "CZK",
                "withVat": "260.00",
                "withoutVat": "260.00",
                "exchangeRate": "1.00000000"
            },
            "shipping": {
                "guid": "8fdb2c89-3fae-11e2-a723-705ab6a2ba75",
                "name": "Osobní odběr"
            },
            "paymentMethod": {
                "guid": "b57f91bb-e920-11e0-baa3-7dc668b75ca8",
                "name": "Hotově"
            },
            "onlinePaymentLink": null,
            "paymentMethods": [
                {
                    "paymentMethod": {
                        "guid": "b57f91bb-e920-11e0-baa3-7dc668b75ca8",
                        "name": "Hotově"
                    },
                    "itemId": 36
                }
            ],
            "shippings": [
                {
                    "shipping": {
                        "guid": "8fdb2c89-3fae-11e2-a723-705ab6a2ba75",
                        "name": "Osobní odběr"
                    },
                    "itemId": 33
                }
            ],
            "items": [
                {
                    "productGuid": null,
                    "itemType": "billing",
                    "name": "Hotově",
                    "variantName": null,
                    "brand": null,
                    "remark": null,
                    "weight": "0.000",
                    "additionalField": null,
                    "amount": "1.000",
                    "amountUnit": null,
                    "priceRatio": "1.0000",
                    "code": null,
                    "supplierName": null,
                    "warrantyDescription": null,
                    "amountCompleted": "0.000",
                    "itemId": 36,
                    "status": {
                        "id": -1,
                        "name": "Nevyřízená"
                    },
                    "itemPrice": {
                        "withVat": "0.00",
                        "withoutVat": "0.00",
                        "vat": "0.00",
                        "vatRate": "0.00"
                    },
                    "displayPrices": [
                        {
                            "withVat": "0.00",
                            "withoutVat": "0.00",
                            "vat": "0.00",
                            "vatRate": "0.00"
                        }
                    ],
                    "buyPrice": null,
                    "recyclingFee": null
                },
                {
                    "productGuid": null,
                    "itemType": "shipping",
                    "name": "Osobní odběr",
                    "variantName": null,
                    "brand": null,
                    "remark": null,
                    "weight": "0.000",
                    "additionalField": null,
                    "amount": "1.000",
                    "amountUnit": null,
                    "priceRatio": "1.0000",
                    "code": null,
                    "supplierName": null,
                    "warrantyDescription": null,
                    "amountCompleted": "0.000",
                    "itemId": 33,
                    "status": {
                        "id": -1,
                        "name": "Nevyřízená"
                    },
                    "itemPrice": {
                        "withVat": "0.00",
                        "withoutVat": "0.00",
                        "vat": "0.00",
                        "vatRate": "0.00"
                    },
                    "displayPrices": [
                        {
                            "withVat": "0.00",
                            "withoutVat": "0.00",
                            "vat": "0.00",
                            "vatRate": "0.00"
                        }
                    ],
                    "buyPrice": null,
                    "recyclingFee": null
                },
                {
                    "productGuid": "533bb41d-d978-11e0-b04f-57a43310b768",
                    "itemType": "product",
                    "name": "Nike The Next",
                    "variantName": null,
                    "brand": "Nike",
                    "remark": null,
                    "weight": "0.000",
                    "additionalField": null,
                    "amount": "1.000",
                    "amountUnit": "ks",
                    "priceRatio": "1.0000",
                    "code": "0021",
                    "supplierName": null,
                    "warrantyDescription": "2 roky",
                    "amountCompleted": "0.000",
                    "itemId": 30,
                    "status": {
                        "id": -1,
                        "name": "Nevyřízená"
                    },
                    "itemPrice": {
                        "withVat": "260.00",
                        "withoutVat": "260.00",
                        "vat": "0.00",
                        "vatRate": "0.00"
                    },
                    "displayPrices": [
                        {
                            "withVat": "260.00",
                            "withoutVat": "260.00",
                            "vat": "0.00",
                            "vatRate": "0.00"
                        }
                    ],
                    "buyPrice": null,
                    "recyclingFee": null
                }
            ]
        }', true, 512, JSON_THROW_ON_ERROR);

        $dtoBillingAddress = new AddressDto(null, 'test', 'test', 'Test', '', 'Test', '12345', 'CZ', 'test@test.cz', '+420722123456', null, '');
        $dtoPayment = new PaymentMethodDto('b57f91bb-e920-11e0-baa3-7dc668b75ca8', 'Hotově', new VatRateAwareValueDto(0, 0, 0));
        $dtoShippingMethod = new ShippingMethodDto('8fdb2c89-3fae-11e2-a723-705ab6a2ba75', 'Osobní odběr', new VatRateAwareValueDto(0, 0, 0), null);
        $dtoNotes = new OrderNoteDto('');
        $items = [];
        $items[] = new OrderItemDto(30, '0021', 'Nike The Next', 1.0, 'ks', 260.0, 260.0, 0, 0, null, 0, 0);

        $dto = new OrderDto(
            0,
            '2021000002',
            $dtoBillingAddress,
            null,
            $dtoPayment,
            $dtoShippingMethod,
            '-1',
            'zaplaceno',
            260.00,
            260.00,
            'CZK',
            $items,
            Chronos::createFromFormat(DateTime::ATOM, '2021-09-29T21:17:22+0200'),
            $dtoNotes
        );

        $this->assertEquals($dto, $factory->createFromApiResponse($data));
    }

    /**
     * @test
     */
    public function create_dto_from_data_array_empty_billing_address(): void
    {
        /** @var ShoptetOrderDtoFactory $factory */
        $factory = self::getContainer()->get(ShoptetOrderDtoFactory::class);

        $this->expectException(OrderCreationException::class);
        $factory->createFromApiResponse(['billingAddress' => null]);
    }
}
