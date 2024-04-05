<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Controller;

use Elasticr\ServiceBus\Shoptet\Api\OrdersApiService;
use Elasticr\ServiceBus\Shoptet\Exception\ShoptetApiException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NewOrderController extends AbstractController
{
    private LoggerInterface $logger;

    private OrdersApiService $ordersApiService;

    public function __construct(OrdersApiService $ordersApiService, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->ordersApiService = $ordersApiService;
    }

    public function create(Request $request): Response
    {
        /** @var string $content */
        $content = $request->getContent();
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        try {
            $orderDetail = $this->ordersApiService->getOrderDetail((int) $data['eshopId'], $data['eventInstance']);
            $this->logger->info('Shoptet order', ['order' => $orderDetail]);
        } catch (ShoptetApiException | Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            return new Response('', 500);
        }

        return new Response('', 200);
    }
}
