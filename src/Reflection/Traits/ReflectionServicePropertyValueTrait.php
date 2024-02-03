<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection\Traits;

use NoreSources\Reflection\ReflectionService;
use NoreSources\Reflection\ReflectionServiceInterface;
use ReflectionClass;

/**
 * Implements methods of ReflectionServiceInterface related to property values
 */
trait ReflectionServicePropertyValueTrait
{

	public function getPropertyValues($object, $flags = 0)
	{
		$properties = [];

		/**
		 *
		 * @var \ReflectionClass $class
		 */
		$class = $this->getReflectionClass($object);
		$properties = [];
		$this->populateClassPropertiyValues($properties, $class, $object,
			$flags);
		return $properties;
	}

	protected function populateClassPropertiyValues(&$properties,
		ReflectionClass $class, $object, $flags = 0,
		ReflectionClass $derivedClass = null)
	{
		if (($flags & ReflectionService::EXPOSE_INHERITED_PROPERTY) &&
			($parent = $class->getParentClass()))
		{

			$this->populateClassPropertiyValues($properties, $parent,
				$object,
				($flags |
				ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY),
				($derivedClass ? $derivedClass : $class));
		}
		/**
		 *
		 * @var \ReflectionProperty $property
		 */

		foreach ($class->getProperties() as $property)
		{
			$value = null;
			if ($this->populatePropertyValue($value, $object, $property,
				$flags, ($derivedClass ? $derivedClass : $class)))
				$properties[$property->getName()] = $value;
		}
		return $properties;
	}

	public function getPropertyValue($object, $property, $flags = 0)
	{
		$value = null;
		$this->populatePropertyValue($value, $object, $property, $flags);
		return $value;
	}

	protected function populatePropertyValue(&$value, $object, $property,
		$flags = 0, ReflectionClass $derivedClass = null)
	{
		$isPublic = false;
		try
		{
			if (!($property instanceof \ReflectionProperty))
			{
				$class = $this->getReflectionClass($object);
				$property = $class->getProperty($property);
			}

			$isPublic = $property->isPublic();
		}
		catch (\ReflectionException $e)
		{
			$property = null;
		}

		if ($isPublic)
		{
			if (($flags & ReflectionServiceInterface::FORCE_READ_METHOD) ==
				ReflectionServiceInterface::FORCE_READ_METHOD)
			{
				/**
				 *
				 * @var \ReflectionMethod $method
				 */
				$method = null;
				if ($derivedClass)
					$method = $this->findReadMethodForProperty(
						$derivedClass, $property->getName());
				if (!$method)
					$method = $this->findReadMethodForProperty(
						$property->getDeclaringClass(),
						$property->getName());
				if ($method)
				{
					$value = $method->invoke($object);
					return true;
				}
			}

			$value = $property->getValue($object);
			return true;
		}

		if (($flags & ReflectionServiceInterface::ALLOW_READ_METHOD) ==
			ReflectionServiceInterface::ALLOW_READ_METHOD)
		{
			$method = null;
			if ($derivedClass)
				$method = $this->findReadMethodForProperty(
					$derivedClass, $property->getName());
			if (!$method)
				$method = $this->findReadMethodForProperty(
					$property->getDeclaringClass(), $property->getName());
			if ($method)
			{
				$value = $method->invoke($object);
				return true;
			}
		}

		if ((($flags & ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY) ==
			ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY) &&
			$property)
		{
			$property->setAccessible(true);
			$value = $property->getValue($object);
			return true;
		}

		return false;
	}

	public function setPropertyValues($object, $values, $flags = 0)
	{
		$class = $this->getReflectionClass($object);
		foreach ($values as $property => $value)
		{
			try
			{
				$property = $this->getReflectionProperty($class,
					$property, $flags);
				$property->setValue($object, $value);
			}
			catch (\Exception $e)
			{}
		}
	}
}
