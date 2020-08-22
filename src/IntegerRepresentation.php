<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

/*
 * A class implementing IntegerRepresentation provides a
 * integer representation of a class instance
 */
interface IntegerRepresentation
{

	/**
	 *
	 * @return integer Integer representation of the class instance
	 */
	function getIntegerValue();
}
