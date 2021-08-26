<?php

namespace Koco\AvroRegy\Tests;

use Koco\AvroRegy\AvroRegyBundle;
use Koco\AvroRegy\AvroSchemaRegistrySerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class ServiceWiringTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new AvroRegyTestingKernel([
            'base_uri' => 'http://localhost',
            'file_naming_strategy' => 'subject',
            'options' => [
                'register_missing_schemas' => true,
                'register_missing_subjects' => true
            ],
            'serializers' => [
                'default' => [
                    'schema_dir' => 'test/dir'
                ]
            ]
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        $serializer = $container->get('avro_regy.serializer.default');
        $this->assertInstanceOf(AvroSchemaRegistrySerializer::class, $serializer);
    }
}

class AvroRegyTestingKernel extends Kernel
{
    private array $avroRegyConfig;

    public function __construct(array $avroRegyConfig = [])
    {
        $this->avroRegyConfig = $avroRegyConfig;

        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new AvroRegyBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function(ContainerBuilder $builder) {
            $builder->register('example_serializer', ExampleSerializer::class)
                ->addTag(
                    'avro_regy.serializer.default',
                    [
                        'qualified_name' => 'com.example.Test',
                        'class_name' => 'Koco\AvroRegy\Tests\ExampleSerializer',
                        'key_subject' => 'test-key',
                        'value_subject' => 'test-value'
                    ]
                );

            $builder->loadFromExtension('avro_regy', $this->avroRegyConfig);
        });
    }

    public function getCacheDir()
    {
        return __DIR__.'/cache/'.spl_object_hash($this);
    }
}

class ExampleSerializer implements SerializerInterface
{

    public function decode(array $encodedEnvelope): Envelope
    {
        // TODO: Implement decode() method.
    }

    public function encode(Envelope $envelope): array
    {
        // TODO: Implement encode() method.
    }
}