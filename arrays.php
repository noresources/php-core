<?php

/**
 * Copyright © 2012-2015 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

/**
 * Remove a key from an array
 *
 * @param string $key        	
 * @param array $table
 *        	Key association is preserved in the result array
 *        	
 * @return A new array that does not contains @param $key
 */
function array_key_remove ($key, &$table)
{
	if (!array_key_exists($key, $table))
	{
		return $table;
	}
	
	$newArray = array();
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
function is_array ($table)
{
	return (\is_array($table) || (\is_object($table) && ($table instanceof \ArrayAccess)));
}

/**
 * Indicates if the given array is an associative array
 *
 * @param array $values        	
 * @return boolean @true if at least one of @param $values keys is not a integer
 *         or if the array keys are not consecutive values
 */
function is_associative_array (&$values)
{
	if (!is_array($values))
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
 * @param mixed $table
 *        	array or Countable object
 * @return int
 * @todo rename into array_count
 */
function count ($table)
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
 * @param $table array
 *        	to reset
 * @return boolean
 */
function array_reset (&$table)
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
 * @param mixed $key
 *        	key
 * @param mixed $table
 *        	array or ArrayAccess implementation
 * @return boolean
 */
function array_key_exists ($key, $table)
{
	if (\is_array($table))
	{
		return (\array_key_exists($key, $table));
	}
	elseif (\is_object($table) && ($table instanceof \ArrayAccess))
	{
		return $table->offsetExists($key);
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
function array_keyvalue (&$table, $key, $a_defaultValue)
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
 * @param array $table
 *        	Input array
 * @param string $glue
 *        	Element glue
 * @return string
 */
function array_implode_values ($table, $glue)
{
	if (is_array($glue) && is_string($table))
	{
		$a = $glue;
		$glue = $table;
		$table = $a;
	}
	
	if (!is_array($table) || count($table) == 0)
	{
		return '';
	}
	
	return (\implode($glue, $table));
}

/**
 * Implode array keys
 *
 * @param array $table
 *        	Table
 * @param string $glue
 *        	Element glue
 *        	
 *        	@note This function accepts parameter inversion
 *        	
 * @return string
 */
function array_implode_keys ($table, $glue)
{
	if (is_array($glue) && is_string($table))
	{
		$a = $glue;
		$glue = $table;
		$table = $a;
	}
	
	if (!is_array($table) || count($table) == 0)
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
 * @param array $table
 *        	Array to implode
 * @param string $glue
 *        	Glue
 * @param callable $callback        	
 * @param string $callbackArguments        	
 */
function array_implode_cb ($table, $glue, $callback, $callbackArguments = null)
{
	if (is_array($glue) && is_string($table))
	{
		$a = $glue;
		$glue = $table;
		$table = $a;
	}
	
	if (!is_array($table) || count($table) == 0)
	{
		return '';
	}
	
	// php 5.1 does not support "class::method" syntax
	$regs = array();
	if (is_string($callback) && preg_match('/([^:]+)::(.+)/', $callback, $regs))
	{
		$callback = array(
				$regs[1],
				$regs[2]
		);
	}
	
	$result = '';
	
	if (!is_array($callbackArguments))
	{
		$callbackArguments = array(
				$callbackArguments
		);
	}
	
	foreach ($table as $k => $v)
	{
		$r = call_user_func_array($callback, array_merge(array(
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
