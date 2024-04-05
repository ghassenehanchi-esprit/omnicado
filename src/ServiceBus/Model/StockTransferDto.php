<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Cake\Chronos\Chronos;

final class StockTransferDto
{
    private string $id;

    private string $number;

    private ?string $state;

    private Chronos $createdAt;

    private string $note;

    /**
     * @var AttachmentDto[]
     */
    private array $attachments;

    private ?DocumentReferenceDto $origin;

    private DocumentDto $putAway;

    /**
     * @var DocumentDto[]
     */
    private array $receipts;

    /**
     * @param DocumentDto[] $receipts
     * @param AttachmentDto[] $attachments
     */
    public function __construct(
        string $id,
        string $number,
        DocumentDto $putAway,
        array $receipts,
        Chronos $createdAt,
        ?DocumentReferenceDto $origin = null,
        string $note = '',
        ?string $state = null,
        array $attachments = [],
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->putAway = $putAway;
        $this->receipts = $receipts;
        $this->state = $state;
        $this->createdAt = $createdAt;
        $this->note = $note;
        $this->attachments = $attachments;
        $this->origin = $origin;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function putAway(): DocumentDto
    {
        return $this->putAway;
    }

    /**
     * @return DocumentDto[]
     */
    public function receipts(): array
    {
        return $this->receipts;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function createdAt(): Chronos
    {
        return $this->createdAt;
    }

    public function note(): string
    {
        return $this->note;
    }

    /**
     * @return AttachmentDto[]
     */
    public function attachments(): array
    {
        return $this->attachments;
    }

    public function addAttachment(AttachmentDto $attachmentDto): void
    {
        $this->attachments[] = $attachmentDto;
    }

    public function origin(): ?DocumentReferenceDto
    {
        return $this->origin;
    }
}
