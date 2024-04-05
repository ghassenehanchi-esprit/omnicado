<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Repository;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;

final class CustomerRepository
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

    public function add(Customer $customer): void
    {
        try {
            $this->entityManager->persist($customer);
            $this->entityManager->flush($customer);
        } catch (ORMException $exception) {
            $this->registry->resetManager();

            throw $exception;
        }
    }

    public function findById(int $id): ?Customer
    {
        $builder = $this->createBuilder();

        return $builder->select('c')
            ->from(Customer::class, 'c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCode(string $code): ?Customer
    {
        $builder = $this->createBuilder();

        return $builder->select('c')
            ->from(Customer::class, 'c')
            ->andWhere('c.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder();
    }
}
