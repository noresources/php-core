<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

/**
 * An interface to create ReflectionProperty with
 * certain capabilities.
 */
interface ReflectionPropertyFactoryInterface
{

	/**
	 *
	 * @var integer
	 */
	const MODE_AUTO = 0;

	/**
	 * Ensure ReflectionProperty will be able to return the property value
	 *
	 * @var number
	 */
	const MODE_READ = 0x01;

	/**
	 * Ensure ReflectionProperty will be able to set property value
	 *
	 * @var number
	 */
	const MODE_WRITE = 0x02;

	/**
	 * Ensure ReflectionProperty will have full access to the property.
	 *
	 * @var number
	 */
	const MODE_RW = 0x03;

	/**
	 *
	 * @param string|object $class
	 *        	Owning class name or object
	 * @param unknown $name
	 *        	Property name
	 * @param integer $mode
	 *        	Mandatory access mode.
	 * @return \ReflectionProperty
	 */
	function createReflectionProperty($class, $name,
		$mode = self::MODE_AUTO);
}
