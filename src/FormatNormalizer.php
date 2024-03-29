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

namespace Json\Normalizer;

use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodedException;
use Ergebnis\Json\Normalizer\Format\Format;
use Ergebnis\Json\Normalizer\Format\Indent;
use Ergebnis\Json\Normalizer\Format\NewLine;
use Ergebnis\Json\Normalizer\Json;
use Ergebnis\Json\Normalizer\NormalizerInterface;
use JsonClass\EncodeErrorException;
use JsonClass\Json as JsonClass;
use JsonClass\JsonInterface;
use UnexpectedValueException;

use function array_key_exists;
use function assert;
use function explode;
use function implode;
use function is_array;
use function is_bool;
use function is_string;
use function mb_strpos;
use function preg_match;
use function rtrim;
use function str_replace;

use const JSON_PRETTY_PRINT;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class FormatNormalizer implements NormalizerInterface
{
    private const PLACE_HOLDER = '$ni$';

    private Format $format;

    private JsonInterface $jsonClass;

    /**
     * @throws UnexpectedValueException
     */
    public function __construct(Format $format)
    {
        $this->format = $format;

        $this->checkPrettyPrint();

        $this->jsonClass = new JsonClass();
    }

    /**
     * @throws EncodeErrorException        When the encode operation fails
     * @throws InvalidJsonEncodedException
     */
    public function normalize(Json $json): Json
    {
        $encodedWithJsonEncodeOptions = $this->jsonClass->encode(
            $json->decoded(),
            $this->format->jsonEncodeOptions()->value()
        );

        $json       = Json::fromEncoded($encodedWithJsonEncodeOptions);
        $oldNewline = (string) NewLine::fromJson($json);

        assert(is_string($oldNewline));
        assert('' !== $oldNewline);

        $lines = explode(
            $oldNewline,
            rtrim($json->encoded())
        );

        assert(is_array($lines));

        $newNewline = (string) $this->format->newLine();
        assert(is_string($newNewline));

        $oldIndent = (string) Indent::fromJson($json);
        assert(is_string($oldIndent));

        $newIndent = (string) $this->format->indent();
        assert(is_string($newIndent));

        $formattedLines = [];
        $matches        = [];

        foreach ($lines as $line) {
            if (!preg_match('/^(?P<ident>\s+)(\S.*)/', $line, $matches)) {
                $formattedLines[] = $line;
                continue;
            }

            assert(array_key_exists('ident', $matches));
            assert(is_string($matches['ident']));

            $tempLine = str_replace($oldIndent, self::PLACE_HOLDER, $matches['ident']);

            assert(is_string($tempLine));
            assert(false === mb_strpos($tempLine, $oldIndent));
            assert(false !== mb_strpos($tempLine, self::PLACE_HOLDER));

            $tempLine = str_replace(self::PLACE_HOLDER, $newIndent, $tempLine);

            assert(false === mb_strpos($tempLine, self::PLACE_HOLDER));

            $formattedLines[] = $tempLine . $matches[2];
        }

        $content = implode($newNewline, $formattedLines);

        if ($this->format->hasFinalNewLine()) {
            $content .= $newNewline;
        }

        return Json::fromEncoded($content);
    }

    /**
     * @throws UnexpectedValueException
     */
    private function checkPrettyPrint(): void
    {
        $jsonOptions = $this->format->jsonEncodeOptions()->value();
        $prettyPrint = (bool) ($jsonOptions & JSON_PRETTY_PRINT);
        assert(is_bool($prettyPrint));

        if (!$prettyPrint) {
            throw new UnexpectedValueException('This Normalizer requires the JSON_PRETTY_PRINT option to be set.');
        }
    }
}
