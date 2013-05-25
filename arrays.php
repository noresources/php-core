<?php
/**
 * Copyright © 2012 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 * @package NoreSources
 */
namespace NoreSources;

/**
 * @param string $key
 * @param array $table
 * @return array
 */
function array_key_remove($key, &$table)
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
function is_array(&$table)
{
	return (\is_array($table) || (\is_object($table) && ($table instanceof \ArrayAccess)));
}

/**
 * count accepts both @c array and @c Countable implementation
 *
 * @param mixed $table array or Countable implementation object
 * @return int
 * @todo rename into array_count
 */
function count(&$table)
{
	if (\is_array($table))
	{
		return \count($table);
	}

	return (\is_object($table) && ($table instanceof \Countable)) ? $table->count() : false;
}

/**
 * Reset array pointer to initial value
 * or rewind an Iterator
 * @param $table
 * @return unknown_type
 */
function array_reset(&$table)
{
	if (\is_array($table))
	{
		\reset($table);
	}
	elseif (\is_object($table) && ($table instanceof \ArrayAccess))
	{
		$table->rewind();
	}
	else
	{
		throw new \InvalidArgumentException();
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
function array_key_exists($key, &$table)
{
	if (!(\is_string($key) || \is_numeric($key)))
	{
		$key = \var_export($key, true);
	}

	if (\is_array($table))
	{
		return \array_key_exists($key, $table);
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
function array_keyvalue(&$table, $key, $a_defaultValue)
{
	if (!is_array($table))
	{
		return $a_defaultValue;
	}

	return (\array_key_exists($key, $table)) ? $table[$key] : $a_defaultValue;
}

?>
