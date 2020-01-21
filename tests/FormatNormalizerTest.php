<?php
/**
 * This file is part of the json-normalizer package.
 *
 * Copyright (c) 2020, Thomas Mueller <mimmi20@live.de>
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
use Ergebnis\Json\Normalizer\NormalizerInterface;
use Json\Normalizer\FormatNormalizer;
use PHPUnit\Framework\TestCase;

final class FormatNormalizerTest extends TestCase
{
    /**
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return void
     */
    public function testNormalizeMissingPrettyPrint(): void
    {
        $jsonOptions = JSON_HEX_QUOT;
        $decodedJson = 'Test-Json';
        $encodedJson = '{Test-Json}';

        $jsonEncodeOptions = JsonEncodeOptions::fromInt($jsonOptions);

        $format = new Format($jsonEncodeOptions, Indent::fromString(' '), NewLine::fromString("\n"), false);

        $normalizer = $this->getMockBuilder(NormalizerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $normalizer
            ->expects(self::never())
            ->method('normalize')
            ->with($decodedJson, ($jsonOptions | JSON_PRETTY_PRINT))
            ->willReturn($encodedJson);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('This Normalizer requires the JSON_PRETTY_PRINT option to be set.');

        /* @var Format $format */
        /* @var NormalizerInterface $normalizer */
        new FormatNormalizer($format, $normalizer);
    }
}
