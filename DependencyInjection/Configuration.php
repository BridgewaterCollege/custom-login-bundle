<?php
namespace Tweisman\Bundle\CustomLoginBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tweisman_custom_login', 'array');

        $rootNode
            ->children()
                ->arrayNode('simplesaml')
                    ->children()
                        ->arrayNode('attributes')
                            ->children()
                                ->variableNode('primary_key')->end()
                                ->variableNode('required_attributes')->end()
                                ->variableNode('user_mappings')->end()
                        ->end()
                    ->end() //config
                ->end() //simplesaml
            ->end()
        ;

        return $treeBuilder;
    }
}