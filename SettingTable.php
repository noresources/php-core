<?php

/**
 * Copyright © 2012 by Renaud Guillard (dev@nore.fr)
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
const kSettingTableRestrictKeys = 0x1;

/**
 * Do not allow to change any value
 * 
 * @var integer
 */
const kSettingTableReadOnly = 0x3;

/**
 * Do not raise exception on set/get error
 * @var integer
 */
const kSettingTableSilent = 0x4;

const kSettingTableFileAuto = 0;
const kSettingTableFilePHP = 1;
const kSettingTableFileJSON = 2;

/**
 * Remove all existing entries, then load all elements of the file 
 * @var integer
 */
const kSettingTableLoadReplace = 1;

/**
 * Don't override existing entries
 * @var integer
 */
const kSettingTableLoadAppend = 2;

/**
 * Merge existing entries with the one in the file
 * @var integer
 */
const kSettingTableLoadMerge = 2;

class SettingTable implements \ArrayAccess, \Serializable
{

	public function __construct()
	{
		$this->m_elements = array ();
		$this->m_flags = 0;
	}

	public function __get($key)
	{
		return $this->offsetGet($key);
	}

	public function __set($key, $value)
	{
		$this->offsetSet($key, $value);
	}

	public function offsetExists($key)
	{
		return array_key_exists($key, $this->m_elements);
	}

	public function offsetGet($key)
	{
		return array_key_exists($key, $this->m_elements) ? $this->m_elements [$key] : null;
	}

	/**
	 *
	 * @param integer $key        	
	 * @param integer $value        	
	 * @throws \Exception
	 */
	public function offsetSet($key, $value)
	{
		if ($this->m_flags & kSettingTableReadOnly)
		{
			if ($this->m_flags & kSettingTableSilent)
			{
				return;
			}
			
			throw new \Exception('Read only setting table');
		}
		
		if (($this->m_flags & kSettingTableRestrictKeys) && !array_key_exists($key, $this->m_elements))
		{
			if ($this->m_flags & kSettingTableSilent)
			{
				return;
			}
			
			throw new \Exception('New key are not accepted');
		}
		
		$this->m_elements [$key] = $value;
	}

	public function offsetUnset($key)
	{
		if (array_key_exists($key, $this->m_elements))
		{
			unset($this->m_elements [$key]);
		}
	}

	public function serialize()
	{
		return json_encode($this->m_elements);
	}

	public function unserialize($serialized)
	{
		$this->m_elements = json_decode($serialized, true);
	}

	public function setFlags($flags)
	{
		$this->m_flags = $flags;
	}
	
	public function getFlags()
	{
		return $this->m_flags;
	}
	
	/**
	 * Load a setting file
	 * - JSON format: Recomended for static settings
	 * - PHP format: For dynamic settings only. The file is included
	 * and should contains 'set' commands such as <code>$this->key = 'value';</code> 
	 * 
	 * @attention Never use this method with untrusted PHP files 
	 * 
	 * @param string $filename
	 *        	File to load
	 * @param string $filetype
	 *        	One of kSettingTableFile*. If @c kSettingTableFileAuto, the file type is
	 *        	automatically detected using the file extenstion
	 */
	public function load($filename, $filetype = kSettingTableFileAuto)
	{
		if ($filetype == kSettingTableFileAuto)
		{
			if (preg_match(chr(1) . '.*\.json' . chr(1) . 'i', $filename))
			{
				$filetype = kSettingTableFileJSON;
			}
			if (preg_match(chr(1) . '.*\.php[0-9]*' . chr(1) . 'i', $filename))
			{
				$filetype = kSettingTableFilePHP;
			}
		}
		
		if ($filetype == kSettingTableFileJSON)
		{
			$elements = json_decode(file_get_contents($filename), true);
			if (is_array($elements))
			{
				foreach ($elements as $k => $v)
				{
					$this->m_elements[$k] = $v;
				}
			}
		}
		elseif ($filetype == kSettingTableFilePHP)
		{
			include ($filename);
		}
	}

	private $m_flags;

	private $m_elements;
}

?>