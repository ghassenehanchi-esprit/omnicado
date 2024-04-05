<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\ValueObject;

use Elasticr\ServiceBus\ServiceBus\Contract\ConfigContract;
use Elasticr\Support\Doctrine\Contract\DoctrineSerializableContract;

final class ShoptetConfig implements ConfigContract, DoctrineSerializableContract
{
    /**
     * @var string
     */
    public const NAME = 'shoptet';

    /**
     * @var string
     */
    public const TYPE_NAME = 'shoptet_config';

    private int $eshopId;

    private int $transferredOrderStatus;

    private int $errorOrderStatus;

    private string $paidStatusName;

    public function __construct(int $eshopId, int $transferredOrderStatus, int $errorOrderStatus, string $paidStatusName)
    {
        $this->eshopId = $eshopId;
        $this->transferredOrderStatus = $transferredOrderStatus;
        $this->errorOrderStatus = $errorOrderStatus;
        $this->paidStatusName = $paidStatusName;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function title(): string
    {
        return 'Shoptet';
    }

    public function eshopId(): int
    {
        return $this->eshopId;
    }

    public function transferredOrderStatus(): int
    {
        return $this->transferredOrderStatus;
    }

    public function paidStatusName(): string
    {
        return $this->paidStatusName;
    }

    public function errorOrderStatus(): int
    {
        return $this->errorOrderStatus;
    }

    public function typeName(): string
    {
        return self::TYPE_NAME;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'eshopId' => $this->eshopId(),
            'transferredOrderStatus' => $this->transferredOrderStatus(),
            'errorOrderStatus' => $this->errorOrderStatus(),
            'paidStatusName' => $this->paidStatusName(),
        ];
    }
}
