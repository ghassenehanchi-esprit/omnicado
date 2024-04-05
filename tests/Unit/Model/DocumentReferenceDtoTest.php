<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Model;

use Elasticr\ServiceBus\ServiceBus\Model\DocumentReferenceDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DocumentReferenceDtoTest extends KernelTestCase
{
    private DocumentReferenceDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new DocumentReferenceDto('type', 'id', 'number');
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame('type', $this->dto->documentType());
        $this->assertSame('id', $this->dto->id());
        $this->assertSame('number', $this->dto->number());
    }
}
