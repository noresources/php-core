<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

use NoreSources\Reflection\ReflectionService;
use NoreSources\Type\ArrayRepresentation;
use NoreSources\Type\TypeConversion;
use Psr\Container\ContainerInterface;
use ArrayAccess;

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
	 * @param bool $fromContainerPropertyInterface
	 *        	Indicates that the method is invokedee from the
	 *        	ContainerPropertyInterface::getContainerProperties().
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
	public static function properties($container,
		$fromContainerPropertyInterface = false)
	{
		if (!$fromContainerPropertyInterface &&
			$container instanceof ContainerPropertyInterface)
			return $container->getContainerProperties();

		if (\is_array($container))
			return self::TRAVERSABLE | self::EXTENDABLE |
				self::SHRINKABLE | self::COUNTABLE | self::OFFSET_ACCESS;

		$properties = 0;

		if ($container instanceof \ArrayAccess)
			$properties |= self::EXTENDABLE | self::SHRINKABLE |
				self::OFFSET_ACCESS;

		if ($container instanceof ContainerInterface)
			$properties |= self::RANDOM_ACCESS;

		if ($container instanceof \Traversable)
			$properties |= self::TRAVERSABLE;

		if ($container instanceof \Countable)
			$properties |= self::COUNTABLE;

		if (\is_object($container))
		{
			if (($properties & self::TRAVERSABLE) != self::TRAVERSABLE)
			{
				$reflection = ReflectionService::getInstance();
				$class = $reflection->getReflectionClass($container);
				foreach ($class->getProperties() as $property)
				{
					if ($property->IsPublic())
					{
						$properties |= self::TRAVERSABLE;
						break;
					}
				}
			}
		}

		return $properties;
	}

	/**
	 * Indicates if the parameter is an array or an object which
	 * implements ArrayAccess interface (PHP 5)
	 *
	 * @param mixed $container
	 *        	The container
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
	 *        	The container
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
	 * Remove first entry from container and return its value.
	 *
	 * @param mixed $container
	 *        	The container
	 * @throws EmptyContainerException
	 * @return mixed Value of the first entry of the container
	 *
	 * @used-by Container::treeValue()
	 */
	public static function shift(&$container)
	{
		if (self::count($container) == 0)
			throw new EmptyContainerException($container);
		if (\is_array($container))
			return \array_shift($container);
		list ($key, $value) = self::first($container);
		self::removeKey($container, $key);
		return $value;
	}

	/**
	 * Remove last entry from container and return its value.
	 *
	 * @param mixed $container
	 *        	The container
	 * @throws EmptyContainerException
	 * @return mixed Value of the last entry of the container
	 *
	 * @used-by Container::treeValue()
	 */
	public static function pop(&$container)
	{
		if (self::count($container) == 0)
			throw new EmptyContainerException($container);
		if (\is_array($container))
			return \array_pop($container);
		list ($key, $value) = self::last($container);
		self::removeKey($container, $key);
		return $value;
	}

	/**
	 * Transform any type to a plain PHP array
	 *
	 * @param mixed $anything
	 *        	The container
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
	 * @param boolean $allowEmpty
	 *        	Assumes empty array is indexed
	 * @return boolean true if the container keys is a non-sparse sequence of integer
	 *         starting from 0 to n-1 (where n is the number of elements of the container).
	 */
	public static function isIndexed($container, $strict = false,
		$allowEmpty = true)
	{
		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);

		if ($strict)
		{
			$c = self::count($container);
			if ($c == 0)
				return $allowEmpty;
			$range = \range(0, $c - 1);
			$keys = self::keys($container);
			return ($keys === $range);
		}

		$i = 0;
		$c = 0;
		foreach ($container as $key => $value)
		{
			$c++;
			if (!(\is_integer($key) || \ctype_digit($key)))
				return false;
			if ($i != \intval($key))
				return false;
			$i++;
		}

		return $allowEmpty || ($c > 0);
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
	 * @param boolean $allowEmpty
	 *        	Assumes empty array is associative
	 * @throws InvalidContainerException
	 * @return boolean @true if at least one of $container keys is not a integer
	 *         or if the array keys are not consecutive values. An empty container is considered as
	 *         associative
	 */
	public static function isAssociative($container, $strict = false,
		$allowEmpty = true)
	{
		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);

		$i = 0;
		$c = 0;
		if ($strict)
		{
			foreach ($container as $key => $value)
			{
				$c++;
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
				$c++;
				if (!(\is_integer($key) || \ctype_digit($key)))
					return true;
				if ($i != \intval($key))
					return true;
				$i++;
			}
		}
		if ($c == 0)
			return $allowEmpty;
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
	#[\ReturnTypeWillChange]
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
	 *        	The container
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
	 *        	The container
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
	 *        	The container
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
	 *        	The container
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
				self::keyValue($dflt, 0, null),
				self::keyValue($dflt, 1, null)
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
			self::keyValue($dflt, 0, null),
			self::keyValue($dflt, 1, null)
		];
	}

	/**
	 * Get the first key of the given container
	 *
	 * @param mixed $container
	 *        	The container
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
	 *        	The container
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
	 * Get the last key-value pair in the given container
	 *
	 * @param mixed $container
	 *        	Container
	 * @param array $dflt
	 *        	Default key-value to return if container is empty
	 * @throws InvalidContainerException
	 * @return array
	 */
	public static function last($container, $dflt = array())
	{
		$key = self::keyValue($dflt, 0, null);
		$value = self::keyValue($dflt, 1, null);

		if (\is_array($container))
		{
			if (\count($container))
			{
				$keys = \array_keys($container);
				$key = \array_pop($keys);
				$value = $container[$key];
			}
		}

		elseif ($container instanceof \IteratorAggregate)
		{
			// Use default case
		}
		elseif ($container instanceof \Iterator)
		{
			$i = clone $container;
			$i->rewind();

			while ($i->valid())
			{
				$key = $i->key();
				$value = $i->current();
				$i->next();
			}

			return [
				$key,
				$value
			];
		}

		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);

		foreach ($container as $k => $v)
		{
			$key = $k;
			$value = $v;
		}

		return [
			$key,
			$value
		];
	}

	/**
	 * Get the last key of the given container
	 *
	 * @param mixed $container
	 *        	The container
	 * @param mixed $key
	 *        	Value to return if $container is empty
	 * @return mixed
	 */
	public static function lastKey($container, $dflt = null)
	{
		list ($k, $v) = self::last($container, [
			$dflt,
			null
		]);

		return $k;
	}

	/**
	 * Get the last value of the container.
	 *
	 * @param mixed $container
	 *        	The container
	 * @param mixed $dflt
	 *        	Value to return if $container is empty
	 * @return mixed
	 */
	public static function lastValue($container, $dflt = null)
	{
		list ($k, $v) = self::last($container, [
			null,
			$dflt
		]);
		return $v;
	}

	/**
	 * Retrieve key value or a default value if key doesn't exists
	 *
	 * @param array $container
	 *        	The container
	 * @param integer|string $key
	 *        	Container index or key
	 * @param mixed $defaultValue
	 *        	Value to return if $key does not exists in container.
	 *
	 * @throws InvalidContainerException
	 *
	 * @return mixed Value associated to $key or $defaultValue if the key does not exists
	 */
	public static function keyValue($container, $key,
		$defaultValue = null)
	{
		$validContainer = false;
		if (\is_array($container))
			return (\array_key_exists($key, $container)) ? $container[$key] : $defaultValue;

		if (!\is_object($container))
			throw new InvalidContainerException($container, __METHOD__);

		if ($container instanceof \ArrayAccess)
		{
			$validContainer = true;
			if ($container->offsetExists($key))
				return $container->offsetGet($key);
		}

		if ($container instanceof ContainerInterface)
		{
			$validContainer = true;
			if ($container->has($key))
				return $container->get($key);
		}

		if ($container instanceof \Traversable)
		{
			$validContainer = true;
			foreach ($container as $k => $value)
			{
				if ($key === $k)
					return $value;
			}
		}

		$exception = null;
		if (($validContainer = \is_string($key)) &&
			(\property_exists($container, $key) ||
			\method_exists($container, '__get')))
		{
			try
			{
				return $container->$key;
			}
			catch (\Exception $e)
			{
				$exception = $e;
			}
		}

		if ($validContainer)
			return $defaultValue;
		if ($exception)
			throw $exception;
		throw new InvalidContainerException($container, __METHOD__);
	}

	/**
	 *
	 * @param array $container
	 *        	The container
	 * @param array|string|integer $keyTree
	 *        	Container key or key path
	 * @param mixed $defaultValue
	 *        	Value to return if $keyTree does not exists in $container
	 *
	 * @throws InvalidContainerException
	 *
	 * @return mixed Value associated to $keyTree or $defaultValue if the key does not exists
	 */
	public static function treeValue($container, $keyTree, $dflt = null,
		$keySeparator = '.')
	{
		if (\is_string($keyTree) && !empty($keySeparator))
			$keyTree = \explode($keySeparator, $keyTree);

		if (!\is_array($keyTree))
			return self::keyValue($container, $keyTree, $dflt);
		$key = self::shift($keyTree);
		if (!Container::keyExists($container, $key))
			return $dflt;
		$container = self::keyValue($container, $key);

		if (\count($keyTree) == 0)
			return $container;

		return self::treeValue($container, $keyTree, $dflt,
			$keySeparator);
	}

	/**
	 *
	 * @param array|\ArrayAccess|\Traversable $container
	 *        	The container
	 * @param mixed $key
	 *        	Container index or key
	 * @param mixed $value
	 *        	Value to set.
	 *
	 * @throws \InvalidArgumentException
	 * @throws InvalidContainerException
	 */
	public static function setValue(&$container, $key, $value)
	{
		$validContainer = false;
		$exception = null;
		if (\is_object($container))
		{
			if (\is_string($key) &&
				(\property_exists($container, $key) ||
				\method_exists($container, '__set')))
			{
				try
				{
					$container->$key = $value;
					$validContainer = true;
				}
				catch (\Exception $e)
				{
					$exception = $e;
				}
			}
		}

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

		if ($exception)
			throw $exception;
		if ($validContainer)
			return;
		throw new InvalidContainerException($container);
	}

	/**
	 * Add a value at the end of the container value list.
	 *
	 * @param array|ArrayAccess|object $container
	 *        	The container
	 * @param mixed $value
	 *        	Value to append to the end of the container value list.
	 * @throws InvalidContainerException
	 * @throws \Exception
	 */
	public static function appendValue(&$container, $value)
	{
		if (\is_array($container))
		{
			$container[] = $value;
			return;
		}

		if (!\is_object($container))
			throw new InvalidContainerException($container);

		$validContainer = false;
		$exception = null;

		if (\method_exists($container, 'append'))
		{
			$validContainer = true;
			try
			{
				\call_user_func([
					$container,
					'append'
				], $value);
				return;
			}
			catch (\Exception $e)
			{
				$exception = $e;
			}
		}

		if ($container instanceof \ArrayAccess)
		{
			$validContainer = true;
			try
			{
				$container[] = $value;
				return;
			}
			catch (\Exception $e)
			{
				$exception = $e;
			}
		}

		if ($exception)
			throw $exception;
		throw new InvalidContainerException($container);
	}

	/**
	 * Add a value at the beginning of the container value list.
	 *
	 * @param array|\ArrayObject|object $container
	 *        	The container
	 * @param mixed $value
	 *        	Value to add at the beginning of the container value list.
	 * @throws InvalidContainerException
	 * @throws \Exception
	 */
	public static function prependValue(&$container, $value)
	{
		if (\is_array($container))
		{
			\array_unshift($container, $value);
			return;
		}

		if (!\is_object($container))
			throw new InvalidContainerException($container);

		$validContainer = false;
		$exception = null;

		if (\method_exists($container, 'prepend'))
		{
			$validContainer = true;
			try
			{
				\call_user_func([
					$container,
					'prepend'
				], $value);
				return;
			}
			catch (\Exception $e)
			{
				$exception = $e;
			}
		}

		if ($container instanceof \ArrayObject)
		{
			$validContainer = true;
			try
			{
				$a = $container->getArrayCopy();
				\array_unshift($a, $value);
				$container->exchangeArray($a);
				return;
			}
			catch (\Exception $e)
			{
				$exception = $e;
			}
		}

		if ($exception)
			throw $exception;
		if ($validContainer)
			return;
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
	 *        	Function to apply to all entries. Function receive key and value as first
	 *        	arguments.
	 * @param string $callableArguments
	 *        	Additional arguments passed to $callable
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
	 *        	The container
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
	 * Filter container elements by values
	 *
	 * @param array|\Traversable $container
	 *        	Container to filter
	 * @param callable $callable
	 *        	Filter callable invoked for each element of $container.
	 *        	The prototype must be function ($value) : boolean
	 * @return array Filtered container
	 */
	public static function filterValues($container, $callable)
	{
		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);
		if (!\is_callable($callable))
			throw new \InvalidArgumentException(
				'Invalid filter procedure');

		if (\is_array($container))
			return \array_filter($container, $callable);

		$result = [];
		foreach ($container as $key => $value)
		{
			if (\call_user_func($callable, $value))
				$result[$key] = $value;
		}

		return $result;
	}

	/**
	 * Filter container elements by keys
	 *
	 * @param array|\Traversable $container
	 *        	Container to filter
	 * @param callable $callable
	 *        	Filter callable invoked for each element of $container.
	 *        	The prototype must be function ($key) : boolean
	 * @return array Filtered container
	 */
	public static function filterKeys($container, $callable)
	{
		if (!self::isTraversable($container))
			throw new InvalidContainerException($container, __METHOD__);
		if (!\is_callable($callable))
			throw new \InvalidArgumentException(
				'Invalid filter procedure');

		$result = [];
		foreach ($container as $key => $value)
		{
			if (\call_user_func($callable, $key))
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
	 * Returns an array containing the results of applying the callback to each of input container
	 * elements.
	 *
	 * @param mixed $container
	 *        	Container
	 * @param callable $callable
	 *        	Callable to apply on each $container elements.
	 *        	The callable receives as arguments the current array element value and
	 *        	all additional arguments given to the mapValues() method
	 * @throws InvalidContainerException
	 * @return array
	 *
	 * @see https://www.php.net/manual/en/function.array-map.phpValueValue
	 */
	public static function mapValues($container, $callable)
	{
		$properties = self::properties($container);
		$expected = self::TRAVERSABLE;
		if (($properties & $expected) != $expected)
			throw new InvalidContainerException($container);

		$args = \array_slice(func_get_args(), 2);

		$result = [];

		if (self::count($args))
			foreach ($container as $value)
				$result[$key] = \call_user_func_array($callable,
					\array_merge([
						$value
					], $args));
		else
			foreach ($container as $key => $value)
				$result[$key] = \call_user_func($callable, $value);

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
	 *        	The container
	 * @param integer $flags
	 *        	asort function flags
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
	 *        	The container
	 * @param integer $flags
	 *        	ksort flags
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
	 *        	The container
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
	 *        	The container
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
	 *        	The container
	 * @param callable $callable
	 *        	Sort function
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
	 *        	The container
	 * @param callable $callable
	 *        	Sort function
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

	/**
	 * Removes duplicate values from an container
	 *
	 * @param unknown $container
	 *        	Input container
	 * @param unknown $comparer
	 *        	User-defined comparision function.
	 *        	The callback will receive in arguments the current candidate to insertion, one of
	 *        	the already inserted element and any additional argument given to the uniqueValues
	 *        	method.
	 * @throws InvalidContainerException
	 * @throws \InvalidArgumentException
	 * @return mixed[]
	 */
	public static function uniqueValues($container, $comparer = null)
	{
		$properties = self::properties($container);
		$expected = self::TRAVERSABLE;
		if (($properties & $expected) != $expected)
			throw new InvalidContainerException($container);

		if ($comparer === null)
			$comparer = [
				self::class,
				'defaultElementComparer'
			];

		if (!\is_callable($comparer))
			throw new \InvalidArgumentException(
				'Invalid comparison function');

		$args = \array_slice(func_get_args(), 2);

		$map = [];

		if (\count($args) > 0)
		{
			foreach ($container as $key => $a)
			{
				$found = false;
				foreach ($map as $b)
				{
					$c = \call_user_func_array($comparer,
						\array_merge([
							$a,
							$b
						], $args));
					if ($c === 0 || $c === true)
					{
						$found = true;
						break;
					}
				}
				if ($found)
					continue;
				$map[$key] = $a;
			}
		}
		else
		{
			foreach ($container as $key => $a)
			{
				$found = false;
				foreach ($map as $b)
				{
					$c = \call_user_func($comparer, $a, $b);
					if ($c === 0 || $c === true)
					{
						$found = true;
						break;
					}
				}
				if ($found)
					continue;
				$map[$key] = $a;
			}
		}

		return $map;
	}

	const MERGE_RECURSE = 0x01;

	const MERGE_LIST_REPLACE = 0x02;

	/**
	 * Merge two or more container
	 *
	 * @param \Traversable ...$container
	 *        	Containers to merge
	 *        	#param integer $options Merge option flags.. Default is 0.
	 * @throws InvalidContainerException
	 * @return array A new array, containing the merged content of all input containers.
	 */
	public static function merge(/*$arrays..., $options */)
	{
		$argv = \func_get_args();
		$argc = \func_num_args();
		$options = 0;
		$output = [];

		if ($argc > 0)
		{
			if (\is_integer($argv[$argc - 1]))
			{
				$options = \array_pop($argv);
				$argc--;
			}
		}

		if ($argc == 0)
			return $output;

		$recurse = ($options & self::MERGE_RECURSE) ==
			self::MERGE_RECURSE;
		$replace = ($options & self::MERGE_LIST_REPLACE) ==
			self::MERGE_LIST_REPLACE;

		foreach ($argv as $i => $container)
		{
			if (!self::isTraversable($container))
				throw new InvalidContainerException($container,
					'Argument #' . $i);

			$isList = self::isIndexed($container);

			if ($replace && $isList && self::isIndexed($output))
			{
				$output = [];
				foreach ($container as $value)
					$output[] = $value;
				continue;
			}

			if ($isList)
			{
				foreach ($container as $value)
					$output[] = $value;
				continue;
			}

			foreach ($container as $key => $value)
			{
				if ($recurse && self::keyExists($output, $key) &&
					($existing = self::keyValue($output, $key)) &&
					self::isTraversable($existing) &&
					self::isTraversable($value))
				{
					$value = self::merge($existing, $value, $options);
				}

				$output[$key] = $value;
			}
		}

		return $output;
	}

	public static function defaultElementComparer($a, $b)
	{
		return TypeConversion::toString($a) ==
			TypeConversion::toString($b);
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

