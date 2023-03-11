<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

use NoreSources\Text\StructuredText;
use NoreSources\Type\ArrayRepresentation;
use NoreSources\Type\TypeConversion;

/**
 * Serializable data tree structure
 */
class DataTree implements \ArrayAccess, \Serializable,
	\IteratorAggregate, \Countable, ArrayRepresentation,
	\JsonSerializable
{

	/**
	 * Content fusion mode.
	 *
	 * Replace existing data with new content.
	 *
	 * @var integer
	 */
	const REPLACE = 0x01;

	/**
	 * Content fusion mode.
	 *
	 * <ul>
	 * <li>Merge exising data with new content.</li>
	 * <li>Append new keys</li>
	 * </ul>
	 *
	 *
	 * @var integer
	 */
	const MERGE = 0x02;

	/**
	 * Content fusion mode.
	 *
	 * <ul>
	 * <li>Merge existing content with new content.</li>
	 * <li>Overwrite existing key values.</li>
	 * </ul>
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

		if (self::isTraversable($data))
			$this->setContent($data, self::REPLACE);
	}

	/**
	 * Deep copy of the tree
	 */
	public function __clone()
	{
		$data = $this->getArrayCopy();
		$this->elements = new \ArrayObject();
		$this->setContent($data, self::REPLACE);
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
	 * Indicates if a element key exists
	 *
	 * @param string $key
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists($key)
	{
		return $this->elements->offsetExists($key);
	}

	/**
	 * Indicates if a element key exists
	 *
	 * @param string $key
	 */
	public function has($key)
	{
		return $this->offsetExists($key);
	}

	/**
	 * Get a value associated to a key
	 *
	 * @param mixed $key
	 *        	Key
	 * @return The element value or <code>NULL</code> if the key does not exists
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($key)
	{
		if ($this->elements->offsetExists($key))
		{
			return $this->elements->offsetGet($key);
		}

		return null;
	}

	/**
	 * Get a value associated to a key
	 *
	 * This method follow the PSR-11 implementation requirements.
	 * It is proposed as an interoperability effort.
	 * However, the behavior of this method differs from the regular behavior of DataTree.
	 *
	 * @param string $key
	 *        	Key. According to PSR-11, $key MUST be a string
	 *
	 * @throws DataTreeElementNotFoundException
	 * @return The element value or <code>NULL</code> if the key does not exists
	 *
	 * @see https://www.php-fig.org/psr/psr-11/
	 */
	public function get($key)
	{
		if (!\is_string($key))
			$key = TypeConversion::toString($key);
		if (!$this->offsetExists($key))
			throw new DataTreeElementNotFoundException($this, $key);
		return $this->offsetGet($key);
	}

	/**
	 * Set a element value
	 *
	 * @param integer $key
	 *        	Setting key
	 * @param integer $value
	 *        	Setting value
	 *
	 * @throws \Exception
	 */
	#[\ReturnTypeWillChange]
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
	 * @param integer $mode
	 *        	Fusion mode
	 * @throws \Exception
	 */
	public function setElement($key, $value, $mode = self::REPLACE)
	{
		if (($mode & self::_FUSION_MODES) == 0)
			throw new \InvalidArgumentException('Invalid mode value');

		$exists = $this->elements->offsetExists($key);
		if (!$exists)
		{
			if (!self::isTraversable($value))
			{
				$this->elements->offsetSet($key, $value);
				return;
			}

			$entry = new DataTree();
			foreach ($value as $k => $v)
				$entry->setElement($k, $v, $mode);
			$this->elements->offsetSet($key, $entry);
			return;
		}

		if (!($mode & self::REPLACE) &&
			!(($mode & self::MERGE_OVERWRITE) == self::MERGE_OVERWRITE))
		{
			return;
		}

		// Mode is REPLACE or MERGE_OVERWRITE

		/**
		 * Always replace when new value is a plain data or a list
		 * of values (indexed array)
		 */

		if (!self::isTraversable($value) || Container::isIndexed($value))
		{
			$this->elements->offsetSet($key, $value);
			return;
		}

		$existing = $this->elements->offsetGet($key);
		$entry = null;

		if ($existing instanceof DataTree &&
			!(($mode & self::REPLACE) || Container::isIndexed($existing)))
			$entry = $existing;

		if (!($entry instanceof DataTree))
			$entry = new DataTree();

		foreach ($value as $k => $v)
			$entry->setElement($k, $v, $mode);

		$this->elements->offsetSet($key, $entry);
	}

	/**
	 * Unset a element Key/Value pair
	 *
	 * @param mixed $key
	 *        	Setting key
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset($key)
	{
		$this->elements->offsetUnset($key);
	}

	// IteratorAggregate ////////////////////

	// TiteratorAggregate
	#[\ReturnTypeWillChange]
	public function getIterator()
	{
		return $this->elements->getIterator();
	}

	// Countable ////////////////////

	/**
	 *
	 * @return Number of child element
	 */
	#[\ReturnTypeWillChange]
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
	 * Load element table from JSON
	 *
	 * @param string $serialized
	 *        	A JSON string
	 */
	public function unserialize($serialized)
	{
		$this->elements->exchangeArray(
			StructuredText::parseText($serialized,
				StructuredText::FORMAT_JSON));

		$this->setContent($data, self::REPLACE);
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
			$a[$key] = ($value instanceof DataTree) ? $value->getArrayCopy() : $value;
		}

		return $a;
	}

	/**
	 * Return the element value or the given default value if the setting is not present
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
					break;

				$k = $key[$i];
				if ($t->offsetExists($k))
					$t = $t->offsetGet($k);
				else
					break;

				$i++;
			}

			return ($i == $c) ? $t : $defaultValue;
		}

		if ($this->elements->offsetExists($key))
			return $this->elements->offsetGet($key);

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
		$c = \array_unshift($a, $value);
		$this->elements->exchangeArray($a);
		return $c;
	}

	/**
	 *
	 * @param array $data
	 *        	Data tree content
	 * @param integer $mode
	 *        	Fusion mode
	 * @throws \ErrorException
	 *
	 * @return DataTree
	 */
	public function setContent($data, $mode = self::REPLACE)
	{
		if (!self::isTraversable($data))
			throw new DataTreeNotTraversableException($this, $data);

		if (($mode & self::REPLACE) == self::REPLACE)
			$this->elements->exchangeArray([]);
		else
			$mode |= self::MERGE;

		foreach ($data as $key => $value)
			$this->setElement($key, $value, $mode);

		return $this;
	}

	/**
	 * Load a DataTree from a file
	 *
	 * @param string $filename
	 *        	File name. File format could be
	 *        	<ul>
	 *        	<li>JSON</li>
	 *        	<li>YAML</li>
	 *        	<li>Ini</li>
	 *        	<li>php</li>
	 *        	</ul>
	 *
	 *        	PHP files are loaded with the require() function and expect a Traversable return
	 *        	value.
	 *
	 *        	Several media type support depends on available PHP extensions.
	 * @param integer $mode
	 *        	Control interaction with existing content
	 * @param string|null $mediaType
	 *        	Specify file media type. If null, media type is automatically detected
	 * @throws \InvalidArgumentException
	 * @return DataTree
	 */
	public function loadFile($filename, $mode = self::REPLACE,
		$mediaType = null)
	{
		if (!\file_exists($filename))
			throw new \InvalidArgumentException(
				$filename . ' not found');

		$extension = \strtolower(
			\pathinfo($filename, PATHINFO_EXTENSION));

		if (!(\is_string($mediaType) && \strlen($mediaType)))
		{
			if (\function_exists('mime_content_type'))
				$mediaType = @mime_content_type($filename);
			elseif (\class_exists('finfo'))
			{
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$mediaType = $finfo->file($filename);
			}
		}

		if ($mediaType == 'text/x-php' || $extension == 'php')
		{
			$result = require ($filename);
			if (!self::isTraversable($result))
				throw new DataTreeNotTraversableException($result);
			$this->setContent($result, $mode);
			return $this;
		}

		return $this->setContent(
			StructuredText::parseFile($filename, $mediaType), $mode);
	}

	public function loadData($data, $structuredTextFormat,
		$mode = self::REPLACE)
	{
		return $this->setContent(
			StructuredText::parseText($data, $structuredTextFormat),
			$mode);
	}

	private static function isTraversable($e)
	{
		return (\is_array($e) || ($e instanceof \Traversable));
	}

	/**
	 * Data tree map
	 *
	 * @var \ArrayObject
	 */
	private $elements;

	/**
	 * Combination of all fusion modes flags
	 *
	 * @var number
	 */
	const _FUSION_MODES = 0x07;
}
