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

/*
 * Object have a integer representation
 */
interface IntegerRepresentation
{

	/**
	 *
	 * @return integer Integer representation of the class instance
	 */
	function getIntegerValue();
}
