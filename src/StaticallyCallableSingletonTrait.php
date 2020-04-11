<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

/**
 * Forward any unknown static method call to the class singleton non-static method.
 */
trait StaticallyCallableSingletonTrait
{
	use SingletonTrait;

	/**
	 * Attempt to invoke non-static method
	 *
	 * @param string $method
	 *        	Method name
	 * @param array $args
	 *        	Method arguments
	 * @return mixed
	 */
	public static function __callstatic($method, $args)
	{
		return \call_user_func_array([
			self::getInstance(),
			$method
		], $args);
	}
}