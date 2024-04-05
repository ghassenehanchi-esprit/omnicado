<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Transforming\Eso9PriceFormatter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class Eso9PriceFormatterTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_formats_number_to_eso_9_number_standard(): void
    {
        $this->assertSame('49.9900', Eso9PriceFormatter::format(49.99));
        $this->assertSame('50.0000', Eso9PriceFormatter::format(50));
        $this->assertSame('50.1000', Eso9PriceFormatter::format(50.1));
    }
}
