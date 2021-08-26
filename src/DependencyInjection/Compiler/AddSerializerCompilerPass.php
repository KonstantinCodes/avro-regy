<?php

declare(strict_types=1);

namespace Koco\AvroRegy\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddSerializerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (strpos($serviceId, 'avro_regy.serializer.') === 0) {
                $this->findAndAddSerializers($container, $definition, $serviceId);
            }
        }
    }

    private function findAndAddSerializers(ContainerBuilder $container, Definition $definition, string $tagName): void
    {
        $taggedSerializers = $container->findTaggedServiceIds($tagName);

        foreach ($taggedSerializers as $taggedSerializerId => $tags) {
            foreach ($tags as $tag) {
                $definition->addMethodCall('addSerializer', [
                    new Reference($taggedSerializerId),
                    $tag['qualified_name'],
                    $tag['class_name'],
                    $tag['key_subject'],
                    $tag['value_subject'],
                ]);
            }
        }
    }
}
