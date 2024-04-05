<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Repository;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Elasticr\ServiceBus\ServiceBus\Entity\OrderTransferRule;

final class OrderTransferRuleRepository
{
    private EntityManager $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        if (!($entityManager instanceof EntityManager)) {
            throw new InvalidArgumentException('Unsupported EntityManager class');
        }

        $this->entityManager = $entityManager;
    }

    /**
     * @return OrderTransferRule[]
     */
    public function rulesByCustomerId(int $customerId): array
    {
        $builder = $this->createBuilder();

        $data = $builder->select('t')
            ->from(OrderTransferRule::class, 't')
            ->andWhere('t.customerId = :customerId')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->getArrayResult();

        $rules = [];

        foreach ($data as $rulesData) {
            $rules[] = new OrderTransferRule($rulesData['customerId'], $rulesData['status'], $rulesData['payment'], $rulesData['paymentStatus']);
        }

        return $rules;
    }

    private function createBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder();
    }
}
