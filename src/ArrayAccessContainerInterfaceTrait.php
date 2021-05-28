<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

/**
 * Implementation of ContainerInterface using ArrayAccess methods
 */
trait ArrayAccessContainerInterfaceTrait
{

	public function has($key)
	{
		return $this->offsetExists($key);
	}

	public function get($key)
	{
		return $this->offsetGet($key);
	}
}
