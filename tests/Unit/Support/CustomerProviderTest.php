<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Support;

use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\ServiceBus\Exception\CustomerNotFoundException;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CustomerProviderTest extends KernelTestCase
{
    private MockObject $customerRepository;

    private CustomerProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $customerRepository = $this->createMock(CustomerRepository::class);
        $this->customerRepository = $customerRepository;

        $this->provider = new CustomerProvider($this->customerRepository);
    }

    /**
     * @test
     **/
    public function it_provides_customer(): void
    {
        $this->customerRepository->method('findByCode')->willReturn($this->createMock(Customer::class));
        $this->customerRepository->method('findById')->willReturn($this->createMock(Customer::class));

        $this->assertInstanceOf(Customer::class, $this->provider->provideByCode('customer-code'));
        $this->assertInstanceOf(Customer::class, $this->provider->provideById(1));
    }

    /**
     * @test
     **/
    public function it_throws_exception_when_customer_by_code_does_not_exists(): void
    {
        $this->customerRepository->method('findByCode')->willReturn(null);

        $this->expectException(CustomerNotFoundException::class);
        $this->expectExceptionMessage('Customer with code: non-existing-customer-code does not exist');
        $this->provider->provideByCode('non-existing-customer-code');
    }

    /**
     * @test
     **/
    public function it_throws_exception_when_customer_by_id_does_not_exists(): void
    {
        $this->customerRepository->method('findById')->willReturn(null);

        $this->expectException(CustomerNotFoundException::class);
        $this->expectExceptionMessage('Customer with id: 1 does not exist');
        $this->provider->provideById(1);
    }
}
