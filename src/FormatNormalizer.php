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
namespace Json\Normalizer;

use Ergebnis\Json\Normalizer\Format\Format;
use Ergebnis\Json\Normalizer\Format\Indent;
use Ergebnis\Json\Normalizer\Format\NewLine;
use Ergebnis\Json\Normalizer\Json;
use Ergebnis\Json\Normalizer\NormalizerInterface;
use JsonClass\Json as JsonClass;

final class FormatNormalizer implements NormalizerInterface
{
    private const PLACE_HOLDER = '$ni$';

    /**
     * @var Format
     */
    private $format;

    /**
     * @var JsonClass
     */
    private $jsonClass;

    /**
     * @param Format $format
     *
     * @throws \UnexpectedValueException
     */
    public function __construct(Format $format)
    {
        $this->format = $format;

        $this->checkPrettyPrint();

        $this->jsonClass = new JsonClass();
    }

    /**
     * @param Json $json
     *
     * @throws \ExceptionalJSON\EncodeErrorException                                                        When the encode operation fails
     * @throws \Ergebnis\Json\Normalizer\Exception\NormalizedInvalidAccordingToSchemaException
     * @throws \Ergebnis\Json\Normalizer\Exception\OriginalInvalidAccordingToSchemaException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeReadException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolvedException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesDocumentWithInvalidMediaTypeException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesInvalidJsonDocumentException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodedException
     *
     * @return Json
     */
    public function normalize(Json $json): Json
    {
        $encodedWithJsonEncodeOptions = $this->jsonClass->encode(
            $json->decoded(),
            $this->format->jsonEncodeOptions()->value()
        );

        $json       = Json::fromEncoded($encodedWithJsonEncodeOptions);
        $oldNewline = (string) NewLine::fromJson($json);

        assert('' !== $oldNewline);

        $lines = explode(
            $oldNewline,
            rtrim($json->encoded())
        );

        assert(is_array($lines));

        $newNewline     = (string) $this->format->newLine();
        $oldIndent      = (string) Indent::fromJson($json);
        $newIndent      = (string) $this->format->indent();
        $formattedLines = [];

        foreach ($lines as $line) {
            if (1 > preg_match('/^(\s+)(\S.*)/', $line, $matches)) {
                $formattedLines[] = $line;
                continue;
            }

            $tempLine = str_replace([$oldIndent, self::PLACE_HOLDER], [self::PLACE_HOLDER, $newIndent], $matches[1]);

            $formattedLines[] = $tempLine . $matches[2];
        }

        $content = implode($newNewline, $formattedLines);

        if ($this->format->hasFinalNewLine()) {
            $content .= $newNewline;
        }

        return Json::fromEncoded($content);
    }

    /**
     * @throws \UnexpectedValueException
     *
     * @return void
     */
    private function checkPrettyPrint(): void
    {
        $jsonOptions = $this->format->jsonEncodeOptions()->value();
        $prettyPrint = (bool) ($jsonOptions & JSON_PRETTY_PRINT);

        if (!$prettyPrint) {
            throw new \UnexpectedValueException('This Normalizer requires the JSON_PRETTY_PRINT option to be set.');
        }
    }
}
