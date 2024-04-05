<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\ServiceBus;

use Elasticr\ServiceBus\ServiceBus\Constant\AttachmentTypes;
use Elasticr\ServiceBus\ServiceBus\Model\AttachmentDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AttachmentDtoTest extends KernelTestCase
{
    private AttachmentDto $attachmentDto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attachmentDto = new AttachmentDto('test-file-name', AttachmentTypes::PRINTABLE_DOCUMENT, 'ASDMNW848171AJ23!$');
    }

    /**
     * @test
     **/
    public function constructor(): void
    {
        $this->assertSame('test-file-name', $this->attachmentDto->name);
        $this->assertSame('PRINTABLE_DOCUMENT', $this->attachmentDto->type->value);
        $this->assertSame('ASDMNW848171AJ23!$', $this->attachmentDto->data);
    }
}
