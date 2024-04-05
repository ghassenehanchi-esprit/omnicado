<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\MoneyS5;

use Elasticr\ServiceBus\MoneyS5\GraphqlHelper;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GraphqlHelperTest extends KernelTestCase
{
    /**
     * @test
     **/
    public function convert_to_graphql(): void
    {
        $this->assertSame($this->expectedString(), GraphqlHelper::convertToGraphql($this->data()));
    }

    /**
     * @return array<string, mixed>
     */
    private function data(): array
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../expectations/moneys5/issued_order.json');

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    private function expectedString(): string
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents(__DIR__ . '/../../expectations/moneys5/graphql_issued_order.txt');

        return $fileContent;
    }
}
