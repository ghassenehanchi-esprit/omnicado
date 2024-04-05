<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Provider;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Api\WarehousesApiService;
use Elasticr\ServiceBus\MoneyS5\Provider\StockroomProvider;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\StockroomDto;
use Elasticr\Support\Collection\ImmutableCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class StockroomProviderTest extends KernelTestCase
{
    private MockObject $warehousesApiService;

    private StockroomProvider $provider;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config = TestingMoneyS5ConfigFactory::create([]);

        $this->warehousesApiService = $this->createMock(WarehousesApiService::class);

        $this->provider = new StockroomProvider(
            $this->warehousesApiService,
        );
    }

    /**
     * @test
     */
    public function provide_stockroom_data(): void
    {
        $this->warehousesApiService
            ->expects($this->exactly(1))
            ->method('getWarehouses')
            ->with(
                $this->equalTo('01'),
                $this->equalTo($this->moneyS5Config)
            )
            ->willReturn(
                $this->expectedWarehouse()
            );

        $this->assertEquals(new StockroomDto('3400ab52-75dc-4ca4-9af1-543a20b925df', '01'), $this->provider->provide($this->getDocumentDto(), $this->moneyS5Config));
    }

    /**
     * @return ImmutableCollection<int, StockroomDto>
     */
    private function expectedWarehouse(): ImmutableCollection
    {
        return new ImmutableCollection([new StockroomDto('3400ab52-75dc-4ca4-9af1-543a20b925df', '01')]);
    }

    private function getDocumentDto(): DocumentDto
    {
        $items = ['NSA-EC' => ['ID' => '80175008-1c95-4577-8c16-7bc8656fdefc',
            'Nazev' => 'Netatmo Smart Smoke Alarm',
            'Mnozstvi' => 10]];
        $documentItemsDto = [];
        foreach ($items as $sku => $item) {
            $documentItemsDto[] = $this->createDocumentItemDto($item['ID'], $sku, $item['Nazev'], $item['Mnozstvi']);
        }

        return new DocumentDto(
            '1',
            'NUMBER123456',
            null,
            null,
            null,
            null,
            null,
            $documentItemsDto,
            Chronos::createFromFormat('Y-m-d H:i:s', '2022-12-12 00:00:00'),
            null,
            'supplierCode',
            'Objednávka přijatá',
            null,
            null,
            [],
            '',
            '01'
        );
    }

    private function createDocumentItemDto(string $id, string $sku, string $name, float $quantity): DocumentItemDto
    {
        return new DocumentItemDto($id, $sku, $name, $quantity, 'ks', null, null, 0);
    }
}
