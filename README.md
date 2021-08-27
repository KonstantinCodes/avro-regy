# avro-regy

This bundle provides a simple way to integrate with the Confluent Schema Registry.

## Installation

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require koco/avro-regy
```

### Applications that don't use Symfony Flex

After adding the composer requirement, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
return [
    // ...
    Koco\AvroRegy\AvroRegyBundle::class => ['all' => true],
];
```

## Configuration
Create `config/avro_regy.yaml` and configure similar to the following example:
```yaml
avro_regy:
  base_uri: '%env(SCHEMA_REGISTRY_URL)%'
  file_naming_strategy: subject
  options:
    register_missing_schemas: true
    register_missing_subjects: true
  serializers:
    catalogue:
      schema_dir: '%kernel.project_dir%/src/Catalogue/Domain/Model/Event/Avro/'
    orders:
      schema_dir: '%kernel.project_dir%/src/Orders/Domain/Model/Event/Avro/'
```

Then, write your serializer like so:
```php
namespace App\Catalogue\Infrastructure\Messenger\Serializer;

use App\Catalogue\Domain\Model\Event\ProductCreated;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class ProductCreatedSerializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        $record = $encodedEnvelope['body'];

        return new Envelope(new ProductCreated(
            $record['id'],
            $record['name'],
            $record['description'],
        ));
    }

    public function encode(Envelope $envelope): array
    {
        /** @var ProductCreated $event */
        $event = $envelope->getMessage();
        
        return [
            'key' => $event->getId(),
            'headers' => [],
            'body' => [
                'id' => $event->getId(),
                'name' => $event->getName(),
                'description' => $event->getDescription(),
            ],
        ];
    }
}

```

And tag it:
```yaml
App\Catalogue\Infrastructure\Messenger\Serializer\ProductCreatedSerializer:
  tags:
    - {
      name: 'avro_regy.serializer.catalogue',
      qualified_name: 'com.Example.Catalogue.ProductCreated',
      class_name: '\App\Catalogue\Domain\Model\Event\ProductCreated',
      key_subject: 'catalogue.product_created-key',
      value_subject: 'catalogue.product_created-value'
    }
```
