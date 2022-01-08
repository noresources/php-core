<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Text;

use NoreSources\Container\Container;
use NoreSources\Type\TypeConversionException;

/**
 * Structured syntax text utility
 *
 * @deprecated Use ns-php-data DataSerializationManager
 */
class StructuredText
{

	/**
	 * IIN file format
	 *
	 * @var string
	 */
	const FORMAT_INI = 'ini';

	/**
	 * JSON
	 *
	 * @var string
	 */
	const FORMAT_JSON = 'json';

	/**
	 * URL encoded data
	 *
	 * @see https://tools.ietf.org/html/rfc3986
	 */
	const FORMAT_URL_ENCODED = 'url';

	/**
	 * XML DOM
	 *
	 * @var string
	 */
	const FORMAT_XML = 'xml';

	/**
	 * YAML document
	 *
	 * @var string
	 */
	const FORMAT_YAML = 'yaml';

	/**
	 *
	 * @param string $filename
	 * @param string|null $mediaType
	 *        	File media type. If null, the media type is guessed from file content.
	 * @throws \InvalidArgumentException
	 * @throws TypeConversionException
	 * @return array
	 */
	public static function parseFile($filename, $mediaType = null)
	{
		if (!\file_exists($filename))
			throw new \InvalidArgumentException(
				$filename . ' not found');

		$extension = \strtolower(
			\pathinfo($filename, PATHINFO_EXTENSION));

		switch ($extension)
		{
			case 'yml':
				$extension = self::FORMAT_YAML;
			break;
			case 'jsn':
				$extension = self::FORMAT_JSON;
			break;
			case 'xhtml':
				$extension = self::FORMAT_XML;
		}

		if (!(\is_string($mediaType) && \strlen($mediaType)))
		{
			if (\function_exists('mime_content_type'))
				$mediaType = @mime_content_type($filename);
			elseif (\class_exists('finfo'))
			{
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$mediaType = $finfo->file($filename);
			}
		}

		$format = self::mediaTypeFormat($mediaType);
		if ($format === false)
			$format = $extension;

		foreach ([
			$format,
			$extension
		] as $f)
		{
			switch ($f)
			{
				case self::FORMAT_INI:
					return self::dataFromIni($filename, true);
				case self::FORMAT_JSON:
					return self::dataFromJson(
						\file_get_contents($filename));
				case self::FORMAT_URL_ENCODED:
					return self::dataFromUrlEncoded(
						\file_get_contents($filename));
				case self::FORMAT_YAML:
					return self::dataFromYaml(
						\file_get_contents($filename));
			}
		}

		throw new TypeConversionException('text', 'array',
			'No conversion available for file "' . $filename . '" (' .
			$mediaType . ')');
	}

	/**
	 *
	 * @param string $text
	 *        	URL encoded query string
	 * @return array|string
	 */
	private static function dataFromUrlEncoded($text)
	{
		if (\strpos($text, '=') !== false)
		{
			$data = [];
			\parse_str($text, $data);
			return $data;
		}

		return \urldecode($text);
	}

	/**
	 *
	 * @param string $text
	 *        	Structured text
	 * @param string $format
	 *        	Text format
	 * @throws TypeConversionException
	 * @return mixed
	 */
	public static function parseText($text, $format)
	{
		switch ($format)
		{
			case self::FORMAT_INI:
				return self::dataFromIni($text, false);
			case self::FORMAT_JSON:
				return self::dataFromJson($text);
			case self::FORMAT_URL_ENCODED:
				return self::dataFromUrlEncoded($text);
			case self::FORMAT_YAML:
				return self::dataFromYaml($text);
		}

		throw new TypeConversionException($text, 'array',
			'No conversion available for ' . $format . ' format');
	}

	/**
	 * Find the structured text format from the given media type.
	 *
	 * @param string $mediaType
	 * @return string|false
	 */
	public static function mediaTypeFormat($mediaType)
	{
		static $mediaTypes = [
			'application/textedit' => self::FORMAT_INI,
			'zz-application/zz-winassoc-ini' => self::FORMAT_INI,
			'application/json' => self::FORMAT_JSON,
			'application/x-www-form-urlencoded' => self::FORMAT_URL_ENCODED,
			'application/xml' => self::FORMAT_XML,
			'text/xml' => self::FORMAT_XML,
			'text/yaml' => self::FORMAT_YAML
		];

		$format = Container::keyValue($mediaTypes, \strval($mediaType),
			false);
		if ($format !== false)
			return $format;

		if (\preg_match(
			chr(1) . self::STRUCTURED_SYNTAX_SUFFIX_PATTERN . '$' .
			chr(1), $mediaType, $m))
		{
			$format = $m[1];
			$format = Container::keyValue($mediaType, $mediaType, false);
		}

		return $format;
	}

	/**
	 *
	 * @param string $format
	 *        	Structured text format
	 * @param boolean $allAlternatives
	 *        	If true, returns all possible media types.
	 * @return array|string|false
	 */
	public static function formatMediaType($format,
		$allAlternatives = false)
	{
		static $formats = [
			/**
			 * From Wikipedia : https://en.wikipedia.org/wiki/INI_file
			 */
			self::FORMAT_INI => [
				'application/textedit',
				'zz-application/zz-winassoc-ini'
			],
			self::FORMAT_JSON => [
				'application/json'
			],
			self::FORMAT_URL_ENCODED => [
				'application/x-www-form-urlencoded'
			],
			/**
			 * From Wikipedia: https://en.wikipedia.org/wiki/XML_and_MIME
			 */
			self::FORMAT_XML => [
				'application/xml',
				'text/xml'
			],

			/**
			 * Not registered
			 */
			self::FORMAT_YAML => [
				'text/yaml'
			]
		];

		$entry = Container::keyValue($formats, $format, false);
		if ($entry === false)
			return $entry;

		if ($allAlternatives)
			return $entry;

		list ($_, $mediaType) = Container::first($entry);
		return $mediaType;
	}

	/**
	 * RFC 6838 Media type structured syntax suffix pattern.
	 *
	 * @var string
	 */
	const STRUCTURED_SYNTAX_SUFFIX_PATTERN = '\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))';

	/**
	 *
	 * @param string $source
	 * @param boolean $isFile
	 * @throws StructuredTextSyntaxErrorException::
	 * @return array
	 */
	private static function dataFromIni($source, $isFile = true)
	{
		if ($isFile)
			$data = @parse_ini_file($source, true);
		else
			$data = @parse_ini_string($source, true);
		if ($data === false)
		{
			$error = \error_get_last();
			throw new StructuredTextSyntaxErrorException(
				self::FORMAT_INI,
				Container::keyValue($error, 'message', null),
				Container::keyValue($error, 'line', null),
				Container::keyValue($error, 'type', null));
		}
		return $data;
	}

	/**
	 *
	 * @param string $text
	 * @throws StructuredTextSyntaxErrorException::
	 * @return array
	 */
	private static function dataFromJson($text)
	{
		$data = @json_decode($text, true);
		$code = json_last_error();
		if ($code != JSON_ERROR_NONE)
			throw new StructuredTextSyntaxErrorException(
				self::FORMAT_JSON, json_last_error_msg(), null, $code);

		return $data;
	}

	/**
	 *
	 * @param string $text
	 * @throws \Exception
	 * @throws StructuredTextSyntaxErrorException::
	 * @return array
	 */
	private static function dataFromYaml($text)
	{
		if (!\function_exists('yaml_parse'))
			throw new \Exception('YAML extension not available');

		$data = @\yaml_parse($text);
		if ($data === false)
			throw new StructuredTextSyntaxErrorException(
				self::FORMAT_YAML);

		return $data;
	}
}