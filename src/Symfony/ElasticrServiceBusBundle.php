<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Symfony;

use Doctrine\DBAL\Types\Type;
use Elasticr\Support\Doctrine\Provider\DoctrineSerializableFactoryProvider;
use Elasticr\Support\Doctrine\Type\DoctrineSerializableCollectionType;
use Elasticr\Support\Doctrine\Type\DoctrineSerializableType;
use Exception;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function dirname;

final class ElasticrServiceBusBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function boot(): void
    {
        parent::boot();

        if (!Type::hasType(DoctrineSerializableType::TYPE_NAME)) {
            Type::addType(DoctrineSerializableType::TYPE_NAME, DoctrineSerializableType::class);
        }

        if ($this->container === null) {
            throw new Exception();
        }

        /** @var DoctrineSerializableFactoryProvider $doctrineSerializableFactoryProvider */
        $doctrineSerializableFactoryProvider = $this->container->get(DoctrineSerializableFactoryProvider::class);

        /** @var DoctrineSerializableType $doctrineSerializableType */
        $doctrineSerializableType = Type::getType(DoctrineSerializableType::TYPE_NAME);
        $doctrineSerializableType->setProvider($doctrineSerializableFactoryProvider);

        if (!Type::hasType(DoctrineSerializableCollectionType::TYPE_NAME)) {
            Type::addType(DoctrineSerializableCollectionType::TYPE_NAME, DoctrineSerializableCollectionType::class);
        }

        /** @var DoctrineSerializableCollectionType $doctrineSerializableType */
        $doctrineSerializableType = Type::getType(DoctrineSerializableCollectionType::TYPE_NAME);
        $doctrineSerializableType->setProvider($doctrineSerializableFactoryProvider);
    }
}
