<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\Eso9\Transforming\Eso9AdviceTransformer;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderNoteDto;
use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9AdviceTransformerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_transforms_order_dto_to_eso9_advice_data(): void
    {
        /** @var Eso9AdviceTransformer $transformer */
        $transformer = self::getContainer()->get(Eso9AdviceTransformer::class);

        $this->assertEquals($this->expectedArray(), $transformer->transform($this->createOrderDto()));
    }

    private function createOrderDto(): OrderDto
    {
        return new OrderDto(
            1,
            'ABC123',
            new AddressDto('', 'Karel', 'Novák', 'Ulice', '123', 'Praha', '12345', 'CZ', null, null, null, null),
            null,
            new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0)),
            new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), null),
            '',
            '',
            0,
            0,
            '',
            [new OrderItemDto(1, 'SKU1', 'Produkt 1', 1, 'KS', 0, 0, 0, 0, '', 0, 0), new OrderItemDto(2, 'SKU2', 'Produkt 2', 10, 'KS', 0, 0, 0, 0, '', 0, 0)],
            Chronos::now(),
            new OrderNoteDto('')
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function expectedArray(): array
    {
        return [
            'Advice_Id' => 'ABC123',
            'Dodavatel' => 'Karel Novák',
            'Poznamka' => '',
            'Polozky' => [
                ['Produkt_Id' => 'SKU1',
                    'Mnozstvi' => '1.0000',
                ],
                ['Produkt_Id' => 'SKU2',
                    'Mnozstvi' => '10.0000',
                ],
            ],
        ];
    }
}
