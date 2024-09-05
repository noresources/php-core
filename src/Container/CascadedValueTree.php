<?php
/**
 * Copyright © 2020 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

/**
 * Represents a tree of values
 * where undefined tree path will take their value
 * from the nearest valid ancestor value.
 */
class CascadedValueTree extends \ArrayObject
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
		$keyTree = Container::normalizeKeyTree($query,
			$this->keySeparator);
		if (Container::count($keyTree) == 1)
		{
			$key = Container::shift($keyTree);
			if (!parent::offsetExists($key))
				return $dflt;
			return parent::offsetGet($key);
		}
		return Container::treeValue($this, $query, $dflt,
			$this->keySeparator);
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
		$keyTree = Container::normalizeKeyTree($query,
			$this->keySeparator);
		if (Container::count($keyTree) == 1)
		{
			$offset = Container::shift($keyTree);
			parent::offsetSet($offset, $value);
			return;
		}
		$a = $this->getArrayCopy();
		Container::treeSet($a, $keyTree, $value, $this->keySeparator);
		$this->exchangeArray($a);
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
		$keyTree = Container::normalizeKeyTree($query,
			$this->keySeparator);
		if (Container::count($keyTree) == 1)
		{
			$offset = Container::shift($keyTree);
			return parent::offsetExists($offset);
		}
		return Container::treeExists($this, $keyTree,
			$this->keySeparator);
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
		$keyTree = Container::normalizeKeyTree($query,
			$this->keySeparator);
		if (Container::count($keyTree) == 1)
		{
			$key = Container::shift($keyTree);
			parent::offsetUnset($key);
			return;
		}
		$a = $this->getArrayCopy();
		Container::treeRemoveKey($a, $keyTree, $this->keySeparator);
		$this->exchangeArray($a);
	}

	/**
	 * Merge given array(s) with the current content
	 *
	 * @return \NoreSources\Container\CascadedValueTree
	 */
	public function merge(  /* ...$arrays [, $options] */ )
	{
		$options = Container::MERGE_RECURSE |
			Container::MERGE_LIST_REPLACE;
		$args = \func_get_args();
		$last = \array_pop($args);
		if (\is_integer($last))
			$options = $last;
		\array_unshift($args, $this->getArrayCopy());
		$args[] = $options;
		$array = \call_user_func_array([
			Container::class,
			'merge'
		], $args);
		$this->exchangeArray($array);
		return $this;
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

	public function __construct($array = array())
	{
		parent::__construct($array);
		$this->setKeySeparator('.');
	}

	/**
	 *
	 * @var string
	 */
	private $keySeparator;
}

