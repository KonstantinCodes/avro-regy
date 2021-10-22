<?php

declare(strict_types=1);

namespace Koco\AvroRegy\DependencyInjection;

use Koco\AvroRegy\AvroSchemaRegistrySerializer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AvroRegyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        if (!$configuration = $this->getConfiguration($configs, $container)) {
            return;
        }

        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['serializers'] as $serializerName => $serializerConfig) {
            $definition = new Definition(AvroSchemaRegistrySerializer::class, [
                $serializerConfig['schema_dir'],
                $serializerConfig['base_uri'] ?? $config['base_uri'],
                $serializerConfig['options'] ?? $config['options'],
                $serializerConfig['file_naming_strategy'] ?? $config['file_naming_strategy'],
            ]);

            $definition->setPublic(true);

            $container->setDefinition('avro_regy.serializer.' . $serializerName, $definition);
        }
    }

    public function getAlias()
    {
        return 'avro_regy';
    }
}
