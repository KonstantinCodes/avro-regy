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
                        ->variableNode('auth')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return !\is_array($v) && !\is_string($v);
                                })
                                ->thenInvalid('auth can be: string or array')
                            ->end()
                        ->end()
                    ->scalarNode('timeout')
                        ->beforeNormalization()
                        ->always(function ($v) {
                            return is_numeric($v) ? (float) $v : $v;
                        })
                        ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return !\is_float($v) && !(\is_string($v) && strpos($v, 'env_') === 0);
                                })
                                ->thenInvalid('timeout can be: float')
                            ->end()
                        ->end()
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
