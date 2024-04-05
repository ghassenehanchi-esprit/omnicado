<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

final class ElasticrServiceBusExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../../config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $doctrine = Yaml::parseFile(__DIR__ . '/../../../config/doctrine.yaml');
        $doctrine['orm']['entity_managers']['default']['mappings']['elasticr-service-bus']['dir'] = __DIR__ . '/../../../config/doctrine/elasticr-service-bus';

        $container->prependExtensionConfig('doctrine', $doctrine);
        $container->prependExtensionConfig('framework', Yaml::parseFile(__DIR__ . '/../../../config/framework.yaml'));
    }
}
