<?php

declare(strict_types=1);

namespace Koco\AvroRegy;

use AvroRecordSchema;
use AvroSchema;
use const FlixTech\AvroSerializer\Common\get;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use function FlixTech\AvroSerializer\Protocol\decode;
use const FlixTech\AvroSerializer\Protocol\PROTOCOL_ACCESSOR_SCHEMA_ID;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use function Widmogrod\Functional\curryN;
use function Widmogrod\Functional\valueOf;

class FlixTechSchemaRegistryClient
{
    protected CachedRegistry $schemaRegistryClient;
    protected RecordSerializer $recordSerializer;

    public function __construct(string $schemaRegistryUrl, array $recordSerializerOptions)
    {
        $this->schemaRegistryClient = new CachedRegistry(
            new PromisingRegistry(
                new Client([
                    'base_uri' => $schemaRegistryUrl,
                ])
            ),
            new AvroObjectCacheAdapter()
        );

        $this->recordSerializer = new RecordSerializer(
            $this->schemaRegistryClient,
            $recordSerializerOptions
        );
    }

    public function getQualifiedNameFromBinary(string $binary): string
    {
        $schema = $this->getAvroRecordSchemaFromBinary($binary);

        return $schema->qualified_name();
    }

    public function decode(string $binary, string $avsc): array
    {
        return $this->recordSerializer->decodeMessage(
            $binary,
            AvroSchema::parse($avsc)
        );
    }

    /**
     * @param mixed $record
     */
    public function encode($record, string $subject, string $avsc): string
    {
        return $this->recordSerializer->encodeRecord(
            $subject,
            AvroSchema::parse($avsc),
            $record
        );
    }

    protected function getAvroRecordSchemaFromBinary(string $binaryMessage): AvroRecordSchema
    {
        $decoded = decode($binaryMessage);

        $get = curryN(2, get);
        $schemaIdGetter = $get(PROTOCOL_ACCESSOR_SCHEMA_ID);

        $schemaId = valueOf($decoded->bind($schemaIdGetter));

        $schema = $this->schemaRegistryClient->schemaForId($schemaId);

        if ($schema instanceof PromiseInterface) {
            $schema = $schema->wait();
        }

        if ($schema instanceof \Exception) {
            throw $schema;
        }

        return $schema;
    }
}
