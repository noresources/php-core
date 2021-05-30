<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

namespace NoreSources;

/**
 * An interface to force classes to define the __clone() magic method.
 * 
 * @see https://www.php.net/manual/en/language.oop5.magic.php
 */
interface ClonableInterface
{
	function __clone ();
}
