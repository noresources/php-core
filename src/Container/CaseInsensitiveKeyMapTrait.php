<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
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
		return $this->caseSensitiveMap->count();
	}

	/**
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return $this->caseSensitiveMap->getIterator();
	}

	/**
	 *
	 * @return array
	 */
	public function getArrayCopy()
	{
		return $this->caseSensitiveMap->getArrayCopy();
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

	public function exchangeArray($array)
	{
		$this->initializeCaseInsensitiveKeyMapTrait($array, true);
	}

	/**
	 *
	 * @param \ArrayObject|array $array
	 *        	Input array. If $array is a \ArrayObject nad $copy is not true. The internal map
	 *        	will use $array as a reference.
	 * @param NULL|boolean $copy
	 *        	If TRUE, copy input array anyway. If NULL, set to FALSE if $array is ArrayObject,
	 *        	FALSE otherwise.
	 *        	If FALSE and if input array is a ArrayObject,
	 *        	use $array as referecne
	 */
	protected function initializeCaseInsensitiveKeyMapTrait(
		$array = array(), $copy = null)
	{
		if ($copy === null)
			$copy = ($array instanceof \ArrayObject) ? false : true;

		if ($array instanceof \ArrayObject && !$copy)
			$this->caseSensitiveMap = $array;
		else
		{
			if (!isset($this->caseSensitiveMap))
				$this->caseSensitiveMap = new \ArrayObject();
			$this->caseSensitiveMap->exchangeArray(
				Container::createArray($array));
		}

		$this->keys = [];
		foreach ($this->caseSensitiveMap as $key => $value)
			if (\is_string($key))
				$this->keys[\strtolower($key)] = $key;
	}

	protected function caselessOffsetExists($name)
	{
		if (\is_string($name))
			return Container::keyExists($this->keys, \strtolower($name));
		return $this->caseSensitiveMap->offsetExists($name);
	}

	protected function caselessOffsetGet($name)
	{
		if (\is_string($name))
			$name = Container::keyValue($this->keys, \strtolower($name),
				$name);
		return $this->caseSensitiveMap->offsetGet($name);
	}

	protected function caselessOffsetSet($name, $value)
	{
		if (\is_string($name))
		{
			$lower = \strtolower($name);
			if (($previous = Container::keyValue($this->keys, $lower)) &&
				($previous != $name))
				$this->caseSensitiveMap->offsetUnset($previous);

			$this->keys[$lower] = $name;
		}

		$this->caseSensitiveMap->offsetSet($name, $value);
	}

	protected function caselessOffsetUnset($name)
	{
		if (\is_string($name))
		{
			$lower = \strtolower($name);
			$name = Container::keyValue($this->keys, $lower, $name);
			Container::removeKey($this->keys, $lower);
		}

		$this->caseSensitiveMap->offsetUnset($name);
	}

	/**
	 *
	 * @var \ArrayObject
	 */
	private $caseSensitiveMap;

	/**
	 *
	 * @var array
	 */
	private $keys;
}