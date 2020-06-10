<?php

namespace BretRZaun\MaintenanceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('maintenance');
        $rootNode = $treeBuilder->getRootNode();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->scalarNode('from')->defaultNull()->end()
                ->scalarNode('until')->defaultNull()->end()
                ->arrayNode('allowed_ip')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->scalarNode('template')->defaultValue('@Maintenance/maintenance.html.twig')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
