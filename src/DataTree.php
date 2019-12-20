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

/**
 * Serializable data tree structure
 */
class DataTree implements \ArrayAccess, \Serializable, \IteratorAggregate, \Countable,
	ArrayRepresentation, \JsonSerializable
{

	/**
	 * Do not allow to add new keys
	 *
	 * @var integer
	 */
	const RESTRICTED_KEYS = 0x01;

	/**
	 * Do not allow to change any value
	 *
	 * @var integer
	 */
	const READ_ONLY = 0x02;

	/**
	 * Do not raise exception on set/get error
	 *
	 * @var integer
	 */
	const SILENT = 0x04;

	const REPLACE = 0x01;

	/**
	 * Merge exising data with new content.
	 * Append new key
	 *
	 * @var integer
	 */
	const MERGE = 0x02;

	/**
	 * Merge existing content with new content.
	 * Overwrite existing key values.
	 *
	 * @var integer
	 */
	const MERGE_OVERWRITE = 0x06;

	/**
	 *
	 * @param array $data
	 *        	Initial data
	 */
	public function __construct($data = [])
	{
		$this->elements = new \ArrayObject();
		if (Container::isTraversable($data, true))
			$this->setContent($data, $mode = self::REPLACE);

		$this->dataTreeFlags = 0;
	}

	/**
	 * String representation
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->serialize();
	}

	/**
	 * Equivalent of offsetGet
	 *
	 * @param string $key
	 *        	Key
	 */
	public function __get($key)
	{
		return $this->offsetGet($key);
	}

	/**
	 * Equivalent of offsetSet
	 *
	 * @param string $key
	 *        	Key
	 * @param mixed $value
	 *        	Value
	 */
	public function __set($key, $value)
	{
		$this->offsetSet($key, $value);
	}

	// ArrayAccess ////////////////////

	/**
	 * Indicates if a setting key exists
	 *
	 * @param string $key
	 */
	public function offsetExists($key)
	{
		return $this->elements->offsetExists($key);
	}

	/**
	 * Get a value associated to a key
	 *
	 * @param mixed $key
	 *        	Key
	 * @return The setting value or <code>NULL</code> if the key does not exists
	 */
	public function offsetGet($key)
	{
		if ($this->elements->offsetExists($key))
		{
			return $this->elements->offsetGet($key);
		}

		return (is_callable($this->defaultValueHandler) ? (call_user_func(
			$this->defaultValueHandler, $key, $this)) : null);
	}

	/**
	 * Set a setting value
	 *
	 * @param integer $key
	 *        	Setting key
	 * @param integer $value
	 *        	Setting value
	 *
	 * @throws \Exception
	 */
	public function offsetSet($key, $value)
	{
		$this->setElement($key, $value, self::REPLACE);
	}

	/**
	 * Set element of a DataTree
	 *
	 * @param string|integer $key
	 *        	Element key
	 * @param mixed $value
	 *        	Element value
	 * @param unknown $mode
	 *        	Fusion mode
	 * @throws \Exception
	 */
	public function setElement($key, $value, $mode = self::REPLACE)
	{
		if ($this->dataTreeFlags & self::READ_ONLY)
		{
			if ($this->dataTreeFlags & self::SILENT)
			{
				return;
			}

			throw new \Exception('Read only setting table');
		}

		if (($this->dataTreeFlags & self::RESTRICTED_KEYS) && !$this->elements->offsetExists($key))
		{
			if ($this->dataTreeFlags & self::SILENT)
			{
				return;
			}

			throw new \Exception('New key are not accepted');
		}

		$exists = $this->elements->offsetExists($key);

		if ($exists)
		{
			if (!($mode & self::REPLACE) &&
				!(($mode & self::MERGE_OVERWRITE) == self::MERGE_OVERWRITE))
				return;
		}

		if (Container::isTraversable($value))
		{
			$st = null;
			if ($exists)
			{
				$existing = $this->elements->offsetGet($key);
				if ($existing instanceof DataTree && !($mode & self::REPLACE))
					$st = $existing;
			}

			if (!($st instanceof DataTree))
			{
				$st = new DataTree();
				$st->defaultValueHandler = $this->defaultValueHandler;
				$st->dataTreeFlags = $this->dataTreeFlags;
			}

			foreach ($value as $k => $v)
				$st->setElement($k, $v, $mode);

			$this->elements->offsetSet($key, $st);
		}
		else
			$this->elements->offsetSet($key, $value);
	}

	/**
	 * Unset a setting Key/Value pair
	 *
	 * @param mixed $key
	 *        	Setting key
	 */
	public function offsetUnset($key)
	{
		$this->elements->offsetUnset($key);
	}

	// IteratorAggregate ////////////////////

	// TiteratorAggregate
	public function getIterator()
	{
		return $this->elements->getIterator();
	}

	// Countable ////////////////////

	// Contable
	public function count()
	{
		return $this->elements->count();
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see JsonSerializable::jsonSerialize()
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Serialize table to JSON
	 */
	public function serialize()
	{
		return \json_encode($this->jsonSerialize());
	}

	/**
	 * Load setting table from JSON
	 *
	 * @param string $serialized
	 *        	A JSON string
	 */
	public function unserialize($serialized)
	{
		$this->elements->exchangeArray(self::dataFromJson($serialized));

		foreach ($this->elements as $key => &$value)
		{
			if (\is_array($value))
			{
				$this->offsetSet($key, $value);
			}
		}
	}

	/**
	 * Convert the DataTree to a regular PHP array
	 *
	 * @return array
	 */
	public function getArrayCopy()
	{
		$a = [];
		foreach ($this->elements as $key => $value)
		{
			$a[$key] = (is_object($value) && ($value instanceof DataTree)) ? $value->getArrayCopy() : $value;
		}

		return $a;
	}

	/**
	 * Return the setting value or the given default value if the setting is not present
	 *
	 * @param mixed $key
	 *        	String key or array of string key representing the setting subpath
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getElement($key, $defaultValue = null)
	{
		if (\is_array($key))
		{
			$i = 0;
			$c = count($key);
			$t = $this;

			while ($i < $c)
			{
				if (!($t instanceof DataTree))
				{
					break;
				}

				$k = $key[$i];
				if ($t->offsetExists($k))
				{
					$t = $t->offsetGet($k);
				}
				else
				{
					break;
				}

				$i++;
			}

			return ($i == $c) ? $t : $defaultValue;
		}

		if (array_key_exists($key, $this->elements))
		{
			return $this->elements[$key];
		}

		return $defaultValue;
	}

	/**
	 * Insert an indexed value at the end of the setting table
	 *
	 * @param mixed $value
	 * @return New number of settings
	 */
	public function append($value)
	{
		$this->elements->append($value);
		return $this->elements->count();
	}

	/**
	 * Insert an indexed value at the beginning of the setting table
	 *
	 * @param mixed $value
	 * @return New number of settings
	 */
	public function prepend($value)
	{
		$a = $this->elements->getArrayCopy();
		$c = array_unshift($a, $value);
		$this->elements->exchangeArray($a);
		return $c;
	}

	/**
	 * Set setting table flags
	 *
	 * @param integer $flags
	 */
	public function setFlags($flags)
	{
		$this->dataTreeFlags = $flags;
	}

	/**
	 * Get setting table flags
	 */
	public function getFlags()
	{
		return $this->dataTreeFlags;
	}

	/**
	 *
	 * @param array $data
	 *        	Data tree content
	 * @param integer $mode
	 *        	Fusion mode
	 * @throws \ErrorException
	 */
	public function setContent($data, $mode = self::REPLACE)
	{
		if (!\is_array($data))
			throw new \ErrorException('Invalid content. Array expected');

		if (($mode & self::REPLACE) == self::REPLACE)
			$this->elements->exchangeArray([]);
		else
			$mode |= self::MERGE;

		foreach ($data as $key => $value)
		{
			$this->setElement($key, $value, $mode);
		}
	}

	public function load($filename, $mode = self::REPLACE, $mediaType = null)
	{
		if (!\file_exists($filename))
			throw new \InvalidArgumentException($filename . ' not found');

		$type = null;
		if ($mediaType === null)
			$type = MediaType::fromMedia($filename);
		elseif (\is_string($type))
			$type = MediaType::fromString($type);

		if (!($type instanceof MediaType))
			throw new \InvalidArgumentException(
				'Invalid mediaType argument (' . TypeDescription::getName($mediaType) . ')');

		$data = file_get_contents($filename);

		if ($type->getStructuredSyntax() == 'json')
			$data = self::dataFromJson($data);

		return $this->setContent($data, $mode);
	}

	/**
	 *
	 * @param callable $callable
	 *        	A function to call when a key does not exists. The function will receive
	 *        	the key and the DataTree in argument and must return a value
	 */
	public function setDefaultValueHandler($callable)
	{
		$this->defaultValueHandler = $callable;
		foreach ($this->elements as $k => &$v)
		{
			if ($v instanceof DataTree)
			{
				$v->setDefaultValueHandler($callable);
			}
		}
	}

	private static function dataFromJson($text)
	{
		$data = @json_decode($text, true);
		$code = json_last_error();
		if ($code != JSON_ERROR_NONE)
		{
			throw new \ErrorException(json_last_error_msg(), $code);
		}

		if (!\is_array($data))
			throw new \ErrorException('Expect object or array. Got ',
				TypeDescription::getName($data));

		return $data;
	}

	/**
	 * Option flags
	 *
	 * @var integer
	 */
	private $dataTreeFlags;

	/**
	 * Setting map
	 *
	 * @var \ArrayObject
	 */
	private $elements;

	/**
	 *
	 * @var mixed
	 */
	private $defaultValueHandler;
}
