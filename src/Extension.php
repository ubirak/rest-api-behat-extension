<?php

namespace Rezzza\RestApiBehatExtension;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Extension implements ExtensionInterface
{
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('rezzza.json_api.rest.client.config', $config['rest']['client']["config"]);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/Resources'));
        $loader->load('services.xml');

        if (true === $config['rest']['store_response']) {
            $definitionRestApiBrowser = $container->findDefinition('rezzza.json_api.rest.rest_api_browser');
            $definitionRestApiBrowser->addMethodCall('enableResponseStorage', array(new Reference('rezzza.json_api.json.json_storage')));
        }
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('rest')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('store_response')
                            ->defaultTrue()->end()
                        ->arrayNode('client')
                        ->addDefaultsIfNotSet()
                        ->children()

                            ->arrayNode('config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('base_uri')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function getConfigKey()
    {
        return 'json_api';
    }

    public function process(ContainerBuilder $container)
    {
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }
}

