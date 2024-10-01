<?php

namespace Ticketing\Common\Infrastructure\DI;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Ticketing\Common\Presenter\ApiPlatform\ErrorProvider;

class ErrorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $errorProviderDefinition = new Definition(ErrorProvider::class);

        $errorProviderDefinition->setArgument('$exceptionTransformer', new Reference('common.error.exception_transformer.delegated_exception_transformer'));
        $errorProviderDefinition->setArgument('$resourceClassResolver', new Reference('api_platform.resource_class_resolver'));
        $errorProviderDefinition->addTag('api_platform.state_provider');

        $container->setDefinition('api_platform.state.error_provider', $errorProviderDefinition);
    }
}
