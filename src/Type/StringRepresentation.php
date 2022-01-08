<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Type;

/**
 * A class implementing StringRepresentation provides a
 * string representation of a class instance
 *
 * This interface is a syntaxic sugar to indicates the object redefines the __toString() magic
 * method.
 *
 * This interface is obsolet in PHP 8 which introduces the implicit Stringable interface.
 *
 * @see https://www.php.net/manual/en/class.stringable.php
 */
interface StringRepresentation
{

	/**
	 *
	 * @return string The string representation of the class instance
	 */
	function __toString();
}
