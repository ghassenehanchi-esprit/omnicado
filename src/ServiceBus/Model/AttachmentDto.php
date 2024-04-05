<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Elasticr\ServiceBus\ServiceBus\Constant\AttachmentTypes;

final class AttachmentDto
{
    public function __construct(
        public readonly string $name,
        public readonly AttachmentTypes $type,
        public readonly string $data
    ) {
    }
}
