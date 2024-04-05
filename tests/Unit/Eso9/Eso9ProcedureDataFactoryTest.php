<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\Eso9ProcedureDataFactory;
use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9ProcedureDataFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_resolve_request(): void
    {
        /** @var Eso9ProcedureDataFactory $factory */
        $factory = self::getContainer()->get(Eso9ProcedureDataFactory::class);

        $procedureData = $factory->create('testing-resource', 'post', 'Testing body', 'testing-user', ['parameter-1' => 'parameter-1-value']);

        $this->assertInstanceOf(Eso9ProcedureData::class, $procedureData);

        $this->assertSame('application/json', $procedureData->contentType());
        $this->assertSame('1.0', $procedureData->version());
        $this->assertSame('testing-user', $procedureData->user());
        $this->assertSame('testing-resource', $procedureData->resource());
        $this->assertSame('post', $procedureData->method());
        $this->assertSame('Testing body', $procedureData->body());
        $this->assertSame(['parameter-1' => 'parameter-1-value'], $procedureData->parameters());
    }
}
