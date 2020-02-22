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

use Psr\Container\ContainerInterface;

/**
 * Container utility class
 */
class Container
{

	/**
	 *
	 * Container is modifiable.
	 *
	 * Container property flag.
	 *
	 * @used-by Container::getContainerProperties()
	 *
	 * @var number
	 */
	const MODIFIABLE = 0x01;

	/**
	 * Container can accept new elements
	 *
	 * Container property flag.
	 *
	 * @used-by Container::getContainerProperties()
	 * @var number
	 */
	const EXTENDABLE = 0x03;

	/**
	 * Elements can be removed from container
	 *
	 * @used-by Container::getContainerProperties()
	 * @var number
	 */
	const SHRINKABLE = 0x05;

	/**
	 * Container elements can be accessed using random access method or bracket operator
	 *
	 * Container property flag.
	 *
	 * @used-by Container::getContainerProperties()
	 * @var number
	 */
	const RANDOM_ACCESS = 0x08;

	/**
	 * Container can is traversable
	 *
	 * Container property flag.
	 *
	 * @used-by Container::getContainerProperties()
	 * @var number
	 */
	const TRAVERSABLE = 0x10;

	/**
	 * Number of elements contained is available
	 *
	 * Container property flag.
	 *
	 * @used-by Container::getContainerProperties()
	 * @var number
	 */
	const COUNTABLE = 0x20;

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
	 * Get the kind of operation supported by a given container
	 *
	 * @param mixed $container
	 *        	Container
	 *
	 * @return number A combination of the following flags
	 *         <ul>
	 *         <li>Container::RANDOM_ACCESS</li>
	 *         <li>Container::TRAVERSABLE</li>
	 *         <li>Container::MODIFIABLE</li>
	 *         <li>Container::EXTENDABLE</li>
	 *         <li>Container::SHRINKABLE</li>
	 *         <ul>
	 *
	 */
	public static function getContainerProperties($container)
	{
		$property = 0;
		if (\is_array($container))
			$property |= self::RANDOM_ACCESS | self::TRAVERSABLE | self::EXTENDABLE |
				self::SHRINKABLE | self::COUNTABLE;
		if ($container instanceof \ArrayAccess)
			$property |= self::RANDOM_ACCESS | self::EXTENDABLE | self::SHRINKABLE;
		if ($container instanceof ContainerInterface)
			$property |= self::RANDOM_ACCESS;
		if ($container instanceof \Traversable)
			$property |= self::TRAVERSABLE;
		if ($container instanceof \Countable)
			$property |= self::COUNTABLE;
		return $property;
	}

	/**
	 * Indicates if the parameter is an array or an object which
	 * implements ArrayAccess interface (PHP 5)
	 *
	 * @param mixed $container
	 */
	public static function isArray($container)
	{
		$p = self::getContainerProperties($container);
		$e = (self::RANDOM_ACCESS);
		return (($p & $e) == $e);
	}

	/**
	 *
	 * Indicates if the parameter is an array or an object which
	 *
	 * @param boolean $acceptAnyObject
	 *        	Any class instance is considered as traversable
	 *
	 * @return boolean true if$container is traversable (i.e usable in a roreach statement)
	 */
	public static function isTraversable($container, $acceptAnyObject = false)
	{
		$p = self::getContainerProperties($container);
		$e = (self::TRAVERSABLE);

		if (\is_object($container) && $acceptAnyObject)
			$p |= self::TRAVERSABLE;

		return (($p & $e) == $e);
	}

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
	 * @return mixed The input When $mode is Container::REMOVE_INPLACE, return a boolean indicating if th input array was modified.
	 *         Otherwise a new container otherwise
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
					return true;
				}

				return false;
			}
			elseif (\is_array($container))
			{
				if (\array_key_exists($key, $container))
				{
					unset($container[$key]);
					return true;
				}

				return false;
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
				$t = \is_object($container) ? new \ArrayObject() : [];
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
	 * Transform any type to a plain PHP array
	 *
	 * @param mixed $anything
	 * @param number $singleElementKey
	 *        	Key used to create a single element array when is not something that could be
	 *        	converted to an array
	 * @return array or null if $anything cannont be converted to array and $singleElementKey is null
	 */
	public static function createArray($anything, $singleElementKey = 0)
	{
		if (\is_array($anything))
		{
			return $anything;
		}
		elseif ($anything instanceof \ArrayObject || $anything instanceof ArrayRepresentation)
		{
			return $anything->getArrayCopy();
		}
		elseif (self::isTraversable($anything, true))
		{
			$a = [];
			foreach ($anything as $k => $v)
			{
				$a[$k] = $v;
			}
			return $a;
		}

		if ($singleElementKey !== null)
			return [
				$singleElementKey => $anything
			];

		return null;
	}

	/**
	 * Indicates if the given container could be considered as
	 * an indexed array
	 *
	 * An indexed array is a container where keys are a sequence of integers
	 * starting from 0 to n-1.
	 * (where n is the number of elements of the container)
	 *
	 * An empty container is always considered as an indexed array
	 *
	 * Complexity : 𝛰(n)
	 *
	 * @param mixed $container
	 *        	Any traversable container
	 *
	 *
	 * @param boolean $strict
	 *        	Only accept pure integer type as valid key.
	 *        	Otherwise, a string key containing only digits is accepted.
	 *
	 * @return boolean
	 *        	true if the container keys is a non-sparse sequence of integer
	 *        starting from 0 to n-1 (where n is the number of elements of the container).
	 */
	public static function isIndexed($container, $strict = false)
	{
		if (!self::isTraversable($container, true))
			throw new InvalidContainerException($container, __METHOD__);

		$i = 0;
		if ($strict)
		{
			foreach ($container as $key => $value)
			{
				if (!(\is_integer($key)))
					return false;

				if ($i != $key)
					return false;

				$i++;
			}
		}
		else
		{
			foreach ($container as $key => $value)
			{
				if (!(\is_integer($key) || \ctype_digit($key)))
					return false;
				if ($i != \intval($key))
					return false;
				$i++;
			}
		}

		return true;
	}

	/**
	 * Indicates if the given array is an associative array
	 *
	 * An empty container is always considered as associative
	 *
	 * Complexity : 𝛰(n)
	 *
	 * @param array|\ArrayAccess|\Traversable $container
	 *        	Any traversable container
	 * @param boolean $strict
	 *        	If true, only consider
	 * @throws InvalidContainerException
	 * @return boolean @true if at least one of $container keys is not a integer
	 *         or if the array keys are not consecutive values. An empty container is considered as associative
	 */
	public static function isAssociative($container, $strict = false)
	{
		if (!self::isTraversable($container, true))
			throw new InvalidContainerException($container, __METHOD__);

		$i = 0;
		if ($strict)
		{
			foreach ($container as $key => $value)
			{
				if (!\is_integer($key))
					return true;

				if ($i != $key)
					return true;

				$i++;
			}
		}
		else
		{
			foreach ($container as $key => $value)
			{
				if (!(\is_integer($key) || \ctype_digit($key)))
					return true;
				if ($i != \intval($key))
					return true;
				$i++;
			}
		}

		return ($i == 0);
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
			return (\array_key_exists($key, $container));
		elseif ($container instanceof \ArrayAccess)
			return $container->offsetExists($key);
		elseif ($container instanceof ContainerInterface)
			return $container->has($key);
		elseif ($container instanceof \Traversable)
		{
			foreach ($container as $k => $_)
			{
				if ($key === $k)
					return true;
			}

			return false;
		}
		elseif (\is_object($container) && \is_string($key))
		{
			return (\property_exists($container, $key));
		}

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Indicates if the given value appears in the container elements
	 *
	 * @param array|\ArrayAccess|\Traversable $container
	 * @param mixed $value
	 *        	Value to check in $container
	 * @param boolean $strict
	 *        	If true, use the strict equal (===) operator
	 *
	 * @throws InvalidContainerException
	 *
	 * @return boolean true if $value appears in $container
	 */
	public static function valueExists($container, $value, $strict = false)
	{
		if (\is_array($container))
		{
			return (\in_array($value, $container, $strict));
		}
		elseif (self::isTraversable($container))
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
			return (\array_key_exists($key, $container)) ? $container[$key] : $defaultValue;
		elseif ($container instanceof \ArrayAccess)
			return ($container->offsetExists($key) ? $container->offsetGet($key) : $defaultValue);
		elseif ($container instanceof ContainerInterface)
			return ($container->has($key) ? $container->get($key) : $defaultValue);
		elseif ($container instanceof \Traversable)
		{
			foreach ($container as $k => $value)
			{
				if ($key === $k)
					return $value;
			}

			return $defaultValue;
		}
		elseif (\is_object($container) && \is_string($key))
		{
			if (\property_exists($container, $key) || \method_exists($container, '__get'))
			{
				return $container->$key;
			}

			return $defaultValue;
		}

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 *
	 * @param array|\ArrayAccess|\Traversable $container
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @throws \InvalidArgumentException
	 * @throws InvalidContainerException
	 */
	public static function setValue(&$container, $key, $value)
	{
		if (\is_array($container))
		{
			$container[$key] = $value;
			return;
		}
		elseif ($container instanceof \ArrayAccess)
		{
			$container->offsetSet($key, $value);
			return;
		}
		elseif (\is_object($container) && \is_string($key))
		{
			if (\property_exists($container, $key) || \method_exists($container, '__set'))
			{
				$container->$key = $value;
				return;
			}

			throw new \InvalidArgumentException(
				$key . ' is not a member of ' . TypeDescription::getName($container));
		}

		throw new InvalidContainerException($container);
	}

	/**
	 * String to prepend before each array element.
	 *
	 * @var string
	 */
	const IMPLODE_BEFORE = 'before';

	/**
	 * String to append after each array element.
	 *
	 * @var string
	 */
	const IMPLODE_AFTER = 'after';

	/**
	 * String to insert between two elements of the array.
	 *
	 * @var string
	 */
	const IMPLODE_BETWEEN = 'between';

	/**
	 * String to insert between the penultimate and last element of the array.
	 *
	 * @var string
	 */
	const IMPLODE_BETWEEN_LAST = 'last';

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
		$callableArguments = array())
	{
		$a = '';
		$b = '';
		$i = $glue;
		$p = $glue;
		if (self::isArray($glue))
		{
			$a = self::keyValue($glue, self::IMPLODE_AFTER);
			$b = self::keyValue($glue, self::IMPLODE_BEFORE);
			$i = self::keyValue($glue, self::IMPLODE_BETWEEN);
			$p = self::keyValue($glue, self::IMPLODE_BETWEEN_LAST);
		}
		if (\is_callable($callable))
		{
			$parts = [];
			foreach ($container as $k => $v)
			{
				$part = call_user_func_array($callable, \array_merge([
					$v
				], $callableArguments));

				if ($part !== false)
					$parts[] = $part;
			}
		}
		else
		{
			$parts = [];
			foreach ($container as $key => $value)
			{
				$parts[] = $value;
			}
		}

		return self::implodeParts($parts, $b, $i, $p, $a);
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
		$callableArguments = array())
	{
		$a = '';
		$b = '';
		$i = $glue;
		$p = $glue;
		if (self::isArray($glue))
		{
			$a = self::keyValue($glue, self::IMPLODE_AFTER);
			$b = self::keyValue($glue, self::IMPLODE_BEFORE);
			$i = self::keyValue($glue, self::IMPLODE_BETWEEN);
			$p = self::keyValue($glue, self::IMPLODE_BETWEEN_LAST);
		}

		if (\is_callable($callable))
		{
			$parts = [];
			foreach ($container as $k => $v)
			{
				$part = call_user_func_array($callable, \array_merge([
					$k
				], $callableArguments));

				if ($part !== false)
					$parts[] = $part;
			}
		}
		else
		{
			$parts = [];
			foreach ($container as $key => $value)
			{
				$parts[] = $key;
			}
		}

		return self::implodeParts($parts, $b, $i, $p, $a);
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
	public static function implode($container, $glue, $callable, $callableArguments = array())
	{
		$a = '';
		$b = '';
		$i = $glue;
		$p = $glue;

		if (self::isArray($glue))
		{
			$a = self::keyValue($glue, self::IMPLODE_AFTER);
			$b = self::keyValue($glue, self::IMPLODE_BEFORE);
			$i = self::keyValue($glue, self::IMPLODE_BETWEEN);
			$p = self::keyValue($glue, self::IMPLODE_BETWEEN_LAST);
		}

		$parts = [];
		foreach ($container as $k => $v)
		{
			$part = call_user_func_array($callable, \array_merge([
				$k,
				$v
			], $callableArguments));

			if ($part !== false)
				$parts[] = $part;
		}

		return self::implodeParts($parts, $b, $i, $p, $a);
	}

	private static function implodeParts($parts, $b, $i, $p, $a)
	{
		$count = self::count($parts);
		$index = 0;
		$result = '';
		foreach ($parts as $part)
		{
			if ($index > 0)
			{
				$result .= ($index - ($count - 1) ? $i : $p);
			}

			$result .= $b . $part . $a;
			$index++;
		}

		return $result;
	}
}

