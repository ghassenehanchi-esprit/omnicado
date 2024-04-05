<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\ServiceBus\Model\BatchIdentifierDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BatchIdentifierDtoTest extends KernelTestCase
{
    private BatchIdentifierDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new BatchIdentifierDto('code', Chronos::createFromFormat('Y-m-d', '2022-10-03'));
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame('code', $this->dto->code());
        $this->assertEquals(Chronos::createFromFormat('Y-m-d', '2022-10-03'), $this->dto->expiration());
    }
}
