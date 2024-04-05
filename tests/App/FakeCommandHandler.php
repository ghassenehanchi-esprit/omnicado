<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\App;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FakeCommandHandler implements MessageHandlerInterface
{
    public function __invoke(FakeCommand $command): void
    {
        $command->commandResult()->markAsSuccessful();
    }
}
