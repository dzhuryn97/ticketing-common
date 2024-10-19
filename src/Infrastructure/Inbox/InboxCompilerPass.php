<?php

namespace Ticketing\Common\Infrastructure\Inbox;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InboxCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $integrationEventHandlerIds = $container->findTaggedServiceIds('common.integration_event.handler');

        $eventToHandlersMap = [];
        foreach ($integrationEventHandlerIds as $integrationEventHandlerId => $args) {
            $definition = $container->getDefinition($integrationEventHandlerId);

            $class = $definition->getClass();
            $reflection = new \ReflectionClass($class);
            $invokeMethod = $reflection->getMethod('__invoke');

            if (!$invokeMethod) {
                throw new \Exception(sprintf('%s doesn\'t has __invoke method', $class));
            }

            $parameters = $invokeMethod->getParameters();
            if (1 !== count($parameters)) {
                throw new \Exception(sprintf('__invoke method of class %s must have event parameter', $class));
            }

            $eventParameter = $invokeMethod->getParameters()[0];
            $eventName = $eventParameter->getType()->getName();

            $eventToHandlersMap[$eventName][] = $definition;
        }

        $inboxMessageHandlerDefinition = $container->getDefinition('common.inbox.message_consume_command');
        $inboxMessageHandlerDefinition->setArgument('$eventToHandlersMap', $eventToHandlersMap);
    }
}
