<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
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
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const MODIFIABLE = 0x01;

	/**
	 * Container can accept new elements.
	 *
	 * Container property flag.
	 *
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const EXTENDABLE = 0x03;

	/**
	 * Elements can be removed from container
	 *
	 * Container property flag.
	 *
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const SHRINKABLE = 0x05;

	/**
	 * Container can is traversable.
	 *
	 * Container property flag.
	 *
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const TRAVERSABLE = 0x08;

	/**
	 * Number of elements contained is available
	 *
	 * Container property flag.
	 *
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const COUNTABLE = 0x10;

	/**
	 * Container elements can be accessed using a arbitrary random access method.
	 *
	 * Container property flag.
	 *
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const RANDOM_ACCESS = 0x20;

	/**
	 * Container elements can be accessed by using the arrow operator.
	 *
	 * Container property flag.
	 *
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const PROPERTY_ACCESS = 0x60;

	/**
	 * Container elements can be accessed by using the bracket operator.
	 *
	 * Container property flag.
	 *
	 * @used-by Container::properties()
	 *
	 * @var number
	 */
	const OFFSET_ACCESS = 0xA0;

	/**
	 * The container element key will be passed to the user-defined callable.
	 *
	 * Container::implode() function callable argument selection flag.
	 *
	 * @var integer
	 */
	const IMPLODE_KEYS = 0x01;

	/**
	 * The container element value will be passed to the user-defined callable;
	 *
	 * Container::implode() function callable argument selection flag.
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
	 * @return integer A combination of the following flags
	 *         <ul>
	 *         <li>Container::RANDOM_ACCESS</li>
	 *         <li>Container::TRAVERSABLE</li>
	 *         <li>Container::MODIFIABLE</li>
	 *         <li>Container::EXTENDABLE</li>
	 *         <li>Container::SHRINKABLE</li>
	 *         <li>Container::RANDOM_ACCESS</li>
	 *         <li>Container::PROPERTY_ACCESS</li>
	 *         <li>Container::OFFSET_ACCESS</li>
	 *         <ul>
	 *
	 */
	public static function properties($container)
	{
		if ($container instanceof ContainerPropertyInterface)
			return $container->getContainerProperties();

		$properties = 0;
		if (\is_array($container))
			$properties |= self::TRAVERSABLE | self::EXTENDABLE |
				self::SHRINKABLE | self::COUNTABLE | self::OFFSET_ACCESS;
		if ($container instanceof \ArrayAccess)
			$properties |= self::EXTENDABLE | self::SHRINKABLE |
				self::OFFSET_ACCESS;
		if ($container instanceof ContainerInterface)
			$properties |= self::RANDOM_ACCESS;
		if ($container instanceof \Traversable)
			$properties |= self::TRAVERSABLE;
		if ($container instanceof \Countable)
			$properties |= self::COUNTABLE;

		return $properties;
	}

	/**
	 * Indicates if the parameter is an array or an object which
	 * implements ArrayAccess interface (PHP 5)
	 *
	 * @param mixed $container
	 */
	public static function isArray($container)
	{
		$p = self::properties($container);
		$e = (self::RANDOM_ACCESS);
		return (($p & $e) == $e);
	}

	/**
	 *
	 * Indicates if the parameter is an array or an object which
	 *
	 * @return boolean true if$container is traversable (i.e usable in a roreach statement)
	 */
	public static function isTraversable($container)
	{
		$p = self::properties($container);
		return (($p & self::TRAVERSABLE) == self::TRAVERSABLE);
	}

	/**
	 *
	 * Get the list of keys of the given container
	 *
	 * @param mixed $container
	 *        	Input container
	 * @throws InvalidContainerException
	 * @return array Numerically indexed array of keys used in $container
	 */
	public static function keys($container)
	{
		if (\is_array($container))
			return \array_keys($container);

		if (self::isTraversable($container))
		{
			$keys = [];
			foreach ($container as $key => $value)
				$keys[] = $key;
			return $keys;
		}

		if ($container instanceof ArrayRepresentation)
			return \array_keys($container->getArrayCopy());

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 *
	 * @param mixed $container
	 *        	Input container
	 * @return boolean true if $container does not contains any element
	 */
	public static function isEmpty($container)
	{
		return (self::count($container) == 0);
	}

	/**
	 * Get the list of values of the given container.
	 *
	 * @param mixed $container
	 *        	Input container
	 * @throws InvalidContainerException
	 * @return mixed[] Numerically indexed array of values contained in $container
	 */
	public static function values($container)
	{
		if (\is_array($container))
			return \array_values($container);

		if (self::isTraversable($container))
		{
			$values = [];
			foreach ($container as $value)
				$values[] = $value;

			return $values;
		}

		if ($container instanceof ArrayRepresentation)
			return \array_values($container->getArrayCopy());

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Remove an entry from the given container
	 *
	 * @param mixed $container
	 * @param mixed $key
	 *        	Entry key to remove
	 * @throws InvalidContainerException
	 * @return boolean true if $container was modified
	 */
	public static function removeKey(&$container, $key)
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

		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Transform any type to a plain PHP array
	 *
	 * @param mixed $anything
	 * @param number $singleElementKey
	 *        	Key used to create a single element array when is not something that could be
	 *        	converted to an array
	 * @return array or null if $anything cannont be converted to array and $singleElementKey is
	 *         null
	 */
	public static function createArray($anything,
		$singleElementKey = null)
	{
		if (\is_array($anything))
			return $anything;
		elseif ($anything instanceof \ArrayObject ||
			$anything instanceof ArrayRepresentation)
			return $anything->getArrayCopy();

		if ($anything instanceof \JsonSerializable)
		{
			$j = $anything->jsonSerialize();
			if (\is_array($j))
				return $j;
		}

		if (self::isTraversable($anything))
		{
			$a = [];
			foreach ($anything as $k => $v)
				$a[$k] = $v;
			return $a;
		}

		if ($singleElementKey !== null)
			return [
				$singleElementKey => $anything
			];

		throw new InvalidContainerException($anything);
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
	 * @return boolean true if the container keys is a non-sparse sequence of integer
	 *         starting from 0 to n-1 (where n is the number of elements of the container).
	 */
	public static function isIndexed($container, $strict = false)
	{
		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);

		if ($strict)
		{
			$c = Container::count($container);
			if ($c == 0)
				return true;
			$range = \range(0, $c - 1);
			$keys = Container::keys($container);
			return ($keys === $range);
		}

		$i = 0;
		foreach ($container as $key => $value)
		{
			if (!(\is_integer($key) || \ctype_digit($key)))
				return false;
			if ($i != \intval($key))
				return false;
			$i++;
		}

		return true;
	}

	/**
	 * Iterate container and return the value of the nth element.
	 *
	 * @param mixed $container
	 *        	A tranversable container
	 * @param integer $offset
	 *        	Index of the expected value
	 * @param mixed $dflt
	 *        	Value to return if the expected index does not exists
	 * @throws InvalidContainerException
	 * @return The nth value of the container
	 */
	public static function nthValue($container, $offset, $dflt = null)
	{
		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);

		foreach ($container as $value)
			if ($offset-- == 0)
				return $value;

		return $dflt;
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
	 *         or if the array keys are not consecutive values. An empty container is considered as
	 *         associative
	 */
	public static function isAssociative($container, $strict = false)
	{
		if (!self::isTraversable($container))
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
	 * @return integer Number of elements in $container
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
	public static function valueExists($container, $value,
		$strict = false)
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
	 * Get the first key and value of the given container.
	 *
	 * @param mixed $container
	 * @throws InvalidContainerException
	 * @return array Array of two elements containing the first key and value of the given
	 *         container. If the container is empty, both return values will be NULL.
	 *
	 *         The list() function can be used to get the result of this function.
	 */
	public static function first($container, $dflt = array())
	{
		if ($container instanceof \Iterator)
		{
			$i = clone $container;
			$i->rewind();
			if ($i->valid())
				return [
					$i->key(),
					$i->current()
				];

			return [
				Container::keyValue($dflt, 0, null),
				Container::keyValue($dflt, 1, null)
			];
		}

		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);

		foreach ($container as $key => $value)
			return [
				$key,
				$value
			];

		return [
			Container::keyValue($dflt, 0, null),
			Container::keyValue($dflt, 1, null)
		];
	}

	/**
	 * Get the first key of the given container
	 *
	 * @param mixed $container
	 * @param mixed $key
	 *        	Value to return if $container is empty
	 * @return mixed
	 */
	public static function firstKey($container, $dflt = null)
	{
		list ($k, $v) = self::first($container, [
			$dflt,
			null
		]);
		return $k;
	}

	/**
	 * Get the first value of the container.
	 *
	 * @param mixed $container
	 * @param mixed $dflt
	 *        	Value to return if $container is empty
	 * @return mixed
	 */
	public static function firstValue($container, $dflt = null)
	{
		list ($k, $v) = self::first($container, [
			null,
			$dflt
		]);
		return $v;
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
	public static function keyValue($container, $key,
		$defaultValue = null)
	{
		if (\is_array($container))
			return (\array_key_exists($key, $container)) ? $container[$key] : $defaultValue;
		elseif ($container instanceof \ArrayAccess)
			return ($container->offsetExists($key) ? $container->offsetGet(
				$key) : $defaultValue);
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
			if (\property_exists($container, $key) ||
				\method_exists($container, '__get'))
				return $container->$key;

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
			if (\property_exists($container, $key) ||
				\method_exists($container, '__set'))
			{
				$container->$key = $value;
				return;
			}

			throw new \InvalidArgumentException(
				$key . ' is not a member of ' .
				TypeDescription::getName($container));
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
	public static function implodeValues($container, $glue,
		$callable = null, $callableArguments = array())
	{
		$a = '';
		$b = '';
		$i = $glue;
		$p = $glue;
		if (self::isArray($glue))
		{
			$b = self::keyValue($glue, self::IMPLODE_BEFORE, '');
			$a = self::keyValue($glue, self::IMPLODE_AFTER, '');
			$i = self::keyValue($glue, self::IMPLODE_BETWEEN, '');
			$p = self::keyValue($glue, self::IMPLODE_BETWEEN_LAST, $i);
		}
		if (\is_callable($callable))
		{
			$parts = [];
			foreach ($container as $k => $v)
			{
				$part = call_user_func_array($callable,
					\array_merge([
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
	public static function implodeKeys($container, $glue,
		$callable = null, $callableArguments = array())
	{
		$a = '';
		$b = '';
		$i = $glue;
		$p = $glue;
		if (self::isArray($glue))
		{
			$b = self::keyValue($glue, self::IMPLODE_BEFORE, '');
			$a = self::keyValue($glue, self::IMPLODE_AFTER, '');
			$i = self::keyValue($glue, self::IMPLODE_BETWEEN, '');
			$p = self::keyValue($glue, self::IMPLODE_BETWEEN_LAST, $i);
		}

		if (\is_callable($callable))
		{
			$parts = [];
			foreach ($container as $k => $v)
			{
				$part = call_user_func_array($callable,
					\array_merge([
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
	public static function implode($container, $glue, $callable,
		$callableArguments = array())
	{
		$a = '';
		$b = '';
		$i = $glue;
		$p = $glue;

		if (self::isArray($glue))
		{
			$b = self::keyValue($glue, self::IMPLODE_BEFORE, '');
			$a = self::keyValue($glue, self::IMPLODE_AFTER, '');
			$i = self::keyValue($glue, self::IMPLODE_BETWEEN, '');
			$p = self::keyValue($glue, self::IMPLODE_BETWEEN_LAST, $i);
		}

		$parts = [];
		foreach ($container as $k => $v)
		{
			$part = call_user_func_array($callable,
				\array_merge([
					$k,
					$v
				], $callableArguments));

			if ($part !== false)
				$parts[] = $part;
		}

		return self::implodeParts($parts, $b, $i, $p, $a);
	}

	/**
	 *
	 * @param array|\Traversable $container
	 * @param callable $callable
	 *        	Filter callable invoked for each element o.f $container.
	 *        	The prototype must be function ($key, $value) : boolean
	 * @return array Filtered container
	 */
	public static function filter($container, $callable)
	{
		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);

		$result = [];
		foreach ($container as $key => $value)
		{
			if (\call_user_func($callable, $key, $value))
				$result[$key] = $value;
		}

		return $result;
	}

	/**
	 * Returns an array containing the results of applying the callback to each of input container
	 * elements.
	 *
	 * @param mixed $container
	 *        	Container
	 * @param callable $callable
	 *        	Callable to apply on each $container elements
	 * @throws InvalidContainerException
	 * @return array
	 *
	 * @see https://www.php.net/manual/en/function.array-map.php
	 */
	public static function map($container, $callable)
	{
		$properties = self::properties($container);
		$expected = self::TRAVERSABLE;
		if (($properties & $expected) != $expected)
			throw new InvalidContainerException($container);

		$args = \array_slice(func_get_args(), 2);

		$result = [];

		if (self::count($args))
			foreach ($container as $key => $value)
				$result[$key] = \call_user_func_array($callable,
					\array_merge([
						$key,
						$value
					], $args));
		else
			foreach ($container as $key => $value)
				$result[$key] = \call_user_func($callable, $key, $value);

		return $result;
	}

	/**
	 * Applies the user-defined callback function to each element of the container $container.
	 *
	 * @param mixed $container
	 *        	Container
	 * @param callable $callable
	 *        	Callable to apply on each elements of $container
	 * @throws InvalidContainerException
	 * @return $container
	 */
	public static function walk(&$container, $callable)
	{
		$properties = self::properties($container);
		$expected = self::TRAVERSABLE | self::RANDOM_ACCESS |
			self::MODIFIABLE;
		if (($properties & $expected) != $expected)
			throw new InvalidContainerException($container);

		$args = \array_slice(func_get_args(), 2);

		if (self::count($args))
			foreach ($container as $key => $value)
				self::setValue($container, $key,
					\call_user_func_array($callable,
						\array_merge([
							$key,
							$value
						], $args)));
		else
			foreach ($container as $key => $value)
				self::setValue($container, $key,
					\call_user_func($callable, $key, $value));

		return $container;
	}

	/**
	 * Sort array values and maintain index association
	 *
	 * @param array|\ArrayObject $container
	 * @param integer $flags
	 * @throws InvalidContainerException
	 * @return boolean
	 *
	 * @see https://www.php.net/manual/en/function.asort.php
	 */
	public static function asort(&$container, $flags = \SORT_REGULAR)
	{
		if (\is_array($container))
			return \asort($container, $flags);
		elseif ($container instanceof \ArrayObject)
			return $container->asort($flags);
		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Sort an array by keys
	 *
	 * @param array|\ArrayObject $container
	 * @param integer $flags
	 * @throws InvalidContainerException
	 * @return boolean
	 *
	 * @see https://www.php.net/manual/en/function.ksort.php
	 */
	public static function ksort(&$container, $flags = \SORT_REGULAR)
	{
		if (\is_array($container))
			return \ksort($container, $flags);
		elseif ($container instanceof \ArrayObject)
			return $container->ksort($flags);
		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Sort an array using a "natural order" algorithm
	 *
	 * @param array|\ArrayObject $container
	 * @throws InvalidContainerException
	 * @return boolean
	 *
	 * @see https://www.php.net/manual/en/function.natsort.php
	 */
	public static function natsort(&$container)
	{
		if (\is_array($container))
			return \natsort($container);
		elseif ($container instanceof \ArrayObject)
			return $container->natsort();
		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Sort an array using a case insensitive "natural order" algorithm
	 *
	 * @param array|\ArrayObject $container
	 * @throws InvalidContainerException
	 * @return boolean
	 *
	 * @see https://www.php.net/manual/en/function.natcasesort.php
	 */
	public static function natcasesort(&$container)
	{
		if (\is_array($container))
			return \natcasesort($container);
		elseif ($container instanceof \ArrayObject)
			return $container->natcasesort();
		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Sort an array with a user-defined comparison function and maintain index associatio
	 *
	 * @param array|\ArrayObject $container
	 * @param callable $callable
	 *        	Value comparison function
	 * @throws InvalidContainerException
	 * @return boolean
	 *
	 * @see https://www.php.net/manual/en/function.uasort.php
	 */
	public static function uasort(&$container, $callable)
	{
		if (\is_array($container))
			return \uasort($container, $callable);
		elseif ($container instanceof \ArrayObject)
			return $container->uasort($callable);
		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 * Sort an array by keys using a user-defined comparison function
	 *
	 * @param array|\ArrayObject $container
	 * @param callable $callable
	 *        	Key comparison function
	 * @throws InvalidContainerException
	 * @return boolean
	 *
	 * @see https://www.php.net/manual/en/function.uksort.php
	 */
	public static function uksort(&$container, $callable)
	{
		if (\is_array($container))
			return \uksort($container, $callable);
		elseif ($container instanceof \ArrayObject)
			return $container->uksort($callable);
		throw new InvalidContainerException($container, __METHOD__);
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

