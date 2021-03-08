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

use Ergebnis\Json\Normalizer\Format\Format;
use Ergebnis\Json\Normalizer\Format\Indent;
use Ergebnis\Json\Normalizer\Format\JsonEncodeOptions;
use Ergebnis\Json\Normalizer\Format\NewLine;
use Ergebnis\Json\Normalizer\Json;
use Json\Normalizer\FormatNormalizer;
use JsonClass\JsonInterface;
use PHPUnit\Framework\TestCase;

final class FormatNormalizerTest extends TestCase
{
    /**
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \UnexpectedValueException
     *
     * @return void
     */
    public function testNormalizeMissingPrettyPrint(): void
    {
        $format = new Format(
            JsonEncodeOptions::fromInt(JSON_HEX_QUOT),
            Indent::fromString(' '),
            NewLine::fromString("\n"),
            false
        );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('This Normalizer requires the JSON_PRETTY_PRINT option to be set.');

        /* @var Format $format */
        new FormatNormalizer($format);
    }

    /**
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \PHPUnit\Framework\Exception
     * @throws \UnexpectedValueException
     * @throws \ReflectionException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodedException
     * @throws \Ergebnis\Json\Normalizer\Exception\NormalizedInvalidAccordingToSchemaException
     * @throws \Ergebnis\Json\Normalizer\Exception\OriginalInvalidAccordingToSchemaException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeReadException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolvedException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesDocumentWithInvalidMediaTypeException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesInvalidJsonDocumentException
     * @throws \ExceptionalJSON\EncodeErrorException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testNormalizeWithPrettyPrint(): void
    {
        $jsonOptions                = JSON_HEX_QUOT | JSON_PRETTY_PRINT;
        $decodedJson                = new \stdClass();
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

        /** @var Format $format */
        $object = new FormatNormalizer($format);

        $jsonClass = $this->getMockBuilder(JsonInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonClass
            ->expects(self::once())
            ->method('encode')
            ->with($decodedJson, $jsonOptions)
            ->willReturn($encodedJson2);

        $refProperty = new \ReflectionProperty($object, 'jsonClass');
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $jsonClass);

        $normalized = $object->normalize($json);

        self::assertSame($expected, $normalized->encoded());
    }
}
