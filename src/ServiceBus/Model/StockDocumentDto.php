<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Cake\Chronos\Chronos;

final class StockDocumentDto
{
    private string $id;

    private string $type;

    private string $stock;

    private ?string $order;

    private Chronos $dtClosed;

    /**
     * @var StockItemDto[]
     */
    private array $items;

    private ?StockDocumentDto $relatedDoc;

    /**
     * @param StockItemDto[] $items
     */
    public function __construct(string $id, string $type, string $stock, ?string $order, array $items, Chronos $dtClosed, ?self $relatedDoc)
    {
        $this->id = $id;
        $this->type = $type;
        $this->stock = $stock;
        $this->order = $order;
        $this->items = $items;
        $this->dtClosed = $dtClosed;
        $this->relatedDoc = $relatedDoc;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function stock(): string
    {
        return $this->stock;
    }

    public function order(): ?string
    {
        return $this->order;
    }

    /**
     * @return StockItemDto[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function dtClosed(): Chronos
    {
        return $this->dtClosed;
    }

    public function relatedDoc(): ?self
    {
        return $this->relatedDoc;
    }
}
