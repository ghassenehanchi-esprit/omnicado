<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api;

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClient;
use Automattic\WooCommerce\HttpClient\Response;
use Elasticr\ServiceBus\WooCommerce\Api\ApiClientFactory;
use Elasticr\ServiceBus\WooCommerce\Api\PaymentGatewayApiService;
use Elasticr\ServiceBus\WooCommerce\Factory\PaymentGatewayDtoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaymentGatewayApiServiceTest extends KernelTestCase
{
    use WooCommerceConfigTrait;

    /**
     * @test
     */
    public function list_of_gateways(): void
    {
        $clientFactoryMock = $this->createMock(ApiClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $httpMock = $this->createMock(HttpClient::class);
        $responseMock = $this->createMock(Response::class);

        $productsApiData = json_decode(file_get_contents(__DIR__ . '/../../../expectations/woocommerce/list_gateways.json') ?: '', false, 512, JSON_THROW_ON_ERROR);

        $clientFactoryMock->method('getClient')->willReturn($clientMock);
        $clientMock->method('get')->willReturnOnConsecutiveCalls($productsApiData, []);
        $responseMock->method('getCode')->willReturn(200);
        $httpMock->method('getResponse')->willReturn($responseMock);
        $clientMock->http = $httpMock;

        /** @var PaymentGatewayDtoFactory $factory */
        $factory = self::getContainer()->get(PaymentGatewayDtoFactory::class);
        $gatewaysApiService = new PaymentGatewayApiService($clientFactoryMock, $factory);

        $gateways = $gatewaysApiService->getPaymentGateways($this->getSimpleConfig());

        $this->assertEquals(3, count($gateways));
    }
}
