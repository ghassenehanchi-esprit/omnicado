<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureParameter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9ProcedureParameterTest extends KernelTestCase
{
    private Eso9ProcedureParameter $procedureParameter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->procedureParameter = new Eso9ProcedureParameter('@procedureParameterName', 'procedure-parameter-value', 1, 2);
    }

    /**
     * @test
     */
    public function name(): void
    {
        $this->assertSame('@procedureParameterName', $this->procedureParameter->name());
    }

    /**
     * @test
     */
    public function value(): void
    {
        $this->assertSame('procedure-parameter-value', $this->procedureParameter->value());
    }

    /**
     * @test
     */
    public function php_type(): void
    {
        $this->assertSame(1, $this->procedureParameter->phpType());
    }

    /**
     * @test
     */
    public function sql_type(): void
    {
        $this->assertSame(2, $this->procedureParameter->sqlType());
    }

    /**
     * @test
     */
    public function alias(): void
    {
        $this->assertSame('procedureParameterName', $this->procedureParameter->alias());
    }
}
