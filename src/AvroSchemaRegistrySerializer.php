<?php

declare(strict_types=1);

namespace Koco\AvroRegy;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class AvroSchemaRegistrySerializer implements SerializerInterface
{
    protected FlixTechSchemaRegistryClient $registryClient;
    protected string $fileNamingStrategy;
    protected string $schemaDir;

    protected array $serializersByQualifiedName = [];
    protected array $serializersByClassName = [];
    protected array $classNameToQualifiedName = [];
    protected array $subjectsByQualifiedName = [];

    public function __construct(string $schemaDir, string $schemaRegistryUrl, array $recordSerializerOptions, string $fileNamingStrategy)
    {
        $this->registryClient = new FlixTechSchemaRegistryClient($schemaRegistryUrl, $recordSerializerOptions);
        $this->fileNamingStrategy = $fileNamingStrategy;
        $this->schemaDir = $schemaDir;
    }

    protected function getSerializerByAvroQualifiedName(string $qualifiedName): SerializerInterface
    {
        if (!\array_key_exists($qualifiedName, $this->serializersByQualifiedName)) {
            throw new TransportException(sprintf('No Serializer found for Avro Qualified Name "%s"', $qualifiedName));
        }

        return $this->serializersByQualifiedName[$qualifiedName];
    }

    protected function getSerializerByPhpClassName(string $className): SerializerInterface
    {
        if (!\array_key_exists($className, $this->serializersByClassName)) {
            throw new TransportException(sprintf('No Serializer found for Class "%s"', $className));
        }

        return $this->serializersByClassName[$className];
    }

    public function addSerializer(SerializerInterface $serializer, string $qualifiedName, string $className, ?string $keySubject, string $valueSubject): void
    {
        $this->serializersByQualifiedName[$qualifiedName] = $serializer;
        $this->serializersByClassName[$className] = $serializer;
        $this->classNameToQualifiedName[$className] = $qualifiedName;
        $this->subjectsByQualifiedName[$qualifiedName] = [
            'key' => $keySubject,
            'value' => $valueSubject,
        ];
    }

    public function getAvsc(string $qualifiedName, string $subject): string
    {
        if ($this->fileNamingStrategy === 'subject') {
            $fileName = $subject;
        } else {
            $fileName = $qualifiedName;
        }

        if ($avroSchemaFile = file_get_contents($this->schemaDir . $fileName . '.avsc')) {
            return $avroSchemaFile;
        }

        throw new \Exception('AVRO Schema not found!');
    }

    public function getKeyAvsc(string $qualifiedName): ?string
    {
        if (!$this->subjectsByQualifiedName[$qualifiedName]['key']) {
            return null;
        }

        return $this->getAvsc($qualifiedName . '-key', $this->subjectsByQualifiedName[$qualifiedName]['key']);
    }

    public function getValueAvsc(string $qualifiedName): string
    {
        return $this->getAvsc($qualifiedName, $this->subjectsByQualifiedName[$qualifiedName]['value']);
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        $qualifiedName = $this->registryClient->getQualifiedNameFromBinary($encodedEnvelope['body']);

        $encodedEnvelope['body'] = $this->registryClient->decode(
            $encodedEnvelope['body'],
            $this->getValueAvsc($qualifiedName)
        );

        return $this->getSerializerByAvroQualifiedName($qualifiedName)->decode($encodedEnvelope);
    }

    public function encode(Envelope $envelope): array
    {
        $className = \get_class($envelope->getMessage());
        $qualifiedName = $this->classNameToQualifiedName[$className];

        $encoded = $this->getSerializerByPhpClassName($className)->encode($envelope);

        $keySubject = $this->subjectsByQualifiedName[$qualifiedName]['key'];
        $keyAvsc = $this->getKeyAvsc($qualifiedName);

        if ($keyAvsc && $keySubject && \array_key_exists('key', $encoded)) {
            $encoded['key'] = $this->registryClient->encodeKey($encoded['key'], $keySubject, $keyAvsc);
        }

        $encoded['body'] = $this->registryClient->encodeValue(
            $encoded['body'],
            $this->subjectsByQualifiedName[$qualifiedName]['value'],
            $this->getValueAvsc($qualifiedName)
        );

        return $encoded;
    }
}
