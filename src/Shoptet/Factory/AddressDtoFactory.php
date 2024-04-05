<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;

final class AddressDtoFactory
{
    /**
     * @param array<string, string>|null $data
     */
    public function createFromApiResponse(?array $data): ?AddressDto
    {
        if ($data === null) {
            return null;
        }

        $fullnameExploded = $this->explodeFullname($data['fullName']);

        return new AddressDto(
            $data['company'],
            $fullnameExploded['firstname'],
            $fullnameExploded['lastname'],
            $data['street'] ?? '',
            $data['houseNumber'] ?? '',
            $data['city'] ?? '',
            $data['zip'] ?? '',
            $data['countryCode'] ?? '',
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['companyId'] ?? null,
            $data['vatId'] ?? null
        );
    }

    /**
     * @return string[]
     */
    public function explodeFullname(?string $fullName): array
    {
        $explodedFullname = explode(' ', $fullName ?? '', 2);
        return [
            'firstname' => $explodedFullname[0] ?? '',
            'lastname' => $explodedFullname[1] ?? '',
        ];
    }
}
