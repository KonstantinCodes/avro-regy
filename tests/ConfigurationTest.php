<?php

declare(strict_types=1);

namespace Koco\AvroRegy\Tests;

use Koco\AvroRegy\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testBaseUriMustBeSet(): void
    {
        static::assertConfigurationIsInvalid(
            [[]],
            'The child config "base_uri" under "avro_regy" must be configured.'
        );
    }

    public function testFileNamingStrategyMustBeSet(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
            ]],
            'The child config "file_naming_strategy" under "avro_regy" must be configured.'
        );
    }

    public function testFileNamingStrategyMustBeSetCorrectly(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'invalid',
            ]],
            'The value "invalid" is not allowed for path "avro_regy.file_naming_strategy". Permissible values: "subject", "qualified_name"'
        );
    }

    public function testOptionsMustBeSet(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
            ]],
            'The child config "options" under "avro_regy" must be configured.'
        );
    }

    public function testOptionsRegisterMissingSchemasMustBeSet(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
                'options' => [
                ],
            ]],
            'The child config "register_missing_schemas" under "avro_regy.options" must be configured.'
        );
    }

    public function testOptionsRegisterMissingSchemasMustBeSetCorrectly(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
                'options' => [
                    'register_missing_schemas' => 1,
                ],
            ]],
            'Invalid type for path "avro_regy.options.register_missing_schemas". Expected "bool", but got "int".'
        );
    }

    public function testOptionsRegisterMissingSubjectsMustBeSet(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
                'options' => [
                    'register_missing_schemas' => true,
                ],
            ]],
            'The child config "register_missing_subjects" under "avro_regy.options" must be configured.'
        );
    }

    public function testOptionsRegisterMissingSubjectsMustBeSetCorrectly(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
                'options' => [
                    'register_missing_schemas' => true,
                    'register_missing_subjects' => 1,
                ],
            ]],
            'Invalid type for path "avro_regy.options.register_missing_subjects". Expected "bool", but got "int".'
        );
    }

    public function testSerializersHasToBeSet(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
                'options' => [
                    'register_missing_schemas' => true,
                    'register_missing_subjects' => true,
                ],
            ]],
            'The child config "serializers" under "avro_regy" must be configured.'
        );
    }

    public function testSerializersCanNotBeEmpty(): void
    {
        static::assertConfigurationIsInvalid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
                'options' => [
                    'register_missing_schemas' => true,
                    'register_missing_subjects' => true,
                ],
                'serializers' => [],
            ]],
            'The path "avro_regy.serializers" should have at least 1 element(s) defined.'
        );
    }

    public function testBasicValidConfig(): void
    {
        static::assertConfigurationIsValid(
            [[
                'base_uri' => 'asdf',
                'file_naming_strategy' => 'subject',
                'options' => [
                    'register_missing_schemas' => true,
                    'register_missing_subjects' => true,
                ],
                'serializers' => [
                    'test' => [
                        'schema_dir' => 'test/',
                    ],
                ],
            ]]
        );
    }

    public function testRequestOptionsValidConfig(): void
    {
        static::assertConfigurationIsValid(
            [[
                'base_uri' => 'asdf',
                'request_options' => [
                    'auth' => [
                        'test',
                        'test',
                    ],
                    'timeout' => 30,
                ],
                'file_naming_strategy' => 'subject',
                'options' => [
                    'register_missing_schemas' => true,
                    'register_missing_subjects' => true,
                ],
                'serializers' => [
                    'test' => [
                        'schema_dir' => 'test/',
                    ],
                ],
            ]]
        );
    }
}
