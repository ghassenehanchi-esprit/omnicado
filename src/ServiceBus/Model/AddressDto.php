<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class AddressDto
{
    private ?string $company;

    private string $firstname;

    private string $lastname;

    private string $street;

    private ?string $houseNumber;

    private string $city;

    private string $zipCode;

    private string $country;

    private ?string $email;

    private ?string $phoneNumber;

    private ?string $cidNumber;

    private ?string $vatNumber;

    private string $gln;

    public function __construct(
        ?string $company,
        string $firstname,
        string $lastname,
        string $street,
        ?string $houseNumber,
        string $city,
        string $zipCode,
        string $country,
        ?string $email,
        ?string $phoneNumber,
        ?string $cidNumber,
        ?string $vatNumber,
        string $gln = '',
    ) {
        $this->street = $street;
        $this->houseNumber = $houseNumber;
        $this->city = $city;
        $this->zipCode = $zipCode;
        $this->country = $country;
        $this->company = $company;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->cidNumber = $cidNumber;
        $this->vatNumber = $vatNumber;
        $this->gln = $gln;
    }

    public function company(): ?string
    {
        return $this->company;
    }

    public function firstName(): string
    {
        return $this->firstname;
    }

    public function lastName(): string
    {
        return $this->lastname;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function houseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function zipCode(): string
    {
        return $this->zipCode;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function cidNumber(): ?string
    {
        return $this->cidNumber;
    }

    public function vatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function gln(): string
    {
        return $this->gln;
    }

    /**
     * @return array{
     *     company: ?string,
     *     cidNumber: ?string,
     *     vatNumber: ?string,
     *     firstname: string,
     *     lastname: string,
     *     phoneNumber: ?string,
     *     email: ?string,
     *     country: string,
     *     street: string,
     *     houseNumber: ?string,
     *     city: string,
     *     zipCode: string,
     *     gln: string
     * }
     */
    public function toArray(): array
    {
        return [
            'company' => $this->company,
            'cidNumber' => $this->cidNumber,
            'vatNumber' => $this->vatNumber,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'phoneNumber' => $this->phoneNumber,
            'email' => $this->email,
            'country' => $this->country,
            'street' => $this->street,
            'houseNumber' => $this->houseNumber,
            'city' => $this->city,
            'zipCode' => $this->zipCode,
            'gln' => $this->gln,
        ];
    }
}
