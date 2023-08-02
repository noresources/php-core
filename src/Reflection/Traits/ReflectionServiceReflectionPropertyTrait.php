<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection\Traits;

use NoreSources\Reflection\ReflectionData;
use NoreSources\Reflection\ReflectionServiceInterface;

/**
 * Implements ReflectionServiceInterface getReflectionProperty()
 */
trait ReflectionServiceReflectionPropertyTrait
{

	public function getReflectionProperty($class, $propertyName,
		$flags = 0)
	{
		/**
		 *
		 * @var ReflectionServiceInterface $this
		 * @var \ReflectionClass $class
		 */
		if (!($class instanceof \ReflectionClass))
			$class = $this->getReflectionClass($class);

		$exists = $class->hasProperty($propertyName);

		if (($flags & ReflectionServiceInterface::ALLOW_RW_METHODS) == 0)
		{
			$reflectionProperty = new \ReflectionProperty(
				$class->getName(), $propertyName);
			if (($flags & ReflectionServiceInterface::RW) &&
				!$reflectionProperty->isPublic())
				$reflectionProperty->setAccessible(true);
			return $reflectionProperty;
		}

		$readMethod = null;
		if (($flags & ReflectionServiceInterface::ALLOW_READ_METHOD) ==
			ReflectionServiceInterface::ALLOW_READ_METHOD)
		{
			$readMethod = $this->findReadMethodForProperty($class,
				$propertyName);
		}

		$writeMethod = null;
		if (($flags & ReflectionServiceInterface::ALLOW_WRITE_METHOD) ==
			ReflectionServiceInterface::ALLOW_WRITE_METHOD)
		{
			$writeMethod = $this->findWriteMethodForProperty($class,
				$propertyName);
		}

		if (!$exists)
		{
			if ((($flags & ReflectionServiceInterface::READABLE) ==
				ReflectionServiceInterface::READABLE) &&
				($readMethod === null))
				throw new \ReflectionException(
					$class->getName() . '::$' . $propertyName .
					' is not readable');
			if ((($flags & ReflectionServiceInterface::WRITABLE) ==
				ReflectionServiceInterface::WRITABLE) &&
				($writeMethod === null))
				throw new \ReflectionException(
					$class->getName() . '::$' . $propertyName .
					' is not writable');
		}

		$reflectionProperty = null;
		if ($exists)
		{
			$reflectionProperty = new \ReflectionProperty(
				$class->getName(), $propertyName);
			if ($reflectionProperty->isPublic())
			{
				if (($flags &
					ReflectionServiceInterface::FORCE_READ_METHOD) !=
					ReflectionServiceInterface::FORCE_READ_METHOD)
				{
					$readMethod = null;
				}

				if (($flags &
					ReflectionServiceInterface::FORCE_WRITE_METHOD) !=
					ReflectionServiceInterface::FORCE_WRITE_METHOD)
				{
					$writeMethod = null;
				}
			}
			else // Not public
			{
				if ((($flags & ReflectionServiceInterface::READABLE) ==
					ReflectionServiceInterface::READABLE) &&
					($readMethod === null))
				{
					$reflectionProperty->setAccessible(true);
				}

				if ((($flags & ReflectionServiceInterface::WRITABLE) ==
					ReflectionServiceInterface::WRITABLE) &&
					($writeMethod === null))
				{
					$reflectionProperty->setAccessible(true);
				}
			}
		}

		if ($readMethod || $writeMethod || !$exists)
			$reflectionProperty = new ReflectionData($class->getName(),
				$propertyName, $reflectionProperty, $readMethod,
				$writeMethod);

		return $reflectionProperty;
	}
}
