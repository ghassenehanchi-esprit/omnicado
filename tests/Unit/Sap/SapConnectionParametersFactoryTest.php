<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Sap;

use Elasticr\ServiceBus\Sap\Factory\SapConnectionParametersFactory;
use Elasticr\ServiceBus\Sap\ValueObject\SapConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SapConnectionParametersFactoryTest extends KernelTestCase
{
    private SapConnectionParametersFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var SapConnectionParametersFactory $factory */

        $factory = self::getContainer()->get(SapConnectionParametersFactory::class);

        $this->factory = $factory;
    }

    /**
     * @test
     */
    public function it_creates_sap_connection_parameters(): void
    {
        $connectionParamsData = [
            'host' => 'testing_host',
            'sysnr' => 'testing_sysnr',
            'client' => 'testing_client',
            'user' => 'testing_user',
            'password' => 'testing_password',
            'sapRouter' => 'testing_sap_router',
        ];

        $connectionParameters = $this->factory->create(new SapConfig($connectionParamsData));

        $this->assertSame([
            'ashost' => 'testing_host',
            'sysnr' => 'testing_sysnr',
            'client' => 'testing_client',
            'user' => 'testing_user',
            'passwd' => 'testing_password',
            'saprouter' => 'testing_sap_router',
        ], $connectionParameters->toArray());
    }
}
