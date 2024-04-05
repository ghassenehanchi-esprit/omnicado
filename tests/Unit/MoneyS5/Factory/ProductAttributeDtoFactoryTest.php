<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory;

use Elasticr\ServiceBus\MoneyS5\Factory\ProductAttributeDtoFactory;
use Elasticr\ServiceBus\ServiceBus\Model\ProductAttributeDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductAttributeDtoFactoryTest extends KernelTestCase
{
    private ProductAttributeDtoFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->getContainer()->get(ProductAttributeDtoFactory::class);
    }

    /**
     * @test
     */
    public function it_creates_attribute_dto_from_data(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../../expectations/moneys5/product.json') ?: '', true, 512, JSON_THROW_ON_ERROR);
        $data = $data['Data'][0];

        $this->assertEquals(new ProductAttributeDto('Vyska', '20'), $this->factory->create('Vyska', $data));
        $this->assertNull($this->factory->create('non_existing_key', $data));
        $this->assertNull($this->factory->create('non_existing_key', []));
        $this->assertNull($this->factory->create('non_existing_key', ['non_existing_key' => []]));
        $this->assertNull($this->factory->create('non_existing_key', ['non_existing_key' => ['value' => false]]));
    }
}
