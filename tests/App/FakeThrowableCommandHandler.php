<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\App;

use Elasticr\ServiceBus\ServiceBus\Exception\CreateOrderException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FakeThrowableCommandHandler implements MessageHandlerInterface
{
    public function __invoke(FakeThrowableCommand $command): void
    {
        throw new CreateOrderException();
    }
}
