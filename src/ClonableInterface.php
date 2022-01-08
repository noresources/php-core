<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources;

/**
 * Provide an interface that allow to get a deep clone of an object
 *
 * @see https://www.php.net/manual/en/language.oop5.magic.php
 */
interface ClonableInterface
{

	/**
	 * PHP magic method
	 *
	 * @return $this
	 */
	function __clone();

	/**
	 * Create clone of the instance
	 *
	 * @param boolean $deep
	 *        	If FALSE, the method MUST produce the same object than the __clone() magic
	 *        	method. If TRUE, the method MAY return a deep copy of the instance.
	 * @return $this Instance clone
	 *
	 */
	function newClone($deep = false);
}
