<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Filter;

use Elasticr\ServiceBus\MoneyS5\DateHelper;
use Elasticr\ServiceBus\MoneyS5\ValueObject\MoneyS5IssuedInvoicesFilters;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IssuedInvoiceFiltersTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     **/
    public function create_filter_issued_invoice(): void
    {
        $expectedFilter = [];
        $expectedFilter['Filter'] = 'Deleted~eq~false#Nazev~ct~opravný#Nazev~ct~daňový#Nazev~ct~doklad';
        $expectedFilter['ChangeFrom'] = '2023-09-29T14:55:00';

        $actualFilter =
        new MoneyS5IssuedInvoicesFilters(
            ['opravný', 'daňový', 'doklad'],
            DateHelper::convertStringToChronos('2023-09-29T14:55:00'),
        );

        $this->assertEquals($expectedFilter, $actualFilter->convertToQuery());
    }
}
