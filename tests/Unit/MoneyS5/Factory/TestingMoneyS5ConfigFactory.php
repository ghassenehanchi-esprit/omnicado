<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory;

use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5IssuedDeliveryNoteTransferConfig;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5IssuedInvoiceTransferConfig;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5ReceivedOrderTransferConfig;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5StockDocumentReturnTransferConfig;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5StockDocumentTransferConfig;

final class TestingMoneyS5ConfigFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data = []): MoneyS5Config
    {
        return new MoneyS5Config(
            $data['url'] ?? '',
            $data['grantType'] ?? '',
            $data['clientId'] ?? '',
            $data['clientSecret'] ?? '',
            $data['scope'] ?? '',
            $data['redirectUri'] ?? '',
            $data['articleParameters'] ?? [],
            $data['articleAttributes'] ?? [],
            new MoneyS5ReceivedOrderTransferConfig($data['receivedOrderTransferConfig']['supplierCode'] ?? '', $data['receivedOrderTransferConfig']['groupId'] ?? ''),
            new MoneyS5StockDocumentTransferConfig(
                $data['stockDocumentTransferConfig']['warehouseCodesForPutAway'] ?? [],
                $data['stockDocumentTransferConfig']['warehouseCodesForReceipt'] ?? [],
                $data['stockDocumentTransferConfig']['allowedDocumentSeriesForPutAway'] ?? [],
                $data['stockDocumentTransferConfig']['allowedDocumentSeriesForReceipt'] ?? [],
                $data['stockDocumentTransferConfig']['referenceForTransmission'] ?? '',
                $data['stockDocumentTransferConfig']['nameForSample'] ?? '',
            ),
            new MoneyS5IssuedInvoiceTransferConfig(
                $data['issuedInvoiceTransferConfig']['namesForCreditNote'] ?? [],
                $data['issuedInvoiceTransferConfig']['warehouseCodesForCreditNote'] ?? [],
            ),
            new MoneyS5StockDocumentReturnTransferConfig(
                $data['stockDocumentReturnTransferConfig']['warehouseCodesForPutAway'] ?? [],
                $data['stockDocumentReturnTransferConfig']['warehouseCodesForReceipt'] ?? [],
                $data['stockDocumentReturnTransferConfig']['allowedDocumentSeriesForPutAway'] ?? [],
                $data['stockDocumentReturnTransferConfig']['allowedDocumentSeriesForReceipt'] ?? [],
                $data['stockDocumentReturnTransferConfig']['referenceForTransmission'] ?? '',
                $data['stockDocumentReturnTransferConfig']['nameForReturn'] ?? '',
            ),
            new MoneyS5IssuedDeliveryNoteTransferConfig(
                $data['issuedDeliveryNoteTransferConfig']['forbiddenSuppliers'] ?? []
            )
        );
    }
}
