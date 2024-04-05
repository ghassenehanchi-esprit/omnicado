<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Exception\DispatchingCommandException;
use Elasticr\ServiceBus\ServiceBus\Exception\HandleableOrderException;
use Exception;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\RoutableMessageBus;

final class ServiceBus
{
    private TargetCommandBusFinder $targetCommandBusFinder;

    /**
     * @var MessageBusInterface[]
     */
    private array $messageBusses;

    /**
     * @param MessageBusInterface[] $messageBusses
     */
    public function __construct(array $messageBusses, TargetCommandBusFinder $targetCommandBusFinder)
    {
        $this->messageBusses = $messageBusses;
        $this->targetCommandBusFinder = $targetCommandBusFinder;
    }

    public function dispatch(CommandContract $command, string $commandBusId): void
    {
        try {
            $targetCommandBusId = $this->targetCommandBusFinder->find($command->customerId(), $commandBusId);

            $targetCommandBus = null;

            foreach ($this->messageBusses as $bus) {
                if ($bus instanceof RoutableMessageBus) {
                    /** @var MessageBusInterface $targetCommandBus */
                    $targetCommandBus = $bus->getMessageBus($targetCommandBusId);
                    break;
                }
            }

            if ($targetCommandBus === null) {
                throw new Exception('Command bus for ' . $targetCommandBusId . 'was not found.');
            }

            $targetCommandBus->dispatch($command);
        } catch (HandlerFailedException $exception) {
            $previousException = $exception->getPrevious();
            if ($previousException instanceof HandleableOrderException) {
                throw $previousException;
            }

            throw new DispatchingCommandException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
