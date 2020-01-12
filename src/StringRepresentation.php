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
 * Object have a string representation.
 * This interface is a syntaxic sugar to indicates the object redefines the __toString() magic method
 */
interface StringRepresentation
{

	/**
	 *
	 * @return string The string representation of the class instance
	 */
	function __toString();
}
