# Elasticr Service Bus

## Installation

```bash
composer require elasticr/service-bus
```

## Configuration

Register bundle in `config/bundles.php`

```php
\Elasticr\ServiceBus\Symfony\ElasticrServiceBusBundle::class => ['all' => true]
```

Add following in config file
```yaml
imports:
  - { resource: '@ElasticrServiceBusBundle/config/config.yaml' }
```

Register routes
```yaml
elasticr_eesb:
  resource: '@ElasticrServiceBusBundle/config/routing.yaml'
```

Add following environment variables to `.env` file
```dotenv
SHOPTET_ACCESS_TOKEN_SERVER=""
```

## CLI Commands

### Shoptet Webhooks

#### Register new webhook
```bash
 bin/console eesb:shoptet:new-webhook <eshopID> <eventName> <URL>
 ```

#### List registered webhooks
```bash
 bin/console eesb:shoptet:list-webhooks <eshopID>
```

#### List registered webhooks
```bash
 bin/console eesb:shoptet:delete-webhook <eshopID> <webhookID>
```

## Flexibee


**Příkazy**
```bash
eesb:flexibee:orders:transfer-new - přenos nových objednávek z Abry do Esa    
eesb:flexibee:products:transfer - přenos seznamu produktů z Abry do Esa
eesb:flexibee:sell-orders:transfer - přenos vydaných objednávek(avíz) z Abry do Esa
eesb:flexibee:sell-orders:update-status - aktualizace stavu vydaných objednávek(avíz) z Esa do Abry
```


**Konfigurace**
```json
{
  "type":"flexibee_config",
  "data":{
    "name":"flexibee",
    "apiUrl":"<URL>",
    "authToken":"<AUTH_TOKEN>",
    "productGroupsToSync":[
      "code:ZBOZI"
    ],
    "sellOrdersConfig":{
      "sellOrdersFilter":{
        "labelCodes":[
          "K-PRIJMU"
        ]
      },
      "orderTransferedLabelCode":"STAZENO-DO-ESA",
      "orderReceivedLabelCode":"PRIJATO-NA-SKLAD"
    },
    "purchaseOrdersConfig":{
      "ordersListFilter":{
        "labelCodes":[
          "TSE"
        ]
      },
      "orderTransferedLabelCode":"TSS",
      "orderTransferFailedLabelCode":"TSC"
    },
    "stocksMapping":{
      "05":{
        "stock":"SKLAD-HANDLING",
        "incomeDocumentTypeId":74,
        "outcomeDocumentTypeId":0,
        "transferDocumentTypeId":2
      }
    }
  }
}
```