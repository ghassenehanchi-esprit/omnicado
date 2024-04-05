<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Elasticr\ServiceBus\MoneyS5\Api\MoneyS5OrdersApiService;
use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\Entity\MoneyS5SyncStatus;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5OrdersFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\OrderDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\MoneyS5\Service\MoneyS5OrdersService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\ServiceBus\Command\CreateOrderCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\Service\DataTransferLogService;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class MoneyS5OrdersFacadeTest extends KernelTestCase
{
    private MoneyS5OrdersFacade $facade;

    private MockObject $ordersApiServiceMock;

    private MoneyS5OrdersService $ordersService;

    private MockObject $serviceBusMock;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config =
        TestingMoneyS5ConfigFactory::create(
            [
                'issuedDeliveryNoteTransferConfig' => [
                    'forbiddenSuppliers' => ['Levenhuk', 'Bresser', 'Meade', 'Magus'],
                ],
            ]
        );

        $customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $customerRepositoryMock->method('findByCode')->with('123456')->willReturn(new Customer('Testing customer', '123456'));
        $customerProvider = new CustomerProvider($customerRepositoryMock);

        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $customerConfigFinderMock->method('find')->willReturn($this->moneyS5Config);

        $moneyS5SyncStatus = new MoneyS5SyncStatus(0);
        $moneyS5SyncStatus->updateLastProcessedIssuedDeliveryNoteTime(DateHelper::convertStringToChronos('2023-12-12T06:15:00'));
        $moneyS5SyncStatusRepositoryMock = $this->createMock(MoneyS5SyncStatusRepository::class);
        $moneyS5SyncStatusRepositoryMock->method('findByCustomerId')->willReturn($moneyS5SyncStatus);

        $this->ordersApiServiceMock = $this->createMock(MoneyS5OrdersApiService::class);

        $this->ordersService = self::getContainer()->get(MoneyS5OrdersService::class);

        /** @var OrderDtoFactory $orderFactory */
        $orderFactory = self::getContainer()->get(OrderDtoFactory::class);

        $this->serviceBusMock = $this->createMock(ServiceBus::class);

        $this->facade = new MoneyS5OrdersFacade(
            $customerProvider,
            $customerConfigFinderMock,
            $moneyS5SyncStatusRepositoryMock,
            $this->ordersApiServiceMock,
            $this->ordersService,
            $this->serviceBusMock,
            $orderFactory,
            $this->createMock(DataTransferLogService::class),
        );
    }

    /**
     * @test
     **/
    public function transfer_orders(): void
    {
        $this->ordersApiServiceMock
            ->expects($this->exactly(1))
            ->method('getOrders')
            ->willReturn($this->expectedIssuedDeliveryNotes());

        $counter = $this->exactly(2);
        $this->serviceBusMock
            ->expects($counter)
            ->method('dispatch')
            ->with(
                $this->callback(
                    fn ($command): bool =>
                        $command instanceof CreateOrderCommand
                        &&
                        match ($counter->getInvocationCount()) {
                            1 => $command->order()->number() === 'DV2317679',
                            2 => $command->order()->number() === 'DV2317677',
                            default => false
                        }
                )
            );

        $this->facade->transferOrders('123456');

        $this->assertEquals(
            $this->expectedIssuedDeliveryNotesValid(),
            $this->ordersService->getFilteredOrders(
                $this->expectedIssuedDeliveryNotes(),
                $this->moneyS5Config->issuedDeliveryNoteTransferConfig()->forbiddenSuppliers()
            )
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedIssuedDeliveryNotes(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/issued_delivery_note_with_forbidden_suppliers.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedIssuedDeliveryNotesValid(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/issued_delivery_note_valid.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
