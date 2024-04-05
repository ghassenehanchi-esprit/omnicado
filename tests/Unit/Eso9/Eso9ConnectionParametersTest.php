<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\Eso9ConnectionParameters;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9ConnectionParametersTest extends KernelTestCase
{
    private Eso9ConnectionParameters $connectionParameters;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionParameters = new Eso9ConnectionParameters('host', 'database', 'user', 'password');
    }

    /**
     * @test
     */
    public function host(): void
    {
        $this->assertSame('host', $this->connectionParameters->host());
    }

    /**
     * @test
     */
    public function database_name(): void
    {
        $this->assertSame('database', $this->connectionParameters->databaseName());
    }

    /**
     * @test
     */
    public function user(): void
    {
        $this->assertSame('user', $this->connectionParameters->user());
    }

    /**
     * @test
     */
    public function password(): void
    {
        $this->assertSame('password', $this->connectionParameters->password());
    }
}
