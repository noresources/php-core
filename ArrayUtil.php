<?php

/**
 * Copyright © 2012-2018 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 * @package Core
 */
namespace NoreSources;

class InvalidContainerException extends \InvalidArgumentException
{

	public function __construct($element, $forMethod = null)
	{
		parent::__construct(TypeDescription::getName($element) . ' is not a valid container' . (\is_string($forMethod) ? ' for method ' . $forMethod : ''));
	}
}

class ContainerUtil
{
	const REMOVE_INPLACE = 1;
	const REMOVE_COPY = 2;
	const IMPLODE_KEYS = 0x01;
	const IMPLODE_VALUES = 0x02;

	public static function removeKey(&$table, $key, $mode = self::REMOVE_COPY)
	{
		if ($mode == self::REMOVE_INPLACE)
		{
			if ($table instanceof \ArrayAccess)
			{
				if ($table->offsetExists($key))
				{
					$table->offsetUnset($key);
				}

				return $table;
			}
			elseif (\is_array($table))
			{
				if (\array_key_exists($key, $table))
				{
					unset($table[$key]);
					return true;
				}

				return $table;
			}

			throw new InvalidContainerException($table, __METHOD__);
		}
		elseif ($mode == self::REMOVE_COPY)
		{
			if ($table instanceof \ArrayAccess)
			{
				$t = clone $table;
				if ($t->offsetExists($key))
				{
					$t->offsetUnset($key);
				}
				return $t;
			}
			elseif (\is_array($table))
			{
				$t = array ();
				foreach ($table as $k => $v)
				{
					if ($k !== $key)
						$t[$k] = $v;
				}
				return $t;
			}

			throw new InvalidContainerException($table, __METHOD__);
		}
		else
			throw new \InvalidArgumentException('mode');
	}

	/**
	 * Indicates if the parameter is an array or an object which
	 * implements ArrayAccess interface (PHP 5)
	 *
	 * @param mixed $table
	 */
	public static function isArray($table)
	{
		return (\is_array($table) || ($table instanceof \ArrayAccess));
	}

	/**
	 * Transform any type to a plain PHP array
	 * @param mixed $anything
	 * @param number $singleElementKey Key used to create a single element array when is not something that could be
	 *        converted to an array
	 * @return array
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
			elseif ($anything instanceof \Traversable)
			{
				$a = array ();
				foreach ($anything as $k => $v)
				{
					$a[$k] = $v;
				}

				return $a;
			}
		}

		return array (
				$singleElementKey => $anything
		);
	}

	/**
	 * Indicates if the given array is an associative array
	 *
	 * @param array|\ArrayAccess|\Traversable $values
	 * @throws InvalidContainerException
	 * @return boolean @true if at least one of $values keys is not a integer
	 *         or if the array keys are not consecutive values
	 */
	public static function isAssociative($values)
	{
		if (!(self::isArray($values) || ($values instanceof \Traversable)))
		{
			throw new InvalidContainerException($values);
		}

		$itemCount = self::count($values);
		$index = 0;

		foreach ($values as $key => $value)
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
	 * @param mixed $table Array, \Countable or \Traversable object
	 * @throws InvalidContainerException
	 * @return number Number of elements in $table
	 *        
	 */
	public static function count($table)
	{
		if (\is_array($table))
			return \count($table);
		elseif ($table instanceof \Countable)
			return $table->count();
		elseif ($table instanceof \Traversable)
		{
			$c = 0;
			foreach ($table as $k => $v)
				$c++;
			return $c;
		}

		throw new InvalidContainerException($table, __METHOD__);
	}

	/**
	 * Reset array pointer to initial value
	 * or rewind an Iterator
	 *
	 * @param $table array to reset
	 * @return boolean
	 */
	public static function reset(&$table)
	{
		if (\is_array($table))
		{
			reset($table);
		}
		elseif ($table instanceof \ArrayAccess)
		{
			$table->rewind();
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * Indicates if a key exists in an array or a ArrayAccess implementation
	 *
	 * @param mixed $key key
	 * @param array|\ArrayAccess|\Traversable $table
	 * @param $table array to reset
	 * @return boolean
	 */
	public static function keyExists($table, $key)
	{
		if (\is_array($table))
		{
			return (\array_key_exists($key, $table));
		}
		elseif ($table instanceof \ArrayAccess)
		{
			return $table->offsetExists($key);
		}
		elseif ($table instanceof \Traversable)
		{
			foreach ($table as $k => $_)
			{
				if ($key === $k)
					return true;
			}

			return false;
		}

		throw new InvalidContainerException($table, __METHOD__);
	}

	/**
	 * Retrieve key value or a default value if key doesn't exists
	 *
	 * @param array $table
	 * @param mixed $key
	 * @param mixed $a_defaultValue
	 */
	public static function keyValue($table, $key, $a_defaultValue)
	{
		if (\is_array($table))
		{
			return (\array_key_exists($key, $table)) ? $table[$key] : $a_defaultValue;
		}

		if ($table instanceof \ArrayAccess)
		{
			return ($table->offsetExists($key) ? $table->offsetGet($key) : $a_defaultValue);
		}
		elseif ($table instanceof \Traversable)
		{
			foreach ($table as $k => $value)
			{
				if ($key === $k)
					return $value;
			}

			return $a_defaultValue;
		}

		throw new InvalidContainerException($element);
	}

	/**
	 * Implode array values
	 *
	 * @param array $table Input array
	 * @param string $glue Element glue
	 * @return string
	 */
	public static function implodeValues($table, $glue, $callable = null, $callableArguments = array ())
	{
		return self::implode($table, $glue, self::IMPLODE_VALUES, $callable, $callableArguments);
	}

	/**
	 * Implode array keys
	 *
	 * @param array $table Table
	 * @param string $glue Element glue
	 *       
	 *        @note This function accepts parameter inversion
	 *
	 * @return string
	 */
	public static function implodeKeys($table, $glue, $callable = null, $callableArguments = array ())
	{
		return self::implode($table, $glue, self::IMPLODE_KEYS, $callable, $callableArguments);
	}

	/**
	 * Implode an array
	 *
	 * @param array $table Array to implode
	 * @param string $glue Glue
	 * @param callable $callable
	 * @param string $callableArguments
	 *
	 * @return string
	 */
	public static function implode($table, $glue, $what, $callable = null, $callableArguments = array())
	{
		if (self::isArray($glue) && is_string($table))
		{
			$a = $glue;
			$glue = $table;
			$table = $a;
		}

		if (!self::isArray($table) || self::count($table) == 0)
		{
			return '';
		}

		$result = '';

		if (!self::isArray($callableArguments))
		{
			$callableArguments = array (
					$callableArguments
			);
		}

		foreach ($table as $k => $v)
		{
			$r = '';
			if (\is_callable($callable))
			{
				$a = array ();
				if ($what & self::IMPLODE_KEYS)
					$a[] = $k;
				if ($what & self::IMPLODE_VALUES)
					$a[] = $v;
				$r = call_user_func_array($callable, array_merge($a, $callableArguments));
			}
			else if ($what & self::IMPLODE_VALUES)
			{
				$r = $v;
			}
			else if ($what & self::IMPLODE_KEYS)
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

class ArrayUtil extends ContainerUtil
{}