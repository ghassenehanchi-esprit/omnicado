<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Provider;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Api\ProductsApiService;
use Elasticr\ServiceBus\MoneyS5\Provider\ProductsDataProvider;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5ProductsFilters;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class ProductsDataProviderTest extends KernelTestCase
{
    private MockObject $productsApiService;

    private ProductsDataProvider $provider;

    private MoneyS5ProductsFilters $expectedFilter;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        $this->moneyS5Config = TestingMoneyS5ConfigFactory::create([]);

        parent::setUp();

        $this->productsApiService = $this->createMock(ProductsApiService::class);

        $this->expectedFilter = new MoneyS5ProductsFilters(
            ['0' => 'NSA-EC'],
            null,
        );

        $this->provider = new ProductsDataProvider(
            $this->productsApiService,
        );
    }

    /**
     * @test
     */
    public function provide_products_data(): void
    {
        $this->productsApiService
            ->expects($this->exactly(1))
            ->method('getProductsFromApi')
            ->with(
                $this->equalTo($this->expectedFilter),
                $this->equalTo($this->moneyS5Config)
            )
            ->willReturn(
                $this->expectedProductions()
            );

        $this->assertEquals(['NSA-EC' => ['ID' => '80175008-1c95-4577-8c16-7bc8656fdefc',
            'Nazev' => 'Netatmo Smart Smoke Alarm']], $this->provider->provide($this->getDocumentDto(), $this->moneyS5Config));
    }

    /**
     * @return array<string, mixed>
     */
    private function expectedProductions(): array
    {
        return ['Data' => ['Articles' => [['ID' => '80175008-1c95-4577-8c16-7bc8656fdefc',
            'Katalog' => 'NSA-EC',
            'Nazev' => 'Netatmo Smart Smoke Alarm']]]];
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

        return new DocumentDto('1', 'NUMBER123456', null, null, null, null, null, $documentItemsDto, Chronos::createFromFormat(
            'Y-m-d H:i:s',
            '2022-12-12 00:00:00'
        ), null, 'supplierCode', 'Objednávka přijatá');
    }

    private function createDocumentItemDto(string $id, string $sku, string $name, float $quantity): DocumentItemDto
    {
        return new DocumentItemDto($id, $sku, $name, $quantity, 'ks', null, null, 0);
    }
}
