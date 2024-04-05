<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Api\ReceivedDeliveryNoteApiService;
use Elasticr\ServiceBus\MoneyS5\Factory\ReceivedDeliveryNoteDataFromIssuedOrderDataFactory;
use Elasticr\ServiceBus\MoneyS5\Service\ReceivedDeliveryNotesService;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5RecievedDeliveryNoteFilters;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentItemDto;
use Elasticr\ServiceBus\ServiceBus\Service\DataTransferLogService;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class RecievedDeliveryNotesServiceTest extends KernelTestCase
{
    private ReceivedDeliveryNotesService $service;

    private MockObject $receivedDeliveryNoteDataFromIssuedOrderDataFactoryMock;

    private MockObject $receivedDeliveryNoteApiServiceMock;

    private MoneyS5RecievedDeliveryNoteFilters $moneyS5RecievedDeliveryNoteFilters;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config = TestingMoneyS5ConfigFactory::create([]);

        $this->receivedDeliveryNoteDataFromIssuedOrderDataFactoryMock = $this->createMock(ReceivedDeliveryNoteDataFromIssuedOrderDataFactory::class);

        $this->receivedDeliveryNoteApiServiceMock = $this->createMock(ReceivedDeliveryNoteApiService::class);
        $this->moneyS5RecievedDeliveryNoteFilters = new MoneyS5RecievedDeliveryNoteFilters(
            'NUMBER123456'
        );

        $this->service = new ReceivedDeliveryNotesService(
            $this->receivedDeliveryNoteDataFromIssuedOrderDataFactoryMock,
            $this->receivedDeliveryNoteApiServiceMock,
            $this->createMock(DataTransferLogService::class),
        );
    }

    /**
     * @test
     **/
    public function create_received_delivery_note_if_not_exists(): void
    {
        $this->receivedDeliveryNoteDataFromIssuedOrderDataFactoryMock
            ->expects($this->exactly(1))
            ->method('create')
            ->with(
                $this->equalTo($this->getDocumentDto()),
                $this->equalTo($this->expectedArray()),
            )
            ->willReturn(
                $this->expectedArray()
            );

        $this->receivedDeliveryNoteApiServiceMock
            ->expects($this->exactly(1))
            ->method('getReceivedDeliveryNote')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->moneyS5RecievedDeliveryNoteFilters),
            )
            ->willReturn(
                []
            );

        $this->receivedDeliveryNoteApiServiceMock
            ->expects($this->exactly(1))
            ->method('createReceivedDeliveryNote')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->expectedArray()),
            );

        $this->service->createReceivedDeliveryNote($this->moneyS5Config, $this->getDocumentDto(), $this->expectedArray());
    }

    /**
     * @test
     **/
    public function create_received_delivery_note_if_exists(): void
    {
        $this->receivedDeliveryNoteDataFromIssuedOrderDataFactoryMock
            ->expects($this->exactly(1))
            ->method('create')
            ->with(
                $this->equalTo($this->getDocumentDto()),
                $this->equalTo($this->expectedArray()),
            )
            ->willReturn(
                $this->expectedArray()
            );

        $this->receivedDeliveryNoteApiServiceMock
            ->expects($this->exactly(1))
            ->method('getReceivedDeliveryNote')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->moneyS5RecievedDeliveryNoteFilters),
            )
            ->willReturn(
                $this->expectedArray()
            );

        $this->receivedDeliveryNoteApiServiceMock
            ->expects($this->exactly(0))
            ->method('createReceivedDeliveryNote')
            ->with(
                $this->equalTo($this->moneyS5Config),
                $this->equalTo($this->expectedArray()),
            );

        $this->service->createReceivedDeliveryNote($this->moneyS5Config, $this->getDocumentDto(), $this->expectedArray());
    }

    private function getDocumentDto(): DocumentDto
    {
        $documentItemsDto = [];

        $items = [
            'HLCRIO173' => 28,
            'HLCRIO172' => 10,
            'HLCRIO161' => 10,
            'HLCRIO157' => 10,
            'HLCRIO171AM' => 10,
        ];

        foreach ($items as $sku => $quantity) {
            $documentItemsDto[] = $this->createDocumentItemDto($sku, (float) $quantity);
        }

        return new DocumentDto('1', 'NUMBER123456', null, null, null, null, null, $documentItemsDto, Chronos::createFromFormat(
            'Y-m-d H:i:s',
            '2022-12-12 00:00:00'
        ), null, 'supplierCode');
    }

    private function createDocumentItemDto(string $sku, float $quantity): DocumentItemDto
    {
        return new DocumentItemDto('1', $sku, 'name', $quantity, 'ks', null, null, 0);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expectedArray(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../../expectations/moneys5/issued_orders.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
}
