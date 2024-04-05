<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Elasticr\ServiceBus\ServiceBus\Model\ValueDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ValueDtoTest extends KernelTestCase
{
    private ValueDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new ValueDto(1000.0, 1210.0);
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame(1000.0, $this->dto->withoutVat());
        $this->assertSame(1210.0, $this->dto->withVat());
    }
}
