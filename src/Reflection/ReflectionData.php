<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use ReflectionType;

class ReflectionData implements \Reflector
{

	public function __construct($class, $name,
		\ReflectionProperty $reflectionProperty = null,
		\ReflectionMethod $readMethod = null,
		\ReflectionMethod $writeMethod = null)
	{
		if ($reflectionProperty)
			$this->reflectionProperty = $reflectionProperty;
		else
		{
			$this->readOnlyProperties = [
				'name' => $name,
				'class' => $class
			];
		}

		if ($readMethod)
			$this->readMethod = $readMethod;
		if ($writeMethod)
			$this->writeMethod = $writeMethod;
	}

	public static function export($class, $name, $return)
	{
		return \ReflectionProperty::export($class, $name, $return);
	}

	/**
	 * Get read-only properties
	 *
	 * @param string $property
	 *        	Property name
	 * @throws \RuntimeException
	 * @return mixed Property value
	 */
	public function __get($property)
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->$property;
		if (!isset($this->readOnlyProperties))
			throw new \RuntimeException('No property $' . $property);
		if (!\array_key_exists($property, $this->readOnlyProperties))
			throw new \RuntimeException('No property $' . $property);
		return $this->readOnlyProperties[$property];
	}

	public function __toString()
	{
		if (isset($this->reflectionProperty))
			return \strval($this->reflectionProperty);
		return $this->class . '::$' . $this->name;
	}

	/**
	 * Transfer call to inner ReflectionProperty object if any.
	 *
	 * @param string $name
	 * @param array $args
	 * @throws \RuntimeException
	 * @return mixed
	 */
	public function __call($name, $args = [])
	{
		if (isset($this->reflectionProperty))
			return \call_user_func_array(
				[
					$this->reflectionProperty,
					$name
				], $args);
		throw new \RuntimeException(
			self::class . '::' . $name . ' (' .
			\implode(', ', \array_map('\gettype', $args)) .
			') is not available.');
	}

	/**
	 *
	 * @param \ReflectionMethod $method
	 */
	public function setReadMethod(\ReflectionMethod $method = null)
	{
		$this->readMethod = $method;
	}

	/**
	 *
	 * @param \ReflectionMethod $method
	 */
	public function setWriteMethod(\ReflectionMethod $method = null)
	{
		$this->writeMethod = $method;
	}

	/**
	 *
	 * @param objec $object
	 *        	Object to get property value from.
	 * @throws \ReflectionException
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function getValue($object = null)
	{
		if (isset($this->readMethod))
			return $this->readMethod->invoke($object);

		if (!isset($this->reflectionProperty))
			throw new \ReflectionException(
				$this->class . ' class $' . $this->name .
				' property is not readable');

		return $this->reflectionProperty->getValue($object);
	}

	#[\ReturnTypeWillChange]
	public function setValue($object, $value = null)
	{
		if ($this->writeMethod)
			return $this->writeMethod->invoke($object, $value);
		if (!isset($this->reflectionProperty))
			throw new \ReflectionException(
				$this->class . ' class $' . $this->name .
				' property is not writable');
		$this->reflectionProperty->setValue($object, $value);
	}

	/**
	 *
	 * @return boolean
	 */
	public function isPublic()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->isPublic();
		if ($this->readMethod)
			return $this->readMethod->isPublic();
		if ($this->writeMethod)
			return $this->writeMethod->isPublic();
		return false;
	}

	public function isProtected()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->isProtected();
		if ($this->readMethod)
			return $this->readMethod->isProtected();
		if ($this->writeMethod)
			return $this->writeMethod->isProtected();
		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isPrivate()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->isPrivate();
		if ($this->readMethod)
			return $this->readMethod->isPrivate();
		if ($this->writeMethod)
			return $this->writeMethod->isPrivate();
		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isStatic()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->isStatic();
		if ($this->readMethod)
			return $this->readMethod->isStatic();
		if ($this->writeMethod)
			return $this->writeMethod->isStatic();
		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isDefault()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->isDefault();
		return false;
	}

	/**
	 *
	 * @param object $object
	 *        	Object
	 * @return boolean
	 */
	public function isInitialized($object = null)
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->isInitialized($object);
		return false;
	}

	/**
	 * Get documentation of inner ReflectionProperty or fallback to the documentation of the read
	 * method.
	 *
	 * @return string
	 */
	public function getDocComment()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->getDocComment();
		if ($this->readMethod)
			return $this->readMethod->getDocComment();
		return '';
	}

	public function setAccessible($accessible)
	{
		if (isset($this->reflectionProperty))
			$this->reflectionProperty->setAccessible($accessible);
	}

	/**
	 * Get property type or return type of the read method.
	 *
	 * @return ReflectionType|NULL
	 */
	public function getType()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->getType();
		if ($this->readMethod)
			return $this->readMethod->getReturnType();
		return null;
	}

	/**
	 * Indicates if property has a type
	 *
	 * @return boolean
	 */
	public function hasType()
	{
		if (isset($this->reflectionProperty))
			return $this->reflectionProperty->hasType();
		if ($this->readMethod)
			return $this->readMethod->hasReturnType();
		return false;
	}

	/**
	 *
	 * @var \ReflectionProperty
	 */
	private $reflectionProperty;

	/**
	 *
	 * @var array
	 */
	private $readOnlyProperties;

	/**
	 *
	 * @var \ReflectionMethod
	 */
	private $readMethod;

	/**
	 *
	 * @var \ReflectionMethod
	 */
	private $writeMethod;
}
