<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Api\Filter;

use Cake\Chronos\Chronos;
use DateTimeInterface;

final class ShoptetOrdersListFilter
{
    private ?Chronos $creationTimeFrom;

    private ?Chronos $creationTimeTo;

    private ?int $statusId;

    public function __construct(Chronos $creationTimeFrom = null, Chronos $creationTimeTo = null, int $statusId = null)
    {
        $this->creationTimeFrom = $creationTimeFrom;
        $this->creationTimeTo = $creationTimeTo;
        $this->statusId = $statusId;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $array = [];
        if ($this->creationTimeFrom !== null) {
            $array['creationTimeFrom'] = $this->creationTimeFrom->format(DateTimeInterface::ISO8601);
        }
        if ($this->creationTimeTo !== null) {
            $array['creationTimeTo'] = $this->creationTimeTo->format(DateTimeInterface::ISO8601);
        }
        if ($this->statusId !== null) {
            $array['statusId'] = (string) $this->statusId;
        }

        return $array;
    }
}
