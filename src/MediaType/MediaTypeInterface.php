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

interface MediaTypeInterface
{

	/**
	 *
	 * @return string
	 */
	function getMainType();

	/**
	 *
	 * @return \NoreSources\MediaType\MediaSubType|string
	 */
	function getSubType();
}