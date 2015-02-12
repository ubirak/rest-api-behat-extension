<?php

namespace Rezzza\JsonApiBehatExtension;

use Behat\Behat\Extension\ExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Extension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/Resources'));
        $loader->load('services.xml');
    }

    public function getConfig(ArrayNodeDefinition $builder)
    {
        return
            $builder
                ->children()
                    ->scalarNode('base_url')->end()
                ->end()
            ->end()
        ;
    }

    public function getCompilerPasses()
    {
        return array();
    }
}

