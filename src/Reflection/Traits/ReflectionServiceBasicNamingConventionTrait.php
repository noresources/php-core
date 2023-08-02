<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection\Traits;

/**
 * Implements ReflectionServiceInterface readMethod & writeMethodrelated methods
 */
trait ReflectionServiceBasicNamingConventionTrait
{

	function findReadMethodForProperty(\ReflectionClass $class,
		$propertyName)

	{
		if (!isset($this->readMethodPrefixes))
			$this->readMethodPrefixes = [
				'get',
				'is'
			];
		foreach ($this->readMethodPrefixes as $prefix)
		{
			$name = $prefix . $propertyName;
			if ($class->hasMethod($name))
				return $class->getMethod($name);
		}
		return null;
	}

	function findWriteMethodForProperty(\ReflectionClass $class,
		$propertyName)
	{
		if (!isset($this->writeMethodPrefixes))
			$this->writeMethodPrefixes = [
				'set',
				'is'
			];
		foreach ($this->writeMethodPrefixes as $prefix)
		{
			$name = $prefix . $propertyName;
			if (!$class->hasMethod($name))
				continue;
			$m = $class->getMethod($name);
			if (\count($m->getParameters()) == 0)
				continue;
			return $m;
		}
		return null;
	}

	public function setReadMethodPrefixes($list)
	{
		$this->readMethodPrefixes = $list;
	}

	public function setWriteMethodPrefixes($list)
	{
		$this->writeMethodPrefixes = $list;
	}

	/**
	 *
	 * @var array
	 */
	private $readMethodPrefixes;

	/**
	 *
	 * @var array
	 */
	private $writeMethodPrefixes;
}
