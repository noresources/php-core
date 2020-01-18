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

class MediaRange implements MediaTypeInterface, StringRepresentation
{

	const ANY = '*';

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
	 * @var \NoreSources\MediaType\MediaType|"*"
	 */
	private $mainType;

	/**
	 *
	 * @var \NoreSources\MediaType\MediaSubType|"*"
	 */
	private $subType;
}