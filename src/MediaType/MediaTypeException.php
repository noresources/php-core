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

class MediaTypeException extends \Exception
{

	/**
	 *
	 * @var \NoreSources\MediaType\MediaType|\NoreSources\MediaType\MediaSubType|string
	 */
	public $type;

	/**
	 *
	 * @param MediaType|MediaSubType|string $type
	 * @param string $message
	 */
	public function __construct($type, $message)
	{
		parent::__construct('[' . strval($type) . '] ' . $message);
	}
}