<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Client\ValueObject\Eso9ProcedureResponseData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9ProcedureResponseDataTest extends KernelTestCase
{
    private Eso9ProcedureResponseData $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->response = new Eso9ProcedureResponseData('response-data');
    }

    /**
     * @test
     */
    public function data(): void
    {
        $this->assertSame('response-data', $this->response->data());
    }
}
