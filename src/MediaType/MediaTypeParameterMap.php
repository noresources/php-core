<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\MediaType;

/**
 * Implementation of MediaTypeParameterMapInterface using PHP ArrayObject
 */
class MediaTypeParameterMap implements MediaTypeParameterMapInterface
{

	public function __construct($parameters = array())
	{
		$this->parameterMap = new \ArrayObject($parameters);
	}

	public function getParameterValue($name, $dflt = null)
	{
		if ($this->parameterMap->offsetExists($name))
			return $this->parameterMap->offsetGet(\strtolower($name));
		return $dflt;
	}

	public function offsetGet($name)
	{
		return $this->parameterMap->offsetGet(\strtolower($name));
	}

	public function offsetExists($name)
	{
		return $this->offsetExists(\strtolower($name));
	}

	public function offsetSet($name, $value)
	{
		$this->parameterMap->offsetSet(\strtolower($name), $value);
	}

	public function offsetUnset($name)
	{
		$this->parameterMap->offsetUnset(\strtolower($name));
	}

	public function count()
	{
		return $this->parameterMap->count();
	}

	public function getIterator()
	{
		return $this->parameterMap->getIterator();
	}

	public function getArrayCopy()
	{
		return $this->parameterMap->getArrayCopy();
	}

	/**
	 *
	 * @var \ArrayObject
	 */
	private $parameterMap;
}