<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Filter;

use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5StockDocumentFilters;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StockDocumentFiltersTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     **/
    public function create_filter_sample(): void
    {
        $expectedFilter = [];
        $expectedFilter['Filter'] = 'Deleted~eq~false#Nazev~eq~vzorek#TypDokladu~eq~2';
        $expectedFilter['ChangeFrom'] = '2023-09-22T00:00:01';

        $actualFilter =
        new MoneyS5StockDocumentFilters(
            null,
            'vzorek',
            '2',
            DateHelper::convertStringToChronos('2023-09-22T00:00:01'),
        );

        $this->assertEquals($expectedFilter, $actualFilter->convertToQuery());
    }

    /**
     * @test
     **/
    public function create_filter_reference(): void
    {
        $expectedFilter = [];
        $expectedFilter['Filter'] = 'Deleted~eq~false#Odkaz~sw~PX';
        $expectedFilter['ChangeFrom'] = '2023-09-22T00:00:01';

        $actualFilter =
            new MoneyS5StockDocumentFilters(
                'PX',
                null,
                null,
                DateHelper::convertStringToChronos('2023-09-22T00:00:01'),
            );

        $this->assertEquals($expectedFilter, $actualFilter->convertToQuery());
    }
}
