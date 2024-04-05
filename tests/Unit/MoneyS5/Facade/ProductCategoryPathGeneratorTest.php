<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Elasticr\ServiceBus\MoneyS5\Generator\ProductCategoryPathGenerator;
use Elasticr\ServiceBus\ServiceBus\Model\ProductCategoryDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductCategoryPathGeneratorTest extends KernelTestCase
{
    /**
     * @test
     */
    public function generate_product_category_path(): void
    {
        $generator = new ProductCategoryPathGenerator();

        $this->assertEquals('', $generator->generate('ba82ffe5-5fca-4dfe-be07-d31d2f689379', [
            'd14d2a93-c811-431d-9482-41338f978b61' => new ProductCategoryDto(
                'd14d2a93-c811-431d-9482-41338f978b61',
                '4000015700',
                'iPhone SE 2022',
                'eedca981-d1cb-48b4-b762-4f4ba4f07af8'
            ),
            'eedca981-d1cb-48b4-b762-4f4ba4f07af8' => new ProductCategoryDto('eedca981-d1cb-48b4-b762-4f4ba4f07af8', '4MODEL', '4Model zařízení', null),
        ]));

        $this->assertEquals('4Model zařízení / iPhone SE 2022', $generator->generate('d14d2a93-c811-431d-9482-41338f978b61', [
            'd14d2a93-c811-431d-9482-41338f978b61' => new ProductCategoryDto(
                'd14d2a93-c811-431d-9482-41338f978b61',
                '4000015700',
                'iPhone SE 2022',
                'eedca981-d1cb-48b4-b762-4f4ba4f07af8'
            ),
            'eedca981-d1cb-48b4-b762-4f4ba4f07af8' => new ProductCategoryDto('eedca981-d1cb-48b4-b762-4f4ba4f07af8', '4MODEL', '4Model zařízení', null),
        ]));
    }
}
