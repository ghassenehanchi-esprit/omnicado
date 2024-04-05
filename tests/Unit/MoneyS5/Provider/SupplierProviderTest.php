<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Provider;

use Elasticr\ServiceBus\MoneyS5\Api\CompaniesApiService;
use Elasticr\ServiceBus\MoneyS5\Provider\SupplierProvider;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5Config;
use Elasticr\ServiceBus\ServiceBus\Model\SupplierDto;
use Elasticr\Support\Collection\ImmutableCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class SupplierProviderTest extends KernelTestCase
{
    private MockObject $companiesApiService;

    private SupplierProvider $provider;

    private MoneyS5Config $moneyS5Config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyS5Config =
            TestingMoneyS5ConfigFactory::create(
                [
                    'receivedOrderTransferConfig' => [
                        'supplierCode' => 'ADR00350',
                    ],
                ]
            );

        $this->companiesApiService = $this->createMock(CompaniesApiService::class);

        $this->provider = new SupplierProvider(
            $this->companiesApiService
        );
    }

    /**
     * @test
     */
    public function provide_supplier_data(): void
    {
        $this->companiesApiService
            ->expects($this->exactly(1))
            ->method('getCompanies')
            ->with(
                $this->equalTo('ADR00350'),
                $this->equalTo($this->moneyS5Config)
            )
            ->willReturn(
                $this->expectedSupplier()
            );

        $this->assertEquals(new SupplierDto('05c9f18b-aa3a-40ff-b260-743db6a192fb', 'ADR00350'), $this->provider->provide($this->moneyS5Config)->first());
    }

    /**
     * @return ImmutableCollection<int, SupplierDto>
     */
    private function expectedSupplier(): ImmutableCollection
    {
        return new ImmutableCollection([new SupplierDto('05c9f18b-aa3a-40ff-b260-743db6a192fb', 'ADR00350')]);
    }
}
