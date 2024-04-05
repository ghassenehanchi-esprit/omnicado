<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Factory;

use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;
use Elasticr\ServiceBus\WooCommerce\Factory\ProductDtoFactory;
use Elasticr\ServiceBus\WooCommerce\ValueObject\WooCommerceTaxConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductDtoFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function create_dto_from_data_array(): void
    {
        /** @var ProductDtoFactory $factory */
        $factory = self::getContainer()->get(ProductDtoFactory::class);

        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/product.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        $taxConfig = new WooCommerceTaxConfig([
            'standard' => 21,
        ]);

        $dto = new ProductDto(4271, '6779290', 'Frosch EKO dárková sada Oase Pomeranč', 379.9, 313.9669421487603, 21, 0.3, 'ks', 3, '', '', '8594059391988');

        $this->assertEquals($dto, $factory->createFromApiResponse($data, $taxConfig));
    }
}
