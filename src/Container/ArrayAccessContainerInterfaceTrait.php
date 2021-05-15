<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Container;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Implementation of ContainerInterface using ArrayAccess methods
 */
trait ArrayAccessContainerInterfaceTrait
{

	/**
	 * Invoke \ArrayAccess:offsetExists ($key)
	 *
	 * @param mixed $key
	 *        	Element key
	 * @return boolean
	 */
	public function has($key)
	{
		return $this->offsetExists($key);
	}

	/**
	 *
	 * @param mixed $key
	 *        	Element key
	 * @return mixed Element value
	 * @throws KeyNotFoundException
	 */
	public function get($key)
	{
		if (!$this->has($key))
		{
			$e = $this->newNotFoundException($key);
			throw $e;
		}

		return $this->offsetGet($key);
	}

	/**
	 * Create an exception that should be raised while attempting to access
	 * a non-existing element
	 *
	 * @param mixed $key
	 *        	Element key
	 * @return NotFoundExceptionInterface
	 */
	protected function newNotFoundException($key)
	{
		return new KeyNotFoundException($key);
	}
}
