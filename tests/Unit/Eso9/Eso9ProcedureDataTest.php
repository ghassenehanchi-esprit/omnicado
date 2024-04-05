<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9ProcedureDataTest extends KernelTestCase
{
    private Eso9ProcedureData $procedureData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->procedureData = new Eso9ProcedureData('post', 'testing-resource', ['parameter-1' => 'parameter-1-value'], 'Testing body', 'application/json', '1.0', 'test-api');
    }

    /**
     * @test
     */
    public function content_type(): void
    {
        $this->assertSame('application/json', $this->procedureData->contentType());
    }

    /**
     * @test
     */
    public function version(): void
    {
        $this->assertSame('1.0', $this->procedureData->version());
    }

    /**
     * @test
     */
    public function user(): void
    {
        $this->assertSame('test-api', $this->procedureData->user());
    }

    /**
     * @test
     */
    public function resource(): void
    {
        $this->assertSame('testing-resource', $this->procedureData->resource());
    }

    /**
     * @test
     */
    public function method(): void
    {
        $this->assertSame('post', $this->procedureData->method());
    }

    /**
     * @test
     */
    public function body(): void
    {
        $this->assertSame('Testing body', $this->procedureData->body());
    }

    /**
     * @test
     */
    public function parameters(): void
    {
        $this->assertSame(['parameter-1' => 'parameter-1-value'], $this->procedureData->parameters());
    }
}
