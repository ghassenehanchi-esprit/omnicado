<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\ServiceBus;

use Elasticr\ServiceBus\Eso9\Factory\ProductQuantityInStockDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductQuantityInStockDtoFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_create_product_quantity_in_stock_dto_from_array_data(): void
    {
        /** @var ProductQuantityInStockDtoFactory $productQuantityInStockDtoFactory */
        $productQuantityInStockDtoFactory = self::getContainer()->get(ProductQuantityInStockDtoFactory::class);

        $data = [
            'Produkt_Id' => 'SKU-1',
            'mnStavSkladCelkem' => 10,
        ];

        $this->assertEquals(new ProductQuantityInStockDto('SKU-1', 10, 10), $productQuantityInStockDtoFactory->create($data));
    }
}
