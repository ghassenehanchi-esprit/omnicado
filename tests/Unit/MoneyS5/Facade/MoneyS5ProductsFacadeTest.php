<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Facade;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\MoneyS5\Api\ProductsApiService;
use Elasticr\ServiceBus\MoneyS5\Api\ProductsCategoryApiService;
use Elasticr\ServiceBus\MoneyS5\Api\ProductsGroupApiService;
use Elasticr\ServiceBus\MoneyS5\Entity\MoneyS5SyncStatus;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5ProductsFacade;
use Elasticr\ServiceBus\MoneyS5\Factory\ProductAttributeDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\ProductCategoryDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\ProductDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\ProductGroupDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Factory\ProductParameterDtoFactory;
use Elasticr\ServiceBus\MoneyS5\Generator\ProductCategoryPathGenerator;
use Elasticr\ServiceBus\MoneyS5\Repository\MoneyS5SyncStatusRepository;
use Elasticr\ServiceBus\ServiceBus\Command\CreateProductsCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\ImportState;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\ServiceBus\ServiceBus;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory\TestingMoneyS5ConfigFactory;

final class MoneyS5ProductsFacadeTest extends KernelTestCase
{
    private const CUSTOMER_CODE = '123456';

    /**
     * @test
     */
    public function get_products(): void
    {
        $moneyS5Config = TestingMoneyS5ConfigFactory::create();
        $productsApiServiceMock = $this->createMock(ProductsApiService::class);
        $productsCategoryApiServiceMock = $this->createMock(ProductsCategoryApiService::class);
        $productsGroupApiServiceMock = $this->createMock(ProductsGroupApiService::class);
        $customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $customerConfigFinderMock = $this->createMock(CustomerConfigFinder::class);
        $moneyS5SyncStatusRepositoryMock = $this->createMock(MoneyS5SyncStatusRepository::class);
        $productDtoFactoryMock = $this->createMock(ProductDtoFactory::class);
        $productCategoryDtoFactoryMock = $this->createMock(ProductCategoryDtoFactory::class);
        $productGroupDtoFactoryMock = $this->createMock(ProductGroupDtoFactory::class);
        $productParameterDtoFactoryMock = $this->createMock(ProductParameterDtoFactory::class);
        $productCategoryPathGeneratorMock = $this->createMock(ProductCategoryPathGenerator::class);
        $productAttributeDtoFactoryMock = $this->createMock(ProductAttributeDtoFactory::class);
        $serviceBusMock = $this->createMock(ServiceBus::class);

        $customerRepositoryMock->expects($this->once())->method('findByCode')->with(self::CUSTOMER_CODE)->willReturn(new Customer('Testing customer', self::CUSTOMER_CODE));

        $customerProvider = new CustomerProvider($customerRepositoryMock);

        $customerConfigFinderMock->expects($this->atLeastOnce())->method('find')->willReturn($moneyS5Config);

        $moneyS5SyncStatusRepositoryMock->expects($this->atLeastOnce())->method('findByCustomerId')->willReturn(new MoneyS5SyncStatus(10));

        $productsApiServiceMock->expects($this->exactly(1))->method('getProductsFromApi')->willReturn([
            'PageCount' => 1,
            'RowCount' => 1,
            'Data' => [
                'Articles' =>
                    [
                        '0' =>
                            [
                                'PolepkaTyp_UserData' => 0,
                                'CisloS3' => 9084,
                                'Kod' => 'P12290',
                                'Nazev' => 'UAG Glass Screen Shield - iPhone 14 Pro Max',
                                'Hmostnost' => [
                                    'ID' => 'c72282fc-da44-402c-8190-eb8686681b19',
                                    'Velicina_ID' => '76abc699-1574-48ef-acca-b070b3665279',
                                    'Hodnota' => 0.05,
                                    'Jednotka_ID' => null,
                                    'Jednotka' => null,
                                ],
                                'HlavniJednotka' => [
                                    'ID' => 'a62cca1c-dd4e-4e87-a61c-4b9271ae4b15',
                                    'TypJednotky' => 0,
                                    'Kod' => 'ks',
                                    'Jednotka' => [
                                        'ID' => 'e604a0fa-c14a-40ca-97ab-c92b2ce618ef',
                                        'Kod' => 'ks',
                                        'TypJednotky' => 4,
                                        'Poznamka' => '',
                                        'Nazev' => 'kus',
                                        'DesMisto' => 0,
                                    ],
                                ],
                                'CarovyKod' => '840283903649',
                                'Kategorie' => 'c792e9ea-afbc-4357-80e8-c5797dff3601|013f3a62-368c-45ab-96ca-a2fc4048ff53',
                            ],
                    ],
            ],
            'Status' => 1,
            'Message' => '',
            'StackTrace' => '',
        ]);

        $productsCategoryApiServiceMock->expects($this->exactly(1))->method('getProductCategoriesFromApi')->willReturn([
            'PageCount' => 1,
            'RowCount' => 4,
            'Data' => [
                'ArticleCategories' => [
                    '0' =>
                        [
                            'ID' => 'c792e9ea-afbc-4357-80e8-c5797dff3601',
                            'Kod' => '1000002900',
                            'ParentObject_ID' => 'f6ee3213-8c32-469f-86f9-dcc1eb8484f4',
                            'Nazev' => 'UAG',
                        ],
                    '1' =>
                        [
                            'ID' => 'f6ee3213-8c32-469f-86f9-dcc1eb8484f4',
                            'Kod' => '1VYR',
                            'ParentObject_ID' => null,
                            'Nazev' => '1Výrobce',
                        ],
                    '2' =>
                        [
                            'ID' => '013f3a62-368c-45ab-96ca-a2fc4048ff53',
                            'Kod' => '2000001600',
                            'ParentObject_ID' => '190d36ca-2ec4-4aac-b09a-0b96a754964d',
                            'Nazev' => 'Ochranná skla',
                        ],
                    '3' =>
                        [
                            'ID' => '190d36ca-2ec4-4aac-b09a-0b96a754964d',
                            'Kod' => '2KAT',
                            'ParentObject_ID' => null,
                            'Nazev' => '2Kategorie',
                        ],
                ],
            ],
            'Status' => 1,
            'Message' => '',
            'StackTrace' => '',
        ]);

        $productsGroupApiServiceMock->expects($this->exactly(1))->method('getProductGroupsFromApi')->willReturn([
            'PageCount' => 1,
            'RowCount' => 1,
            'Data' => [
                'MerpGroups' => [
                    '0' =>
                        [
                            'ID' => '3f9a9416-d3e9-4f48-8267-005cb1f72003',
                            'ParentObject_ID' => '663d0629-1994-442a-8276-17703bd7cb2c',
                            'Kod' => 'PEAKOLD',
                            'Nazev' => 'Peak Desing OLD',
                        ],
                ],
            ],
            'Status' => 1,
            'Message' => '',
            'StackTrace' => '',
        ]);

        $serviceBusMock->expects($this->exactly(1))->method('dispatch')->with($this->isInstanceOf(CreateProductsCommand::class));

        $importState = new ImportState();

        $importState->changeStartedTime(Chronos::createFromFormat('Y-m-d H:i:s', '2023-08-03 08:15:30'));
        $importState->finishedSuccessfully();

        $productsFacade = new MoneyS5ProductsFacade(
            $productsApiServiceMock,
            $productsCategoryApiServiceMock,
            $productsGroupApiServiceMock,
            $productDtoFactoryMock,
            $productCategoryDtoFactoryMock,
            $productGroupDtoFactoryMock,
            $productParameterDtoFactoryMock,
            $productCategoryPathGeneratorMock,
            $serviceBusMock,
            $productAttributeDtoFactoryMock,
            $moneyS5SyncStatusRepositoryMock,
            $customerConfigFinderMock,
            $customerProvider,
            $importState
        );

        $productsFacade->transferProducts(self::CUSTOMER_CODE);
    }
}
