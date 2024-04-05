<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\App;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;

final class FakeCommand implements CommandContract
{
    private CommandResult $commandResult;

    public function __construct(CommandResult $commandResult)
    {
        $this->commandResult = $commandResult;
    }

    public function commandResult(): CommandResult
    {
        return $this->commandResult;
    }

    public function customerId(): int
    {
        return 0;
    }
}
