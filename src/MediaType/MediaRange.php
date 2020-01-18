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

class MediaRange implements MediaTypeInterface, StringRepresentation
{
	use MediaTypeStructuredTextTrait;

	const ANY = '*';

	const STRING_PATTERN = '^(?:\*/\*)|(?:([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))/(?:(?:\*)|((?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*))$';

	public function __construct($type = self::ANY, $subType = self::ANY)
	{
		$this->mainType = $type;
		$this->subType = $subType;
	}

	public function getMainType()
	{
		return $this->mainType;
	}

	public function getSubType()
	{
		return $this->subType;
	}

	public function __toString()
	{
		return $this->mainType . '/' . strval($this->subType);
	}

	/**
	 *
	 * @param string $mediaTypeString
	 *        	Media range string
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaRange
	 */
	public static function fromString($mediaTypeString)
	{
		$matches = [];
		if (!\preg_match(chr(1) . self::STRING_PATTERN . chr(1) . 'i', $mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString, 'Not a valid media range string');

		$subType = self::ANY;
		if (Container::keyExists($matches, 2))
		{
			$facets = explode('.', $matches[2]);
			$syntax = Container::keyValue($matches, 3, null);
			$subType = new MediaSubType($facets, $syntax);
		}

		return new MediaRange(Container::keyValue($matches, 1, self::ANY), $subType);
	}

	/**
	 *
	 * @var \NoreSources\MediaType\MediaType|"*"
	 */
	private $mainType;

	/**
	 *
	 * @var \NoreSources\MediaType\MediaSubType|"*"
	 */
	private $subType;
}