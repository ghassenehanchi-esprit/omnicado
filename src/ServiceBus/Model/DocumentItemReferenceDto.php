<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class DocumentItemReferenceDto
{
    public function __construct(
        private readonly DocumentReferenceDto $documentReference,
        private readonly string $itemId
    ) {
    }

    public function documentReference(): DocumentReferenceDto
    {
        return $this->documentReference;
    }

    public function itemId(): string
    {
        return $this->itemId;
    }
}
