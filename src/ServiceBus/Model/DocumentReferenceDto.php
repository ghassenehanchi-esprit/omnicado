<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class DocumentReferenceDto
{
    private string $documentNumber;

    public function __construct(
        private readonly string $documentType,
        private readonly string $documentId,
        string $documentNumber = ''
    ) {
        $this->documentNumber = $documentNumber;
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    public function id(): string
    {
        return $this->documentId;
    }

    public function number(): string
    {
        return $this->documentNumber ?? '';
    }
}
