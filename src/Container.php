<?php

/**
 * Copyright © 2012-2018 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

class InvalidContainerException extends \InvalidArgumentException
{

	public function __construct($element, $forMethod = null)
	{
		parent::__construct(
			TypeDescription::getName($element) . ' is not a valid container' .
			(\is_string($forMethod) ? ' for method ' . $forMethod : ''));
	}
}

/**
 * Container utility class
 */
class Container
{

	/**
	 * Behavior of the Container::removeKey method.
	 * Replace element in-place.
	 *
	 * @var integer
	 */
	const REMOVE_INPLACE = 0x1;

	/**
	 * Behavior of the Container::removeKey method.
	 *
	 * Return a clone of the input container without the removed key
	 * or an array
	 *
	 * @var integer
	 */
	const REMOVE_COPY = 0x2;

	/**
	 * Behavior of the Container::removeKey method.
	 *
	 * Return a clone of the input container without the removed key.
	 *
	 * @var integer
	 */
	const REMOVE_COPY_STRICT_TYPE = 0x6;

	/**
	 * Implode function callable argument selection.
	 *
	 * The container element key will be passed to the user-defined callable.
	 *
	 * @var integer
	 */
	const IMPLODE_KEYS = 0x01;

	/**
	 * Implode function callable argument selection.
	 *
	 * The container element value will be passed to the user-defined callable;
	 *
	 * @var integer
	 */
	const IMPLODE_VALUES = 0x02;

	/**
	 * Remove an element of a container
	 *
	 * @param array|\ArrayAccess|\Traversable $container
	 *        	Input container
	 * @param mixed $key
	 *        	The key of the element to remove
	 * @param integer $mode
	 *        	Remove mode.
	 *        	<ul>
	 *        	<li>Container::REMOVE_INPLACE: Remove element in-place</li>
	 *        	<li>Container::REMOVE_COPY: Create a new container without the removed element</li>
	 *        	<li>Container::REMOVE_COPY_STRICT_TYPE: Ensure the new container have the same type as the input
	 *        	container</li>
	 *        	</ul>
	 * @throws InvalidContainerException
	 * @throws \InvalidArgumentException
	 * @return \ArrayAccess|boolean|\ArrayAccess[]|\Traversable[] The input array if $mode is Container::REMOVE_INPLACE,
	 *         or a new container otherwise
	 */
	public static function removeKey(&$container, $key, $mode = self::REMOVE_COPY)
	{
		if ($mode == self::REMOVE_INPLACE)
		{
			if ($container instanceof \ArrayAccess)
			{
				if ($container->offsetExists($key))
				{
					$container->offsetUnset($key);
				}

				return $container;
			}
			elseif (\is_array($container))
			{
				if (\array_key_exists($key, $container))
				{
					unset($container[$key]);
					return true;
				}

				return $container;
			}

			throw new InvalidContainerException($container, __METHOD__ . ' (inplace)');
		}
		elseif ($mode & self::REMOVE_COPY)
		{
			$relax = (($mode & self::REMOVE_COPY_STRICT_TYPE) != self::REMOVE_COPY_STRICT_TYPE);

			if ($container instanceof \ArrayAccess)
			{
				$t = clone $container;
				if ($t->offsetExists($key))
				{
					$t->offsetUnset($key);
				}

				return $t;
			}
			elseif (\is_array($container) || (($container instanceof \Traversable) && $relax))
			{
				$t = \is_object($container) ? new \ArrayObject() : array();
				foreach ($container as $k => $v)
				{
					if ($k !== $key)
						$t[$k] = $v;
				}
				return $t;
			}

			throw new InvalidContainerException($container, __METHOD__ . ' (copy)');
		}
		else
			throw new \InvalidArgumentException('mode');
	}

	/**
	 * Indicates if the parameter is an array or an object which
	 * implements ArrayAccess interface (PHP 5)
	 *
	 * @param mixed $container
	 */
	public static function isArray($container)
	{
		return (\is_array($container) || ($container instanceof \ArrayAccess));
	}

	/**
	 * Transform any type to a plain PHP array
	 *
	 * @param mixed $anything
	 * @param number $singleElementKey
	 *        	Key used to create a single element array when is not something that could be
	 *        	converted to an array
	 * @return array or @c null if @c $anything cannont be converted to array and @c $singleElementKey is @c null
	 */
	public static function createArray($anything, $singleElementKey = 0)
	{
		if (\is_array($anything))
		{
			return $anything;
		}
		elseif (is_object($anything))
		{
			if ($anything instanceof DataTree)
			{
				return $anything->toArray();
			}
			elseif ($anything instanceof \ArrayObject)
			{
				return $anything->getArrayCopy();
			}
			else
			{
				$a = array();
				foreach ($anything as $k => $v)
				{
					$a[$k] = $v;
				}

				return $a;
			}
		}

		if ($singleElementKey !== null)
			return array(
				$singleElementKey => $anything
			);

		return null;
	}

	/**
	 * Indicates if the given array is an associative array
	 *
	 * @param array|\ArrayAccess|\Traversable $container
	 * @throws InvalidContainerException
	 * @return boolean @true if at least one of $container keys is not a integer
	 *         or if the array keys are not consecutive values
	 */
	public static function isAssociative($container)
	{
		if (!(\is_array($container) || ($container instanceof \Traversable)))
		{
			throw new InvalidContainerException($container, __METHOD__);
		}

		$itemCount = self::count($container);
		$index = 0;

		foreach ($container as $key => $value)
		{
			if (is_numeric($key))
			{
				if ($index != intval($key))
				{
					return true;
				}
			}
			else
			{
				return true;
			}

			$index++;
		}

		return false;
	}

	/**
	 * Get the number of element of the given array
	 *
	 * @param mixed $container
	 *        	Array, \Countable or \Traversable object
	 * @throws InvalidContainerException
	 * @return number Number of elements in $container
	 *        
	 */
	public static function count($container)
	{
		if (\is_array($container))
			return \count($container);
		elseif ($container instanceof \Countable)
			return $container->count();
		elseif ($container instanceof \Traversable)
		{
			$c = 0;
			foreach ($container as $k => $v)
				$c++;
			return $c;
		}

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Reset array pointer to initial value
	 * or rewind an Iterator
	 *
	 * @param array|\ArrayAccess $container
	 * @throws InvalidContainerException
	 */
	public static function reset(&$container)
	{
		if (\is_array($container))
		{
			reset($container);
		}
		elseif ($container instanceof \Iterator)
		{
			$container->rewind();
		}

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Indicates if a key exists in an array or a ArrayAccess implementation
	 *
	 * @param array|\ArrayAccess|\Traversable $container
	 * @param mixed $key
	 *        	Key to test
	 * @throws InvalidContainerException
	 *
	 * @return boolean
	 */
	public static function keyExists($container, $key)
	{
		if (\is_array($container))
		{
			return (\array_key_exists($key, $container));
		}
		elseif ($container instanceof \ArrayAccess)
		{
			return $container->offsetExists($key);
		}
		elseif ($container instanceof \Traversable)
		{
			foreach ($container as $k => $_)
			{
				if ($key === $k)
					return true;
			}

			return false;
		}

		throw new InvalidContainerException($container, __METHOD__);
	}

	public static function valueExists($container, $value, $strict = false)
	{
		if (\is_array($container))
		{
			return (\in_array($value, $container, $strict));
		}
		elseif ($container instanceof \Traversable)
		{
			if ($strict)
			{
				foreach ($container as $k => $v)
				{
					if ($v === $value)
						return true;
				}
			}
			else
			{
				foreach ($container as $k => $v)
				{
					if ($v == $value)
						return true;
				}
			}

			return false;
		}

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Retrieve key value or a default value if key doesn't exists
	 *
	 * @param array $container
	 * @param mixed $key
	 * @param mixed $defaultValue
	 *
	 * @throws InvalidContainerException
	 *
	 * @return mixed Value associated to $key or $defaultValue if the key does not exists
	 */
	public static function keyValue($container, $key, $defaultValue = null)
	{
		if (\is_array($container))
		{
			return (\array_key_exists($key, $container)) ? $container[$key] : $defaultValue;
		}

		if ($container instanceof \ArrayAccess)
		{
			return ($container->offsetExists($key) ? $container->offsetGet($key) : $defaultValue);
		}
		elseif ($container instanceof \Traversable)
		{
			foreach ($container as $k => $value)
			{
				if ($key === $k)
					return $value;
			}

			return $defaultValue;
		}

		throw new InvalidContainerException($element);
	}

	/**
	 * Implode array values
	 *
	 * @param array $container
	 *        	Input array
	 * @param string $glue
	 *        	Element glue
	 * @return string
	 */
	public static function implodeValues($container, $glue, $callable = null,
		$callableArguments = array ())
	{
		return self::implode($container, $glue, self::IMPLODE_VALUES, $callable, $callableArguments);
	}

	/**
	 * Implode array keys
	 *
	 * @param array $container
	 *        	Table
	 * @param string $glue
	 *        	Element glue
	 *        	
	 *		@note This function accepts parameter inversion
	 *
	 * @return string
	 */
	public static function implodeKeys($container, $glue, $callable = null,
		$callableArguments = array ())
	{
		return self::implode($container, $glue, self::IMPLODE_KEYS, $callable, $callableArguments);
	}

	/**
	 * Implode an array
	 *
	 * @param array $container
	 *        	Array to implode
	 * @param string $glue
	 *        	Glue
	 * @param callable $callable
	 * @param string $callableArguments
	 *
	 * @return string
	 */
	public static function implode($container, $glue, $what, $callable = null,
		$callableArguments = array())
	{
		if (self::isArray($glue) && is_string($container))
		{
			$a = $glue;
			$glue = $container;
			$container = $a;
		}

		if (!self::isArray($container) || self::count($container) == 0)
		{
			return '';
		}

		$result = '';

		if (!self::isArray($callableArguments))
		{
			$callableArguments = array(
				$callableArguments
			);
		}

		foreach ($container as $k => $v)
		{
			$r = '';
			if (\is_callable($callable))
			{
				$a = array();
				if ($what & self::IMPLODE_KEYS)
					$a[] = $k;
				if ($what & self::IMPLODE_VALUES)
					$a[] = $v;
				$r = call_user_func_array($callable, array_merge($a, $callableArguments));
			}
			else 
				if ($what & self::IMPLODE_VALUES)
				{
					$r = $v;
				}
				else 
					if ($what & self::IMPLODE_KEYS)
					{
						$r = $k;
					}

			if (strlen($r) == 0)
			{
				continue;
			}

			if (strlen($result) > 0)
			{
				$result .= $glue;
			}

			$result .= $r;
		}

		return $result;
	}
}

