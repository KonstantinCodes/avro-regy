<?php

declare(strict_types=1);

namespace Koco\AvroRegy\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('avro_regy');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('base_uri')->isRequired()->end()
                ->arrayNode('request_options')
                    ->children()
                        ->variableNode('auth')->end()
                    ->end()
                ->end()
                ->enumNode('file_naming_strategy')
                    ->values(['subject', 'qualified_name'])
                    ->isRequired()
                ->end()
                ->arrayNode('options')
                    ->isRequired()
                    ->children()
                        ->booleanNode('register_missing_schemas')->isRequired()->end()
                        ->booleanNode('register_missing_subjects')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('serializers')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('base_uri')->end()
                            ->scalarNode('schema_dir')->end()
                            ->enumNode('file_naming_strategy')
                                ->values(['subject', 'qualified_name'])
                            ->end()
                            ->arrayNode('options')
                                ->children()
                                    ->booleanNode('register_missing_schemas')->defaultFalse()->end()
                                    ->booleanNode('register_missing_subjects')->defaultFalse()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
