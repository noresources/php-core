<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection\Traits;

use NoreSources\Reflection\ReflectionServiceInterface;

/**
 * Implements methods of ReflectionServiceInterface related to property values
 */
trait ReflectionServicePropertyValueTrait
{

	public function getPropertyValues($object, $flags = 0)
	{
		/**
		 *
		 * @var \ReflectionClass $class
		 */
		$class = $this->getReflectionClass($object);
		$properties = [];
		foreach ($class->getProperties() as $property)
		{
			/**
			 *
			 * @var \ReflectionProperty $property
			 */
			$properties[$property->getName()] = $this->getPropertyValue(
				$object, $property, $flags);
		}
		return $properties;
	}

	public function getPropertyValue($object, $property, $flags = 0)
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
				$method = $this->findReadMethodForProperty(
					$property->getDeclaringClass(), $property->getName());
				if ($method)
					return $method->invoke($object);
			}

			return $property->getValue($object);
		}

		if (($flags & ReflectionServiceInterface::ALLOW_READ_METHOD) ==
			ReflectionServiceInterface::ALLOW_READ_METHOD)
		{
			$method = $this->findReadMethodForProperty(
				$property->getDeclaringClass(), $property->getName());
			if ($method)
				return $method->invoke($object);
		}

		if ((($flags & ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY) ==
			ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY) &&
			$property)
		{
			$property->setAccessible(true);
			return $property->getValue($object);
		}

		return null;
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
