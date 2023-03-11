<?php
/**
 * Copyright © 2020 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

use NoreSources\Type\ArrayRepresentation;

/**
 * Represents a tree of values
 * where undefined tree path will take their value
 * from the nearest valid ancestor value.
 */
class CascadedValueTree implements \ArrayAccess, ArrayRepresentation
{

	/**
	 *
	 * @param string|array $query
	 *        	Key path
	 * @param mixed $dflt
	 *        	Default value
	 * @return mixed Value associated to the leaf key of $query.
	 *         If the value does not exists, the value of the leaf key of the
	 *         nearest ancestor is returned.
	 *         Otherwise, return the default value
	 */
	public function query($query, $dflt = null)
	{
		$value = $dflt;

		$this->walk($query,
			function ($node, $leafKey) use (&$value) {
				$value = $this->keyValue($node, $leafKey, $value);
				return true;
			},
			function ($node, $key, $leafKey) {
				return $this->hasNode($node, $key);
			},
			function ($node, $key, $leafKey) use (&$value) {
				$node = $node[self::NODES][$key];
				$value = $this->keyValue($node, $leafKey, $value);
				return true;
			});

		return $value;
	}

	#[\ReturnTypeWillChange]
	public function getArrayCopy()
	{
		return $this->valueTree->getArrayCopy();
	}

	/**
	 * Query key path value
	 *
	 * @param string|array $query
	 *        	Key path
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($query)
	{
		return $this->query($query);
	}

	/**
	 * Set the value associated to the given key path
	 *
	 * @param string|array $query
	 *        	Key path
	 * @param mixed $value
	 *        	Value
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($query, $value)
	{
		return $this->walk($query, null, null, null,
			function ($node, $valid) use ($value) {
				Container::setValue($node, self::VALUE, $value);
				return true;
			});
	}

	/**
	 * Indicates if a value is associated to the given key path
	 *
	 * @param string|array $query
	 *        	Key path
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists($query)
	{
		return $this->walk($query, null,
			function ($node, $key, $leafKey) {
				return $this->hasNode($node, $key);
			}, null,
			function ($node, $valid) {
				return $valid && Container::keyExists($node, self::VALUE);
			});
	}

	/**
	 * Remove the value associated to the given key path
	 *
	 * @param string|array $query
	 *        	Key path
	 * @return true if value was removed
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset($query)
	{
		return $this->walk($query, null,
			function ($node, $key) {
				$valid = $node->offsetExists(self::NODES) &&
				$node[self::NODES]->offsetExists($key);

				return $valid;
			}, null,
			function ($node, $valid) {
				if (!$valid)
					return $valid;
				if (!$node->offsetExists(self::VALUE))
					return false;
				$node->offsetUnset(self::VALUE);
				return true;
			});
	}

	/**
	 * Alias of query() method
	 *
	 * @return mixed
	 */
	public function __invoke()
	{
		return \call_user_func_array([
			$this,
			'query'
		], func_get_args());
	}

	/**
	 * Set the separator of the string-form key path query
	 *
	 * @param string $separator
	 */
	public function setKeySeparator($separator)
	{
		$this->keySeparator = $separator;
	}

	public function __construct()
	{
		$this->setKeySeparator('.');
		$this->valueTree = new \ArrayObject();
	}

	private function hasNode(\ArrayObject $node, $key)
	{
		return $node->offsetExists(self::NODES) &&
			$node[self::NODES]->offsetExists($key);
	}

	private function keyValue(\ArrayObject $node, $key, $dflt = null)
	{
		if ($node->offsetExists(self::NODES) &&
			$node[self::NODES]->offsetExists($key))
			return Container::keyValue($node[self::NODES][$key],
				self::VALUE, $dflt);
		return $dflt;
	}

	private function walk($query, $init = null, $nodePreprocess = null,
		$nodeProcess = null, $completion = null)
	{
		if (!\is_array($query))
			$query = \explode($this->keySeparator, $query);

		$node = $this->valueTree;
		$c = \count($query);
		$valid = true;
		$leafKey = ($c ? $query[$c - 1] : null);

		if (\is_callable($init))
			$valid = \call_user_func($init, $node, $leafKey);

		if ($valid)
			foreach ($query as $key)
			{
				if (\is_callable($nodePreprocess))
					$valid = \call_user_func($nodePreprocess, $node,
						$key, $leafKey);

				if (!$valid)
					break;

				if (!$node->offsetExists(self::NODES))
					$node[self::NODES] = new \ArrayObject();
				if (!$node[self::NODES]->offsetExists($key))
					$node[self::NODES][$key] = new \ArrayObject();

				if (\is_callable($nodeProcess))
					if (!($valid = \call_user_func($nodeProcess, $node,
						$key, $leafKey)))
						break;

				$node = $node[self::NODES][$key];
			}

		if (\is_callable($completion))
			$valid = \call_user_func($completion, $node, $valid);

		return $valid;
	}

	const NODES = 'nodes';

	const VALUE = 'value';

	private $keySeparator;

	private $valueTree;
}

