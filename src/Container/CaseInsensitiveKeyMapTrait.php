<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Http
 */
namespace NoreSources\Container;

/**
 * Case-insensitive key value map.
 *
 * Item access works case-insensitively but key case is preserved internally.
 *
 * Implements ArrayAccess, ContainerInterface, Countable, IteratorAggregator, ArrayRepresentation
 */
trait CaseInsensitiveKeyMapTrait
{

	/**
	 *
	 * @param array $array
	 */
	public function __construct($array = array())
	{
		$this->initializeCaseInsensitiveKeyMapTrait($array);
	}

	/**
	 *
	 * @return integer
	 */
	public function count()
	{
		return $this->map->count();
	}

	/**
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return $this->map->getIterator();
	}

	/**
	 *
	 * @return array
	 */
	public function getArrayCopy()
	{
		return $this->map->getArrayCopy();
	}

	/**
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetExists($name)
	{
		return $this->caselessOffsetExists($name);
	}

	/**
	 *
	 * @param string $name
	 * @return mixed|NULL
	 */
	public function offsetGet($name)
	{
		return $this->caselessOffsetGet($name);
	}

	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function offsetSet($name, $value)
	{
		return $this->caselessOffsetSet($name, $value);
	}

	/**
	 *
	 * @param string $name
	 */
	public function offsetUnset($name)
	{
		$this->caselessOffsetUnset($name);
	}

	/**
	 *
	 * @param mixed $name
	 *        	Key
	 * @throws KeyNotFoundException
	 * @return mixed
	 */
	public function get($name)
	{
		if (!$this->offsetExists($name))
			$this->onKeyNotFound($name);
		return $this->offsetGet($name);
	}

	/**
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function has($name)
	{
		return $this->offsetExists($name);
	}

	protected function initializeCaseInsensitiveKeyMapTrait(
		$array = array())
	{
		$this->map = new \ArrayObject();
		if (Container::isTraversable($array))
			foreach ($array as $name => $value)
			{
				$this->offsetSet($name, $value);
			}
	}

	protected function caselessOffsetExists($name)
	{
		$strict = $this->map->offsetExists($name);
		if ($strict)
			return true;
		foreach ($this->map as $key => $_)
		{
			if (\strcasecmp($key, $name) == 0)
				return true;
		}
		return false;
	}

	protected function caselessOffsetGet($name)
	{
		if ($this->map->offsetExists($name))
			return $this->map->offsetGet($name);

		foreach ($this->map as $key => $value)
		{
			if (\strcasecmp($key, $name) == 0)
				return $value;
		}

		return null;
	}

	protected function caselessOffsetSet($name, $value)
	{
		$this->offsetUnset($name);
		$this->map->offsetSet($name, $value);
	}

	protected function caselessOffsetUnset($name)
	{
		foreach ($this->map as $key => $_)
		{
			if (\strcasecmp($key, $name) == 0)
			{
				$this->map->offsetUnset($key);
				return;
			}
		}
	}

	/**
	 * Thing to do when a requested element was not found
	 *
	 * @param mixed $key
	 * @throws KeyNotFoundException
	 */
	protected function onKeyNotFound($key)
	{
		throw new KeyNotFoundException($key);
	}

	/**
	 *
	 * @var \ArrayObject
	 */
	private $map;
}