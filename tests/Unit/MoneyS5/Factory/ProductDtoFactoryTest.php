<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory;

use Elasticr\ServiceBus\MoneyS5\Factory\ProductDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;
use Elasticr\ServiceBus\ServiceBus\Model\ProductParameterDto;
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

        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/moneys5/product.json') ?: '', true, 512, JSON_THROW_ON_ERROR);

        $dto = new ProductDto(528, 'ACS01832', 'Spigen Cecile, bloom - iPhone 12 mini', 0, 0, 21, 0.04, 'ks', 0, '', '', '8809710757387', [
            new ProductParameterDto('ID', '0b82d360-34fb-442f-81b8-000260e6b49a'),
        ]);

        $this->assertEquals($dto, $factory->create($data['Data'][0]));
    }
}
