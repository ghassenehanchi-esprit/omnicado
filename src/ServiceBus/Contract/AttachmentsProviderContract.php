<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Contract;

use Elasticr\ServiceBus\ServiceBus\Model\AttachmentDto;

interface AttachmentsProviderContract
{
    /**
     * @return AttachmentDto[]
     */
    public function provide(object $object): array;

    public function supports(object $object, int $customerId): bool;
}
