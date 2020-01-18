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

	/**
	 * Get the subtype structured syntax name if any.
	 *
	 * If the subtype does not specify a structured syntax name and if the media main type is "test",
	 * the sybtype name is returned.
	 *
	 * @return string|array|string|NULL
	 */
	function getStructuredSyntax();
}