<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use NoreSources\SingletonTrait;
use NoreSources\Container\Container;
use NoreSources\Reflection\Traits\ReflectionServiceBasicNamingConventionTrait;
use NoreSources\Reflection\Traits\ReflectionServicePropertyValueTrait;
use NoreSources\Reflection\Traits\ReflectionServiceReflectionPropertyTrait;

/**
 * Default implementation of ReflectionServiceInterface
 */
class ReflectionService implements ReflectionServiceInterface
{

	use ReflectionServiceBasicNamingConventionTrait;
	use ReflectionServicePropertyValueTrait;
	use ReflectionServiceReflectionPropertyTrait;
	use SingletonTrait;

	public function __construct()
	{
		$this->classCache = [];
	}

	/**
	 * Get reflection class for the given class name or object.
	 *
	 * @param string|object $classNameOrObject
	 *        	Class name or Class instance
	 * @return \ReflectionClass
	 */
	public function getReflectionClass($classNameOrObject)
	{
		$name = $classNameOrObject;
		if (\is_object($classNameOrObject))
			$name = \get_class($classNameOrObject);
		$key = \strtolower($name);
		$class = Container::keyValue($this->classCache, $key);
		if ($class)
			return $class;
		$class = new \ReflectionClass($name);
		$this->classCache[$key] = $class;
		return $class;
	}

	/**
	 * ReflectionClass cache
	 *
	 * @var array
	 */
	private $classCache;
}
