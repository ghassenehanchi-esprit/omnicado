<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Repository;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Elasticr\ServiceBus\Shoptet\Entity\OrdersSyncStatus;

final class SyncStatusRepository
{
    private EntityManager $entityManager;

    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        if (!($entityManager instanceof EntityManager)) {
            throw new InvalidArgumentException('Unsupported EntityManager class');
        }

        $this->registry = $registry;
        $this->entityManager = $entityManager;
    }

    public function save(OrdersSyncStatus $syncStatus): void
    {
        try {
            $this->entityManager->persist($syncStatus);
            $this->entityManager->flush($syncStatus);
        } catch (ORMException $exception) {
            $this->registry->resetManager();

            throw $exception;
        }
    }

    public function findByCustomerId(int $customerId): ?OrdersSyncStatus
    {
        $builder = $this->createBuilder();

        return $builder->select('a')
            ->from(OrdersSyncStatus::class, 'a')
            ->andWhere('a.customerId = :customerId')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder();
    }
}
