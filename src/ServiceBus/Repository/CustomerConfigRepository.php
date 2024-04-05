<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Repository;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Elasticr\ServiceBus\ServiceBus\Entity\CustomerConfig;

final class CustomerConfigRepository
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

    public function add(CustomerConfig $customerConfig): void
    {
        try {
            $this->entityManager->persist($customerConfig);
            $this->entityManager->flush($customerConfig);
        } catch (ORMException $exception) {
            $this->registry->resetManager();

            throw $exception;
        }
    }

    public function findByCustomerId(int $customerId): ?CustomerConfig
    {
        $builder = $this->createBuilder();

        return $builder->select('cc')
            ->from(CustomerConfig::class, 'cc')
            ->andWhere('cc.customerId = :customerId')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder();
    }
}
