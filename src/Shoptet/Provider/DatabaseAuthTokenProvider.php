<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\Persistence\ManagerRegistry;
use Elasticr\ServiceBus\Shoptet\Provider\Contract\AuthTokenProviderContract;

final class DatabaseAuthTokenProvider implements AuthTokenProviderContract
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getToken(int $eshopId): string
    {
        /** @var Connection $connection */
        $connection = $this->registry->getConnection();

        /** @var Statement $statement */
        $statement = $connection->prepare('SELECT * FROM addon_identity where eshop_id = :eshopId;');
        $statement->bindValue('eshopId', $eshopId);
        $result = $statement->executeQuery();

        $resultAsArray = $result->fetchAssociative();

        if ($resultAsArray === false) {
            return '';
        }

        return $resultAsArray['o_auth_token'];
    }
}
