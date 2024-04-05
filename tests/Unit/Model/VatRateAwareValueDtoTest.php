<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Elasticr\ServiceBus\ServiceBus\Model\ValueDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class VatRateAwareValueDtoTest extends KernelTestCase
{
    private VatRateAwareValueDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new VatRateAwareValueDto(1000.0, 1210.0, 21.0);
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame(1000.0, $this->dto->withoutVat());
        $this->assertSame(1210.0, $this->dto->withVat());
        $this->assertSame(21.0, $this->dto->vatRate());
    }

    /**
     * @test
     **/
    public function value(): void
    {
        $this->assertEquals(new ValueDto(1000.0, 1210.0), $this->dto->value());
    }

    /**
     * @test
     **/
    public function vat(): void
    {
        $this->assertSame(210.0, $this->dto->vat());
    }
}
