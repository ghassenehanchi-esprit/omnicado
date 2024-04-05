<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Elasticr\ServiceBus\ServiceBus\Model\BatchIdentifierDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\ProductConfigurationReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DocumentItemDtoTest extends KernelTestCase
{
    private DocumentItemDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new DocumentItemDto('1', 'sku', 'name', 100.0, 'ks', new VatRateAwareValueDto(1000.0, 1210.0, 21.0), '1', 10.0, new DocumentItemReferenceDto(
            new DocumentReferenceDto(
                'type',
                'id'
            ),
            'itemId'
        ), [
            'sscc-1',
        ], 10, 'stockItemId', new BatchIdentifierDto(
            'code',
            null
        ), [
            'serial_number_1',
            'serial_number_2',
            'serial_number_3',
        ], new ProductConfigurationReferenceDto('configurationName', 'configurationId'), ['coverPositionData' => []]);
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame('1', $this->dto->id());
        $this->assertSame('sku', $this->dto->sku());
        $this->assertSame('name', $this->dto->name());
        $this->assertSame(100.0, $this->dto->quantity());
        $this->assertSame('ks', $this->dto->unit());
        $this->assertEquals(new VatRateAwareValueDto(1000.0, 1210.0, 21.0), $this->dto->price());
        $this->assertSame('1', $this->dto->supplier());
        $this->assertSame(10.0, $this->dto->weight());
        $this->assertSame(10, $this->dto->rowNumber());
        $this->assertSame('stockItemId', $this->dto->stockItemId());
        $this->assertEquals(new BatchIdentifierDto('code', null), $this->dto->batchIdentifier());
        $this->assertSame(['serial_number_1', 'serial_number_2', 'serial_number_3'], $this->dto->serialNumbers());
        $this->assertEquals(new ProductConfigurationReferenceDto('configurationName', 'configurationId'), $this->dto->productConfigurationReferenceDto());
        $this->assertSame(['coverPositionData' => []], $this->dto->coverPositionData());
        $this->assertEquals(new DocumentItemReferenceDto(new DocumentReferenceDto('type', 'id'), 'itemId'), $this->dto->originItem());
        $this->assertSame(['sscc-1'], $this->dto->serialShippingContainerCodes());
    }
}
