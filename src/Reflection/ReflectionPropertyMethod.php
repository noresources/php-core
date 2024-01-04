<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

/**
 * A ReflectionProperty that may use ReflectionMethod as setter and getter.
 *
 * @deprecated Use ReflectionService and ReflectionData
 */
class ReflectionPropertyMethod extends \ReflectionProperty
{

	/**
	 *
	 * @param object|string $class
	 *        	Class name or object
	 * @param string $name
	 *        	Property name
	 * @param object $get
	 *        	ReadMethod
	 * @param object $set
	 *        	WriteMethod
	 */
	public function __construct($class, $name, $get = null, $set = null)
	{
		parent::__construct($class, $name);
		$this->getter = $get;
		$this->setter = $set;
	}

	#[\ReturnTypeWillChange]
	public function getValue($object = null)
	{
		if ($this->getter)
			return $this->getter->invoke($object);
		return parent::getValue($object);
	}

	#[\ReturnTypeWillChange]
	public function setValue($object, $value = null)
	{
		if ($this->setter)
			return $this->setter->invoke($object, $value);
		return parent::setValue($object, $value);
	}

	public function setAccessMethods($get = null, $set = null)
	{
		$this->getter = $get;
		$this->setter = $set;
	}

	/**
	 *
	 * @var \ReflectionMethod
	 */
	private $getter;

	/**
	 *
	 * @var \ReflectionMethod
	 */
	private $setter;
}
