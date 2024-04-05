<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Elasticr\ServiceBus\ServiceBus\Model\ProductConfigurationReferenceDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductConfigurationReferenceDtoTest extends KernelTestCase
{
    private ProductConfigurationReferenceDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new ProductConfigurationReferenceDto('configurationName', 'configurationId');
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame('configurationName', $this->dto->configuratorName());
        $this->assertSame('configurationId', $this->dto->configurationId());
    }
}
