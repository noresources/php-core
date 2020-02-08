<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

/**
 * A class implementing ArrayRepresentation provides
 * an array representation of a class instance.
 */
interface ArrayRepresentation
{

	/**
	 *
	 * @return array Array representation of the class instance
	 */
	function getArrayCopy();
}
