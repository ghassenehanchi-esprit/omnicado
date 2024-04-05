<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Model;

use Cake\Chronos\Chronos;

final class Webhook
{
    private int $id;

    private string $event;

    private string $url;

    private Chronos $created;

    public function __construct(int $id, string $event, string $url, Chronos $created)
    {
        $this->id = $id;
        $this->event = $event;
        $this->url = $url;
        $this->created = $created;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function event(): string
    {
        return $this->event;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function created(): Chronos
    {
        return $this->created;
    }
}
