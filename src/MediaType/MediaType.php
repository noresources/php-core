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

	use MediaTypeStructuredTextTrait;

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
		return strval($this->mainType) . '/' . strval($this->subType);
	}

	/**
	 * Parse a media type string
	 *
	 * @param string $mediaTypeString
	 *        	Mediga type strin
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaType
	 */
	public static function fromString($mediaTypeString)
	{
		$matches = [];
		if (!\preg_match(chr(1) . self::STRING_PATTERN . chr(1) . 'i', $mediaTypeString, $matches))
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

	const STRING_PATTERN = '^([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))/((?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*$';

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