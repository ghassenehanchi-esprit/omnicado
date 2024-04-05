<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemReferenceDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentReferenceDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DocumentItemReferenceDtoTest extends KernelTestCase
{
    private DocumentItemReferenceDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new DocumentItemReferenceDto(new DocumentReferenceDto('type', 'id', 'number'), 'itemId');
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertEquals(new DocumentReferenceDto('type', 'id', 'number'), $this->dto->documentReference());
        $this->assertSame('itemId', $this->dto->itemId());
    }
}
