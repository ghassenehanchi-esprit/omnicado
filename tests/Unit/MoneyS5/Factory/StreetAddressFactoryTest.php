<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5\Factory;

use Elasticr\ServiceBus\MoneyS5\Factory\StreetAddressFactory;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StreetAddressFactoryTest extends KernelTestCase
{
    /**
     * @test
     */
    public function create_street_address(): void
    {
        /** @var StreetAddressFactory $factory */
        $factory = self::getContainer()->get(StreetAddressFactory::class);

        $dto = new AddressDto(
            'Ingredi Europa s.r.o.',
            '',
            '',
            'Formanská',
            '257',
            'Praha',
            '14900',
            'Česko',
            '',
            '',
            '28544668',
            'CZ28544668'
        );

        $this->assertEquals('Formanská 257', $factory->create($dto));
    }
}
