<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Elasticr\ServiceBus\ServiceBus\Model\PriceDto;
use Elasticr\ServiceBus\ServiceBus\Model\ValueDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PriceDtoTest extends KernelTestCase
{
    private PriceDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new PriceDto(new ValueDto(1000.0, 1210.0), 'CZK');
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertEquals(new ValueDto(1000.0, 1210.0), $this->dto->value());
        $this->assertSame('CZK', $this->dto->currency());
    }
}
