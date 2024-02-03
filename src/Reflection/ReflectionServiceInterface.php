<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use ReflectionClass;
use ReflectionProperty;

/**
 * Reflection utility service
 */
interface ReflectionServiceInterface
{

	/**
	 * Expose non-public prpeerties when retrieving property values.
	 *
	 * @var integer
	 */
	const EXPOSE_HIDDEN_PROPERTY = 0x01;

	/**
	 * Require write permission to value.
	 *
	 *
	 * @var integer
	 */
	const WRITABLE = 0x02;

	/**
	 * Require public value access
	 *
	 *
	 *
	 * @var integer
	 */
	const READABLE = 0x04;

	/**
	 * Require full access to value.
	 *
	 * @var integer
	 */
	const RW = self::READABLE | self::WRITABLE;

	/**
	 * Expose private properties of parent classes
	 *
	 *
	 * @var integer
	 */
	const EXPOSE_INHERITED_PROPERTY = 0x08;

	/**
	 * Use class instance method to alter property value.
	 *
	 * @var integer
	 */
	const ALLOW_WRITE_METHOD = 0x20;

	/**
	 * Use a class instance method to read access property value.
	 *
	 * When retrieving non-public property value.
	 * Invoke method matching getter naming convention if any.
	 *
	 * @var integer
	 */
	const ALLOW_READ_METHOD = 0x40;

	/**
	 * Use class instance methods to set and get values.
	 *
	 * @var integer
	 */
	const ALLOW_RW_METHODS = (self::ALLOW_READ_METHOD |
		self::ALLOW_WRITE_METHOD);

	/**
	 * When assigning property value.
	 * Always use mettong matching getter naming convention if possible.
	 *
	 * @var integer
	 */
	const FORCE_WRITE_METHOD = (0x200 | self::ALLOW_WRITE_METHOD);

	/**
	 * When retrieving property value.
	 * Always use mettong matching getter naming convention if possible.
	 *
	 * @var integer
	 */
	const FORCE_READ_METHOD = (0x400 | self::ALLOW_READ_METHOD);

	/**
	 * Force use of setter and getter methods even if property has public access.
	 *
	 * @var integer
	 */
	const FORCE_RW_METHODS = (self::FORCE_READ_METHOD |
		self::FORCE_WRITE_METHOD);

	/**
	 * Get reflection class for the given class name or object.
	 *
	 * @param string|object $classNameOrObject
	 *        	Class name or Class instance
	 * @return \ReflectionClass
	 */
	function getReflectionClass($classNameOrObject);

	/**
	 *
	 * @param ReflectionClass|object|string $class
	 *        	Class, class name or object
	 * @param string $property
	 *        	Property name
	 * @param number $flags
	 *        	Property requirements and options.
	 * @return ReflectionProperty|NULL A ReflectionProperty that honor $flags requirements.$this
	 *         If $flags does not expect any requirement, NULL may be returned .
	 *         If no ReflectionProperty can be provided the requirements expected by $flags, an
	 *         error is thrown.
	 *
	 */
	function getReflectionProperty($class, $property, $flags = 0);

	/**
	 *
	 * @param object $object
	 *        	Class instance
	 * @param \ReflectionProperty|string $property
	 *        	Property of $object
	 * @param number $flags
	 *        	Option flags
	 * @return mixed Property value if it can be retrieved or NULL otherwise
	 */
	function getPropertyValue($object, $property, $flags = 0);

	/**
	 *
	 * @param object $object
	 *        	Class instance
	 * @param number $flags
	 *        	Option flags
	 * @return mixed[] Dictionary of prperty values.
	 */
	function getPropertyValues($object, $flags = 0);

	/**
	 *
	 * @param object $object
	 *        	Output object
	 * @param array $values
	 *        	Property values
	 * @param number $flags
	 *        	ReflectionProperty flags
	 */
	function setPropertyValues($object, $values, $flags = 0);

	/**
	 * Find the method matching getter naming convention for the given property name.
	 *
	 * @param \ReflectionClass $class
	 *        	Class
	 * @param string $propertyName
	 *        	Property name
	 * @return \ReflectionMethod|NULL
	 */
	function findReadMethodForProperty(\ReflectionClass $class,
		$propertyName);

	/**
	 * Find the method matching setter naming convention for the given property name.
	 *
	 * @param \ReflectionClass $class
	 *        	Class
	 * @param string $propertyName
	 *        	Property name
	 * @return \ReflectionMethod|NULL
	 */
	function findWriteMethodForProperty(\ReflectionClass $class,
		$propertyName);
}