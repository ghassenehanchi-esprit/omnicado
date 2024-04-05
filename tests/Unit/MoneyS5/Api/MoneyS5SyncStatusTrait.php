<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Api;

use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\Entity\MoneyS5SyncStatus;

trait MoneyS5SyncStatusTrait
{
    private function getSyncStatus(): MoneyS5SyncStatus
    {
        $moneyS5SyncStatus = new MoneyS5SyncStatus(
            1,
        );

        $now = DateHelper::convertStringToChronos('2023-09-01T00:00:01');

        $moneyS5SyncStatus->updateLastProcessedArticleTime($now);
        $moneyS5SyncStatus->updateLastProcessedIssuedDeliveryNoteTime($now);
        $moneyS5SyncStatus->updateLastProcessedStockDocumentTime($now);
        $moneyS5SyncStatus->updateLastProcessedStockDocumentSampleTime($now);

        $moneyS5SyncStatus->updatelastProcessedIssuedInvoiceTime($now);

        $moneyS5SyncStatus->updatelastProcessedStockDocumentReturnTime($now);

        return $moneyS5SyncStatus;
    }
}
