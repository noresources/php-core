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

class ArrayUtil
{

	/**
	 * Remove a key from an array
	 *
	 * @param string $key
	 * @param array $table Key association is preserved in the result array
	 *       
	 * @return A new array that does not contains @param $key
	 */
	public static function removeKey($table, $key)
	{
		if (!array_key_exists($key, $table))
		{
			return $table;
		}
		
		$newArray = array ();
		foreach ($table as $k => $v)
		{
			if ($k != $key)
			{
				$newArray[$k] = $v;
			}
		}
		
		return $newArray;
	}

	/**
	 * Indicates if the parameter is an array or an object which
	 * implements ArrayAccess interface (PHP 5)
	 *
	 * @param mixed $table
	 */
	public static function isArray($table)
	{
		return (\is_array($table) || (\is_object($table) && ($table instanceof \ArrayAccess)));
	}

	/**
	 * Transform any type to a plain PHP array
	 * @param mixed $anything
	 * @param number $singleElementKey Key used to create a single element array when @param is not something that could be
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
	 * @param array $values
	 * @return boolean @true if at least one of @param $values keys is not a integer
	 *         or if the array keys are not consecutive values
	 */
	public static function isAssociative(&$values)
	{
		if (!self::isArray($values))
		{
			return false;
		}
		
		$itemCount = count($values);
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
	 * count accepts both <code>array</code> and <code>Countable</code>
	 * implementation
	 *
	 * @param mixed $table array or Countable object
	 * @return int
	 * @todo rename into array_count
	 */
	public static function count($table)
	{
		if (\is_array($table))
		{
			return (\count($table));
		}
		
		return (\is_object($table) && ($table instanceof \Countable)) ? $table->count() : false;
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
		elseif (\is_object($table) && ($table instanceof \ArrayAccess))
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
	 * @param mixed $table array or ArrayAccess implementation
	 * @return boolean
	 */
	public static function keyExists($table, $key)
	{
		if (\is_array($table))
		{
			return (\array_key_exists($key, $table));
		}
		elseif (\is_object($table) && ($table instanceof \ArrayAccess))
		{
			return $table->offsetExists($key);
		}
		elseif (\is_array($key))
		{
			return (\array_key_exists($table, $key));
		}
		elseif (\is_object($key) && ($key instanceof \ArrayAccess))
		{
			return $key->offsetExists($table);
		}
		
		return false;
	}

	/**
	 * Retrieve key value or a default value if key doesn't exists
	 *
	 * @param array $table
	 * @param mixed $key
	 * @param mixed $a_defaultValue
	 */
	public static function keyValue(&$table, $key, $a_defaultValue)
	{
		if (\is_array($table))
		{
			return (\array_key_exists($key, $table)) ? $table[$key] : $a_defaultValue;
		}
		
		if (is_object($table) && ($table instanceof \ArrayAccess))
		{
			return ($table->offsetExists($key) ? $table->offsetGet($key) : $a_defaultValue);
		}
		
		return $a_defaultValue;
	}

	/**
	 * Implode array values
	 *
	 * @param array $table Input array
	 * @param string $glue Element glue
	 * @return string
	 */
	public static function implodeValues($table, $glue)
	{
		if (self::isArray($glue) && is_string($table))
		{
			$a = $glue;
			$glue = $table;
			$table = $a;
		}
		
		if (!self::isArray($table) || count($table) == 0)
		{
			return '';
		}
		
		return (\implode($glue, $table));
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
	public static function implodeKeys($table, $glue)
	{
		if (self::isArray($glue) && is_string($table))
		{
			$a = $glue;
			$glue = $table;
			$table = $a;
		}
		
		if (!self::isArray($table) || count($table) == 0)
		{
			return '';
		}
		
		// php 5.1 does not support "class::method" syntax
		$result = '';
		
		foreach ($table as $k => $v)
		{
			$r = $k;
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

	/**
	 * Implode a array
	 *
	 * @param array $table Array to implode
	 * @param string $glue Glue
	 * @param callable $callback
	 * @param string $callbackArguments
	 */
	public static function implode($table, $glue, $callback, $callbackArguments = null)
	{
		if (self::isArray($glue) && is_string($table))
		{
			$a = $glue;
			$glue = $table;
			$table = $a;
		}
		
		if (!self::isArray($table) || count($table) == 0)
		{
			return '';
		}
		
		// php 5.1 does not support "class::method" syntax
		$regs = array ();
		if (is_string($callback) && preg_match('/([^:]+)::(.+)/', $callback, $regs))
		{
			$callback = array (
					$regs[1],
					$regs[2] 
			);
		}
		
		$result = '';
		
		if (!self::isArray($callbackArguments))
		{
			$callbackArguments = array (
					$callbackArguments 
			);
		}
		
		foreach ($table as $k => $v)
		{
			$r = call_user_func_array($callback, array_merge(array (
					$k,
					$v 
			), $callbackArguments));
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
