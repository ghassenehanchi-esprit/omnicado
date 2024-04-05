<?php

declare(strict_types=1);

namespace Unit\Shoptet\Api;

use Elasticr\ServiceBus\Shoptet\Api\OrdersApiService;
use Elasticr\ServiceBus\Shoptet\Api\ShoptetApi;
use Elasticr\ServiceBus\Shoptet\Factory\ShoptetOrderDtoFactory;
use Elasticr\ServiceBus\Shoptet\Model\OrderStatusDto;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderStatusTransformer;
use Elasticr\ServiceBus\Shoptet\Transforming\OrderTrackingInfoTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class OrdersApiServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function list_of_order_statuses(): void
    {
        $shoptetApiMock = $this->createMock(ShoptetApi::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $orderDtoFactory = self::getContainer()->get(ShoptetOrderDtoFactory::class);
        $orderTrackingInfoTransformer = new OrderTrackingInfoTransformer();
        $orderStatusTransformer = new OrderStatusTransformer();

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([
            'data' => [
                'statuses' => [
                    ['id' => 1,
                        'name' => 'Objednáno',
                        'markAsPaid' => false,
                    ],
                    ['id' => 2,
                        'name' => 'Vyřizuje se',
                        'markAsPaid' => false,
                    ],
                    ['id' => 3,
                        'name' => 'Vyřízeno',
                        'markAsPaid' => true,
                    ],
                ],
            ],
        ]);
        $responseMock->method('getStatusCode')->willReturn(200);

        $service = new OrdersApiService($shoptetApiMock, $httpClientMock, $orderDtoFactory, $orderTrackingInfoTransformer, $orderStatusTransformer);

        $httpClientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $expectedStatuses = [];
        $expectedStatuses[] = new OrderStatusDto(1, 1, 'Objednáno', false);
        $expectedStatuses[] = new OrderStatusDto(2, 1, 'Vyřizuje se', false);
        $expectedStatuses[] = new OrderStatusDto(3, 1, 'Vyřízeno', true);

        $statuses = $service->listOfOrderStatuses(1);
        $this->assertEquals($expectedStatuses, $statuses);
    }
}
