<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;

final class OrderNoteDtoFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function createFromApiResponse(array $data): OrderNoteDto
    {
        return new OrderNoteDto($data['customerRemark'] ?? '');
    }
}
