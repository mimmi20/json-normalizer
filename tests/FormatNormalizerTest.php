<?php
/**
 * This file is part of the json-normalizer package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace JsonTest\Normalizer;

use Ergebnis\Json\Normalizer\Exception\InvalidIndentStringException;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodedException;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException;
use Ergebnis\Json\Normalizer\Exception\NormalizedInvalidAccordingToSchemaException;
use Ergebnis\Json\Normalizer\Exception\OriginalInvalidAccordingToSchemaException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeReadException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolvedException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesDocumentWithInvalidMediaTypeException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesInvalidJsonDocumentException;
use Ergebnis\Json\Normalizer\Format\Format;
use Ergebnis\Json\Normalizer\Format\Indent;
use Ergebnis\Json\Normalizer\Format\JsonEncodeOptions;
use Ergebnis\Json\Normalizer\Format\NewLine;
use Ergebnis\Json\Normalizer\Json;
use ExceptionalJSON\EncodeErrorException;
use Json\Normalizer\FormatNormalizer;
use JsonClass\JsonInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

use function assert;

use const JSON_HEX_QUOT;
use const JSON_PRETTY_PRINT;

final class FormatNormalizerTest extends TestCase
{
    /**
     * @throws InvalidJsonEncodeOptionsException
     * @throws InvalidIndentStringException
     * @throws InvalidNewLineStringException
     * @throws UnexpectedValueException
     */
    public function testNormalizeMissingPrettyPrint(): void
    {
        $format = new Format(
            JsonEncodeOptions::fromInt(JSON_HEX_QUOT),
            Indent::fromString(' '),
            NewLine::fromString("\n"),
            false
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('This Normalizer requires the JSON_PRETTY_PRINT option to be set.');

        assert($format instanceof Format);
        new FormatNormalizer($format);
    }

    /**
     * @throws InvalidIndentStringException
     * @throws InvalidNewLineStringException
     * @throws Exception
     * @throws UnexpectedValueException
     * @throws ReflectionException
     * @throws InvalidJsonEncodeOptionsException
     * @throws InvalidJsonEncodedException
     * @throws NormalizedInvalidAccordingToSchemaException
     * @throws OriginalInvalidAccordingToSchemaException
     * @throws SchemaUriCouldNotBeReadException
     * @throws SchemaUriCouldNotBeResolvedException
     * @throws SchemaUriReferencesDocumentWithInvalidMediaTypeException
     * @throws SchemaUriReferencesInvalidJsonDocumentException
     * @throws EncodeErrorException
     * @throws InvalidArgumentException
     */
    public function testNormalizeWithPrettyPrint(): void
    {
        $jsonOptions                = JSON_HEX_QUOT | JSON_PRETTY_PRINT;
        $decodedJson                = new stdClass();
        $decodedJson->{'Test-Json'} = false;
        $encodedJson                = '{"Test-Json": false}';
        $encodedJson2               = "{\n    \"Test-Json\": false\n}";
        $expected                   = "{\r\n \"Test-Json\": false\r\n}\r\n";

        $json   = Json::fromEncoded($encodedJson);
        $format = new Format(
            JsonEncodeOptions::fromInt($jsonOptions),
            Indent::fromString(' '),
            NewLine::fromString("\r\n"),
            true
        );

        assert($format instanceof Format);
        $object = new FormatNormalizer($format);

        $jsonClass = $this->getMockBuilder(JsonInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonClass
            ->expects(self::once())
            ->method('encode')
            ->with($decodedJson, $jsonOptions)
            ->willReturn($encodedJson2);

        $refProperty = new ReflectionProperty($object, 'jsonClass');
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $jsonClass);

        $normalized = $object->normalize($json);

        self::assertSame($expected, $normalized->encoded());
    }

    /**
     * @throws InvalidIndentStringException
     * @throws InvalidNewLineStringException
     * @throws Exception
     * @throws UnexpectedValueException
     * @throws ReflectionException
     * @throws InvalidJsonEncodeOptionsException
     * @throws InvalidJsonEncodedException
     * @throws NormalizedInvalidAccordingToSchemaException
     * @throws OriginalInvalidAccordingToSchemaException
     * @throws SchemaUriCouldNotBeReadException
     * @throws SchemaUriCouldNotBeResolvedException
     * @throws SchemaUriReferencesDocumentWithInvalidMediaTypeException
     * @throws SchemaUriReferencesInvalidJsonDocumentException
     * @throws EncodeErrorException
     * @throws InvalidArgumentException
     */
    public function testNormalizeWithPrettyPrint2(): void
    {
        $jsonOptions                = JSON_HEX_QUOT | JSON_PRETTY_PRINT;
        $decodedJson                = new stdClass();
        $decodedJson->{'Test-Json'} = false;
        $encodedJson                = '{"Test-Json": false}';
        $encodedJson2               = "{\n    \"Test-Json\": false\n}\n\n";
        $expected                   = "{\r\n \"Test-Json\": false\r\n}\r\n";

        $json   = Json::fromEncoded($encodedJson);
        $format = new Format(
            JsonEncodeOptions::fromInt($jsonOptions),
            Indent::fromString(' '),
            NewLine::fromString("\r\n"),
            true
        );

        assert($format instanceof Format);
        $object = new FormatNormalizer($format);

        $jsonClass = $this->getMockBuilder(JsonInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonClass
            ->expects(self::once())
            ->method('encode')
            ->with($decodedJson, $jsonOptions)
            ->willReturn($encodedJson2);

        $refProperty = new ReflectionProperty($object, 'jsonClass');
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $jsonClass);

        $normalized = $object->normalize($json);

        self::assertSame($expected, $normalized->encoded());
    }

    /**
     * @throws InvalidIndentStringException
     * @throws InvalidNewLineStringException
     * @throws Exception
     * @throws UnexpectedValueException
     * @throws ReflectionException
     * @throws InvalidJsonEncodeOptionsException
     * @throws InvalidJsonEncodedException
     * @throws NormalizedInvalidAccordingToSchemaException
     * @throws OriginalInvalidAccordingToSchemaException
     * @throws SchemaUriCouldNotBeReadException
     * @throws SchemaUriCouldNotBeResolvedException
     * @throws SchemaUriReferencesDocumentWithInvalidMediaTypeException
     * @throws SchemaUriReferencesInvalidJsonDocumentException
     * @throws EncodeErrorException
     * @throws InvalidArgumentException
     */
    public function testNormalizeWithPrettyPrint3(): void
    {
        $jsonOptions                 = JSON_HEX_QUOT | JSON_PRETTY_PRINT;
        $decodedJson                 = new stdClass();
        $decodedJson->{'Test-Json'}  = false;
        $decodedJson->{'Test-Json2'} = '    ';
        $encodedJson                 = '{"Test-Json": false,"Test-Json2": "    "}';
        $encodedJson2                = "{\n    \"Test-Json\": false,\n    \"Test-Json2\": \"    \"\n}\n\n";
        $expected                    = "{\r\n \"Test-Json\": false,\r\n \"Test-Json2\": \"    \"\r\n}\r\n";

        $json   = Json::fromEncoded($encodedJson);
        $format = new Format(
            JsonEncodeOptions::fromInt($jsonOptions),
            Indent::fromString(' '),
            NewLine::fromString("\r\n"),
            true
        );

        assert($format instanceof Format);
        $object = new FormatNormalizer($format);

        $jsonClass = $this->getMockBuilder(JsonInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonClass
            ->expects(self::once())
            ->method('encode')
            ->with($decodedJson, $jsonOptions)
            ->willReturn($encodedJson2);

        $refProperty = new ReflectionProperty($object, 'jsonClass');
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $jsonClass);

        $normalized = $object->normalize($json);

        self::assertSame($expected, $normalized->encoded());
    }
}
