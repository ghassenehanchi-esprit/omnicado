<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class OrderNoteDto
{
    private string $note;

    public function __construct(string $note)
    {
        $this->note = $note;
    }

    public function note(): string
    {
        return $this->note;
    }
}
