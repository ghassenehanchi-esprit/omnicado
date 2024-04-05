<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit;

use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;

trait TestingAddressTrait
{
    protected function createAddress(): AddressDto
    {
        return new AddressDto('KomTeSa spol. s r.o.', 'John', 'Doe', 'St. Johns', '12', 'Doetown', '11122', 'CZE', 'john@doe.com', '111222333', '12345678', 'CZ12345678');
    }
}
