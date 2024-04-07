<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources;

/**
 * Singleton pattern trait
 */
trait SingletonTrait
{

	/**
	 * Get the class singleton instance
	 *
	 * @return $this Class singleton instance. If the singleton was not created yet,
	 *         the instance will be created by calling the class constructor with the arguments
	 *         given to the getInstance() method
	 */
	public static function getInstance()
	{
		if (!isset(self::$singletonInstances))
			self::$singletonInstances = [];
		if (!isset(self::$singletonInstances[static::class]))
		{
			$reflectionClass = new \ReflectionClass(static::class);
			self::$singletonInstances[static::class] = $reflectionClass->newInstanceArgs(
				func_get_args());
		}

		return self::$singletonInstances[static::class];
	}

	/**
	 * Per class type instance
	 *
	 * @var object[]
	 */
	private static $singletonInstances;
}