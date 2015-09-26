<?php

namespace Dwf\PronosticsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dwf_pronostics');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->arrayNode('from_email')
                    ->children()
                        ->scalarNode('address')->end()
                        ->scalarNode('sender_name')->end()
                    ->end()
                ->end() // from_email
                ->arrayNode('invitation')
                    ->children()
                        ->scalarNode('template')->end()
                    ->end()
                ->end() // invitation
            ->end()
        ;
        return $treeBuilder;
    }
}
