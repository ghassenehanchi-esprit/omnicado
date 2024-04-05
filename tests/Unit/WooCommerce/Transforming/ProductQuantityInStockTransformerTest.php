<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Transforming;

use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;
use Elasticr\ServiceBus\WooCommerce\Transforming\ProductQuantityInStockTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductQuantityInStockTransformerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function tranform_dto_to_array(): void
    {
        /** @var ProductQuantityInStockTransformer $productQuantityInStockTransformer */
        $productQuantityInStockTransformer = self::getContainer()->get(ProductQuantityInStockTransformer::class);

        $productQuantityDto = new ProductQuantityInStockDto('10', 10, 10, '');

        $this->assertEquals($productQuantityInStockTransformer->transform($productQuantityDto), [
            'id' => 10,
            'stock_quantity' => 10,
        ]);
    }
}
