<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\MediaType;

use NoreSources\StringRepresentation;
use NoreSources\Container;

/**
 *
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 *
 */
class MediaType implements MediaTypeInterface, StringRepresentation
{

	/**
	 *
	 * @return string
	 */
	public function getMainType()
	{
		return $this->mainType;
	}

	/**
	 *
	 * @return \NoreSources\MediaType\MediaSubType
	 */
	public function getSubType()
	{
		return $this->subType;
	}

	const ANY = '*';

	public function __construct($type, MediaSubType $subType = null)
	{
		$this->mainType = $type;
		$this->subType = $subType;
	}

	public function __toString()
	{
		if (\is_string($this->mainType) && strlen($this->mainType) && ($this->mainType != self::ANY))
		{
			$s = $this->mainType . '/';
			if ($this->subType instanceof MediaSubType)
				$s .= strval($this->subType);
			else
				$s .= '*';
			return $s;
		}

		return '*/*';
	}

	/**
	 * Get media type of a file or stream
	 *
	 * @param string|resource $media
	 *        	File path or stream
	 * @return \NoreSources\MediaType\MediaType
	 */
	public static function fromMedia($media)
	{
		$type = @mime_content_type($media);

		if ($type === false)
			throw \Exception('Unable to recognize media type');
		elseif ($type == 'text/plain')
		{
			if (is_file($media))
			{
				$x = self::fromFileExtension(pathinfo($media, PATHINFO_EXTENSION));
				if ($x !== false)
					$type = $x;
			}
		}

		return self::fromString($type);
	}

	/**
	 * Get Media type from file extension
	 *
	 * @param string $extension
	 *        	File extension
	 * @return \NoreSources\MediaType\MediaType|false
	 */
	public static function fromFileExtension($extension)
	{
		if (!\is_array(self::$extensions))
			self::$extensions = [
				'css' => 'text/css',
				'html' => 'text/html',
				'htm' => 'text/html',
				'js' => 'text/javascript',
				'json' => 'application/json',
				'xml' => 'text/xml',
				'yaml' => 'text/yaml'
			];

		$mediaTypeString = Container::keyValue(self::$extensions, strtolower($extension), false);
		if (\is_string($mediaTypeString))
			return self::fromString($mediaTypeString);
		return $mediaTypeString;
	}

	/**
	 * Parse a media type string
	 *
	 * @param string $mediaTypeString
	 *        	Mediga type strin
	 * @param boolean $acceptStar
	 *        	Accept the character '*' indicating "any type or subtype" in HTTP Accept header
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaType
	 */
	public static function fromString($mediaTypeString, $acceptStar = false)
	{
		$matches = [];
		$pattern = ($acceptStar ? self::PATTERN_OPTIONAL : self::PATTERN_STRICT);
		if (!\preg_match(chr(1) . $pattern . chr(1) . 'i', $mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString, 'Not a valid media type string');

		$subType = null;
		if (Container::keyExists($matches, 2))
		{
			$facets = explode('.', $matches[2]);
			$syntax = Container::keyValue($matches, 3, null);
			$subType = new MediaSubType($facets, $syntax);
		}

		return new MediaType(Container::keyValue($matches, 1, self::ANY), $subType);
	}

	/**
	 * Get the subtype structured syntax name if any.
	 *
	 * If the subtype does not specify a structured syntax name and if the media main type is "test",
	 * the sybtype name is returned.
	 *
	 * @return string|array|string|NULL
	 */
	public function getStructuredSyntax()
	{
		if (!($this->subType instanceof MediaSubType))
			return null;

		$s = $this->subType->getStructuredSyntax();
		if ($s)
			return $s;

		if ($this->subType->getFacetCount() == 1)
		{
			$facet = $this->subType->getFacet(0);
			if ((strtolower($this->mainType) == 'text') || \in_array($facet, [
				'json',
				'xml'
			]))
				return $facet;
		}

		return null;
	}

	const PATTERN_STRICT = '^([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))/((?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*$';

	const PATTERN_OPTIONAL = '^(?:\*/\*)|(?:([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))/(?:(?:\*)|((?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*))$';

	/**
	 * Media main type
	 *
	 * @var string
	 */
	private $mainType;

	/**
	 *
	 * @var MediaSubType
	 */
	private $subType;

	private static $extensions;
}