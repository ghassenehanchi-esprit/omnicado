<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory;

use Cake\Chronos\Chronos;
use DateTime;
use Elasticr\ServiceBus\MoneyS5\Contract\MoneyS5DefaultPaymentMethodProviderContract;
use Elasticr\ServiceBus\MoneyS5\Contract\MoneyS5DefaultShippingMethodProviderContract;
use Elasticr\ServiceBus\MoneyS5\Contract\MoneyS5OrderDtoEnhancerContract;
use Elasticr\ServiceBus\MoneyS5\Contract\MoneyS5ServiceItemsProviderContract;
use Elasticr\ServiceBus\MoneyS5\Factory\MoneyS5PaymentMethodDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\MoneyS5ShippingMethodDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\OrderDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\OrderItemDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use UnexpectedValueException;

final class OrderDtoFactoryTest extends KernelTestCase
{
    private OrderDtoFactory $factory;

    private MockObject $dispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MoneyS5OrderDtoEnhancerContract[] $enhancers */
        $enhancers = [];
        $orderItemFactory = self::getContainer()->get(OrderItemDtoFactory::class);
        $moneyS5ShippingMethodDtoFactory = self::getContainer()->get(MoneyS5ShippingMethodDtoFactory::class);
        $moneyS5PaymentMethodDtoFactory = self::getContainer()->get(MoneyS5PaymentMethodDtoFactory::class);
        $moneyS5DefaultPaymentMethodProviderContract = self::getContainer()->get(MoneyS5DefaultPaymentMethodProviderContract::class);
        $moneyS5DefaultShippingMethodProviderContract = self::getContainer()->get(MoneyS5DefaultShippingMethodProviderContract::class);
        $moneyS5ServiceItemsProviderContract = self::getContainer()->get(MoneyS5ServiceItemsProviderContract::class);
        $this->dispatcherMock = $this->createMock(TraceableEventDispatcher::class);

        $this->factory = new OrderDtoFactory(
            $enhancers,
            $orderItemFactory,
            $moneyS5ShippingMethodDtoFactory,
            $moneyS5PaymentMethodDtoFactory,
            $moneyS5DefaultPaymentMethodProviderContract,
            $moneyS5DefaultShippingMethodProviderContract,
            $moneyS5ServiceItemsProviderContract,
            $this->dispatcherMock
        );
    }

    /**
     * @test
     *
     * @dataProvider data
     * @param array<int, array<string, mixed>> $items
     **/
    public function create(array $items, string $issuedDataPath): void
    {
        $stockDocumentSamplesDtoData = $this->getOrderDto($items);

        /** @var array<int, array<string, mixed>> $stockDocumentSamplesData */
        $stockDocumentSamplesData = $this->loadContentFromFile($issuedDataPath);

        $this->dispatcherMock
            ->expects($this->exactly(1))
            ->method('dispatch');

        $this->assertEquals($stockDocumentSamplesDtoData, $this->factory->create($stockDocumentSamplesData[0]));
    }

    /**
     * @test
     **/
    public function it_throws_exception_when_items_is_missing(): void
    {
        /** @var array<int, array<string, mixed>> $stockDocumentSamplesData */
        $stockDocumentSamplesData = $this->loadContentFromFile('/../../../expectations/moneys5/stock_documents_transfers_samples_without_items.json');

        $this->expectException(UnexpectedValueException::class);
        $this->factory->create($stockDocumentSamplesData[0]);
    }

    /**
     * @return array<int, mixed>
     */
    public function data(): array
    {
        return [
            [
                'items' => [
                    0 => [
                        'id' => 1,
                        'sku' => 'BEDB-30-CH-2',
                        'quantity' => 2,
                        'price' => 12.1,
                        'priceWithoutVat' => 10.0,
                        'taxRate' => 21,
                        'tax' => 0,
                        'weight' => 3.52,
                        'rowNumber' => 1,
                    ],
                ],
                'issuedDataPath' => '/../../../expectations/moneys5/stock_documents_transfers_samples.json',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|array<int, array<string, mixed>>
     */
    private function loadContentFromFile(string $path): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . $path);

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function getOrderDto(array $items): OrderDto
    {
        $orderItemsDto = [];

        foreach ($items as $item) {
            $orderItemsDto[] = $this->createOrderItemDto(
                $item['id'],
                $item['sku'],
                (float) $item['quantity'],
                (float) $item['price'],
                (float) $item['priceWithoutVat'],
                (float) $item['taxRate'],
                (float) $item['tax'],
                (float) $item['weight'],
                (int) $item['rowNumber']
            );
        }

        $createDate = DateTime::createFromFormat('Y-m-d\TH:i:s.u', '2023-09-19T08:47:15.74');

        if ($createDate === false) {
            $createDate = Chronos::now();
        }

        return new OrderDto(
            0,
            'VJ231963',
            new AddressDto(
                'Ingredi Europa s.r.o.',
                '',
                '',
                'Formanská 257',
                '',
                'Praha',
                '14900',
                'CZE',
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
                'CZE',
                '',
                '',
                null,
                null,
            ),
            new PaymentMethodDto('B', 'Bankovním převodem', new VatRateAwareValueDto(0.0, 0.0, 0.0)),
            new ShippingMethodDto('P', 'Přepravní služba', new VatRateAwareValueDto(0.0, 0.0, 0.0), null),
            '',
            '',
            0,
            0,
            'CZK',
            $orderItemsDto,
            Chronos::createFromFormat('Y-m-d H:i:s', $createDate->format('Y-m-d H:i:s')),
            new OrderNoteDto(''),
            null,
            [],
            'Vzorek'
        );
    }

    private function createOrderItemDto(
        int $id,
        string $sku,
        float $quantity,
        float $price,
        float $priceWithoutVat,
        float $taxRate,
        float $tax,
        float $weight,
        int $rowNumber
    ): OrderItemDto {
        return new OrderItemDto($id, $sku, 'Peak Design Everyday Backpack 30L V2, charcoal', $quantity, 'ks', $price, $priceWithoutVat, $taxRate, $tax, $sku, $weight, $rowNumber);
    }
}
