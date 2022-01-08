<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Type;

/**
 * A class implementing BooleanRepresentation provides
 * a boolean evaluation of a class instance
 */
interface BooleanRepresentation
{

	/**
	 *
	 * @return boolean evaluation representation of the class instance
	 */
	function getBooleanValue();
}
