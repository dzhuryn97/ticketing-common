<?php

namespace Ticketing\Common;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Ticketing\Common\Application\Command\CommandHandlerInterface;
use Ticketing\Common\Application\DomainEventHandlerInterface;
use Ticketing\Common\Application\EventBus\IntegrationEventHandlerInterface;
use Ticketing\Common\Application\EventBus\IntegrationEventInterface;
use Ticketing\Common\Application\Query\QueryHandlerInterface;
use Ticketing\Common\Infrastructure\Inbox\InboxCompilerPass;
use Ticketing\Common\Infrastructure\Outbox\OutboxMessage;

class CommonBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->arrayNode('event_bus')
            ->children()
            ->scalarNode('exchange_name')->end()
            ->scalarNode('queues_name')->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->registerForAutoconfiguration(QueryHandlerInterface::class)
            ->addTag('messenger.message_handler');

        $builder->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag('messenger.message_handler');

        $builder->registerForAutoconfiguration(DomainEventHandlerInterface::class)
            ->addTag('messenger.message_handler');

        $builder->registerForAutoconfiguration(IntegrationEventHandlerInterface::class)
            ->addTag('common.integration_event.handler');


        $container->import('../config/*');
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new InboxCompilerPass());
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $commonConfig = $builder->getExtensionConfig('common');
        $eventBusConfig = $commonConfig[0]['event_bus'];

        $container->extension('framework', [
            'messenger' => [
                'buses' => [
                    'common.command_bus' => [
                        'middleware' => [
                            'common.domain_exception_extracting_middleware' => [],
                        ],
                    ],
                    'common.query_bus' => [
                        'middleware' => [
                            'common.domain_exception_extracting_middleware' => [],
                        ],
                    ],
                    'common.outbox_message_bus' => [
                        'middleware' => [
                            'dispatch_after_current_bus' => [],
                        ],
                    ],
                    'common.distributed_message_bus' => [],

                ],
                'transports' => [
                    'common.outbox' => '%env(MESSENGER_DOCTRINE_TRANSPORT_DSN)%',
                    'common.distributed' => [
                        'dsn' => '%env(MESSENGER_DISTRIBUTED_TRANSPORT_DSN)%',
                        'options' => [
                            'exchange' => [
                                'name' => $eventBusConfig['exchange_name'],
                                'type' => 'direct',
                            ],
                            'queues' => [
                                $eventBusConfig['queues_name'] => '~',
                            ],
                        ],
                    ],

                ],
                'routing' => [
                    OutboxMessage::class => 'common.outbox',
                    IntegrationEventInterface::class => 'common.distributed',
                ],
            ],
        ], prepend: true);

        $container->extension('api_platform', [
            'exception_to_status' => [
                \DomainException::class => 400,
            ],
        ], prepend: true);
    }
}
