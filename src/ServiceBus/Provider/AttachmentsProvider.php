<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Provider;

use Elasticr\ServiceBus\ServiceBus\Contract\AttachmentsProviderContract;
use Elasticr\ServiceBus\ServiceBus\Model\AttachmentDto;

final class AttachmentsProvider
{
    /**
     * @var AttachmentsProviderContract[]
     */
    private array $attachmentsProviders;

    /**
     * @param AttachmentsProviderContract[] $attachmentsProviders
     */
    public function __construct(array $attachmentsProviders)
    {
        $this->attachmentsProviders = $attachmentsProviders;
    }

    /**
     * @return AttachmentDto[]
     */
    public function provide(object $object, int $customer): array
    {
        foreach ($this->attachmentsProviders as $attachmentsProvider) {
            if ($attachmentsProvider->supports($object, $customer)) {
                return $attachmentsProvider->provide($object);
            }
        }

        return [];
    }
}
