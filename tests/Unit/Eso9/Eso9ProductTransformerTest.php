<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Transforming\Eso9ProductTransformer;
use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9ProductTransformerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_transforms_product_dto_to_eso9_data(): void
    {
        /** @var Eso9ProductTransformer $transformer */
        $transformer = self::getContainer()->get(Eso9ProductTransformer::class);

        $this->assertEquals($this->expectedArray(), $transformer->transform($this->createProductDto()));
    }

    /**
     * @test
     */
    public function it_transforms_product_dto_to_eso9_data_ignore_empty_values(): void
    {
        /** @var Eso9ProductTransformer $transformer */
        $transformer = self::getContainer()->get(Eso9ProductTransformer::class);

        $product = new ProductDto(1, 'AB123', 'Produkt', 121.0, 100.00, 21.00, 0, 'ks', 10);

        $this->assertEquals($this->expectedArrayNoEmptyValues(), $transformer->transform($product));
    }

    private function createProductDto(): ProductDto
    {
        return new ProductDto(1, 'AB123', 'Produkt', 121.0, 100.00, 21.00, 0, 'ks', 10, '', '', '123456');
    }

    /**
     * @return array<string, mixed>
     */
    private function expectedArray(): array
    {
        return [
            'Produkt_Id' => 'AB123',
            'Produkt_Nazev' => 'Produkt',
            'procentoDPH' => '21.0000',
            'Kod_Mj' => 'ks',
            'EAN_Dodavatele' => '123456',
            'vlDelitelneBaleni' => false,
            'mnVBaleni' => 1,
            'vlKrehke' => false,
            'Hmotnost' => '0.0000',
            'VyskaBaleni' => '0.0000',
            'SirkaBaleni' => '0.0000',
            'HloubkaBaleni' => '0.0000',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function expectedArrayNoEmptyValues(): array
    {
        return [
            'Produkt_Id' => 'AB123',
            'Produkt_Nazev' => 'Produkt',
            'procentoDPH' => '21.0000',
            'Kod_Mj' => 'ks',
            'vlDelitelneBaleni' => false,
            'mnVBaleni' => 1,
            'vlKrehke' => false,
            'Hmotnost' => '0.0000',
            'VyskaBaleni' => '0.0000',
            'SirkaBaleni' => '0.0000',
            'HloubkaBaleni' => '0.0000',
        ];
    }
}
