<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Type;

/**
 * A class implementing FloatRepresentation provides
 * a floating point number representation of a class instance
 */
interface FloatRepresentation
{

	/**
	 *
	 * @return float Float representation of the class instance
	 */
	function getFloatValue();
}
