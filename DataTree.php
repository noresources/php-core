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
 * Do not allow to add new keys
 *
 * @var integer
 */
const kDataTreeRestrictKeys = 0x1;

/**
 * Do not allow to change any value
 *
 * @var integer
 */
const kDataTreeReadOnly = 0x3;

/**
 * Do not raise exception on set/get error
 *
 * @var integer
 */
const kDataTreeSilent = 0x4;

const kDataTreeFileAuto = 0;

const kDataTreeFilePHP = 1;

const kDataTreeFileJSON = 2;

/**
 * Remove all existing entries, then load all elements of the file
 *
 * @var integer
 */
const kDataTreeLoadReplace = 1;

/**
 * Don't override existing entries
 *
 * @var integer
 */
const kDataTreeLoadAppend = 2;

/**
 * Merge existing entries with the one in the file
 *
 * @var integer
 */
const kDataTreeLoadMerge = 2;

/**
 * Serializable data tree structure
 *
 */
class DataTree implements \ArrayAccess, \Serializable, \IteratorAggregate, \Countable
{

	/**
	 * @param array $data Initial data
	 */
	public function __construct($data = array())
	{
		$this->m_elements = new \ArrayObject();
		if (is_array($data) || ($data instanceof \Traversable))
		{
			foreach ($data as $k => $v)
			{
				$this->offsetSet($k, $v);
			}
		}
		$this->m_flags = 0;
	}

	/**
	 * String representation
	 * @return string
	 */
	public function __toString()
	{
		return $this->serialize();
	}

	/**
	 * Equivalent of offsetGet
	 *
	 * @param unknown $key Key
	 */
	public function __get($key)
	{
		return $this->offsetGet($key);
	}

	/**
	 * Equivalent of offsetSet
	 *
	 * @param unknown $key Key
	 * @param unknown $value Value
	 */
	public function __set($key, $value)
	{
		$this->offsetSet($key, $value);
	}
	
	// ArrayAccess ////////////////////
	

	/**
	 * Indicates if a setting key exists
	 *
	 * @param unknown $key
	 */
	public function offsetExists($key)
	{
		return $this->m_elements->offsetExists($key);
	}

	/**
	 * Get a value associated to a key
	 *
	 * @param mixed $key Key
	 * @return The setting value or <code>NULL</code> if the key does not exists
	 */
	public function offsetGet($key)
	{
		if ($this->m_elements->offsetExists($key))
		{
			return $this->m_elements->offsetGet($key);
		}
		
		return (is_callable($this->m_defaultValueHandler) ? (call_user_func($this->m_defaultValueHandler, $key, $this)) : null);
	}

	/**
	 * Set a setting value
	 *
	 * @param integer $key Setting key
	 * @param integer $value Setting value
	 *       
	 * @throws \Exception
	 */
	public function offsetSet($key, $value)
	{
		if ($this->m_flags & kDataTreeReadOnly)
		{
			if ($this->m_flags & kDataTreeSilent)
			{
				return;
			}
			
			throw new \Exception('Read only setting table');
		}
		
		if (($this->m_flags & kDataTreeRestrictKeys) && !$this->m_elements->offsetExists($key))
		{
			if ($this->m_flags & kDataTreeSilent)
			{
				return;
			}
			
			throw new \Exception('New key are not accepted');
		}
		
		if (\is_array($value) || (is_object($value) && ($value instanceof \Traversable)))
		{
			$st = new DataTree();
			$st->m_defaultValueHandler = $this->m_defaultValueHandler;
			$st->m_flags = $this->m_flags;
			foreach ($value as $k => $v)
			{
				$st->offsetSet($k, $v);
			}
						
			$this->m_elements->offsetSet($key, $st);
		}
		else
		{
			$this->m_elements->offsetSet($key, $value);
		}
	}

	/**
	 * Unset a setting Key/Value pair
	 *
	 * @param mixed $key Setting key
	 */
	public function offsetUnset($key)
	{
		$this->m_elements->offsetUnset($key);
	}
	
	// IteratorAggregate ////////////////////
	

	// TiteratorAggregate
	public function getIterator()
	{
		return $this->m_elements->getIterator();
	}
	
	// Countable ////////////////////
	

	// Contable
	public function count()
	{
		return $this->m_elements->count();
	}
	
	// Serializable ////////////////////
	

	/**
	 * Serialize table to JSON
	 */
	public function serialize()
	{
		return json_encode($this->toArray());
	}

	/**
	 * Load setting table from JSON
	 *
	 * @param string $serialized A JSON string
	 */
	public function unserialize($serialized)
	{
		$this->m_elements = new \ArrayObject(json_decode($serialized, true));
		foreach ($this->m_elements as $key => &$value)
		{
			if (\is_array($value))
			{
				$this->offsetSet($key, $value);
			}
		}
	}

	/**
	 * Convert the DataTree to a regular PHP array
	 * @return array
	 */
	public function toArray()
	{
		$a = array ();
		foreach ($this->m_elements as $key => $value)
		{
			$a[$key] = (is_object($value) && ($value instanceof DataTree)) ? $value->toArray() : $value;
		}
		
		return $a;
	}

	/**
	 * Return the setting value or the given default value if the setting is not present
	 * @param mixed $key String key or array of string key representing the setting subpath
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
		
		if (array_key_exists($key, $this->m_elements))
		{
			return $this->m_elements[$key];
		}
		
		return $defaultValue;
	}
	
	/**
	 * Backward compatible name
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed|ArrayObject|\NoreSources\DataTree|\NoreSources\The
	 */
	public function getSetting($key, $defaultValue = null)
	{
		return $this->getElement($key, $defaultValue);
	}

	/**
	 * Insert an indexed value at the end of the setting table
	 * @param mixed $value
	 * @return New number of settings
	 */
	public function append($value)
	{
		$this->m_elements->append($value);
		return $this->m_elements->count();
	}

	/**
	 * Insert an indexed value at the beginning of the setting table
	 * @param mixed $value
	 * @return New number of settings
	 */
	public function prepend($value)
	{
		$a = $this->m_elements->getArrayCopy();
		$c = array_unshift($a, $value);
		$this->m_elements->exchangeArray($a);
		return $c;
	}

	/**
	 * Set setting table flags
	 *
	 * @param integer $flags
	 */
	public function setFlags($flags)
	{
		$this->m_flags = $flags;
	}

	/**
	 * Get setting table flags
	 */
	public function getFlags()
	{
		return $this->m_flags;
	}

	/**
	 * Load a setting file
	 * - JSON format: Recomended for static settings
	 * - PHP format: For dynamic settings only.
	 * The file is included
	 * and should contains 'set' commands such as <code>$this->key =
	 * 'value';</code>
	 *
	 * @attention Never use this method with untrusted PHP files
	 *
	 * @param string $filename File to load
	 * @param string $filetype One of kDataTreeFile*. If @c kDataTreeFileAuto, the
	 *        file type is
	 *        automatically detected using the file extension
	 */
	public function load($filename, $filetype = kDataTreeFileAuto)
	{
		if ($filetype == kDataTreeFileAuto)
		{
			if (preg_match(chr(1) . '.*\.json' . chr(1) . 'i', $filename))
			{
				$filetype = kDataTreeFileJSON;
			}
			if (preg_match(chr(1) . '.*\.php[0-9]*' . chr(1) . 'i', $filename))
			{
				$filetype = kDataTreeFilePHP;
			}
		}
		
		if ($filetype == kDataTreeFileJSON)
		{
			$elements = json_decode(file_get_contents($filename), true);
			if (\is_array($elements))
			{
				foreach ($elements as $k => $v)
				{
					$this->offsetSet($k, $v);
				}
			}
		}
		elseif ($filetype == kDataTreeFilePHP)
		{
			include ($filename);
		}
	}

	/**
	 *
	 * @param callable $callable A function to call when a key does not exists. The function will receive
	 *        the key and the DataTree in argument and must return a value
	 */
	public function setDefaultValueHandler($callable)
	{
		$this->m_defaultValueHandler = $callable;
		foreach ($this->m_elements as $k => &$v)
		{
			if ($v instanceof DataTree)
			{
				$v->setDefaultValueHandler($callable);
			}
		}
	}

	/**
	 * Option flags
	 *
	 * @var integer
	 */
	private $m_flags;

	/**
	 * Setting map
	 *
	 * @var \ArrayObject
	 */
	private $m_elements;

	private $m_defaultValueHandler;
}
