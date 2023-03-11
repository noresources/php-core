<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

use NoreSources\Type\IntegerRepresentation;
use NoreSources\Type\TypeDescription;

/**
 * Iterator over ChainElements
 */
class ChainElementIterator implements \Iterator
{

	const DIRECTION_FORWARD = 1;

	const DIRECTION_BACKWARD = -1;

	/**
	 *
	 * @param ChainElementInterface $chain
	 *        	Chain to iterate
	 * @param integer $direction
	 *        	Direction of iterator
	 */
	public function __construct(ChainElementInterface $chain,
		$direction = self::DIRECTION_FORWARD)
	{
		$this->current = $this->base = $chain;
		$this->direction = $direction;
	}

	#[\ReturnTypeWillChange]
	public function current()
	{
		return $this->current;
	}

	#[\ReturnTypeWillChange]
	public function next()
	{
		$this->current = ($this->direction == self::DIRECTION_FORWARD) ? $this->current->getNextElement() : $this->current->getPreviousElement();
	}

	#[\ReturnTypeWillChange]
	public function key()
	{
		if ($this->current instanceof IntegerRepresentation)
			return $this->current->getInteger();
		elseif (TypeDescription::hasStringRepresentation($element))
			return \strval($this->current);
		return null;
	}

	#[\ReturnTypeWillChange]
	public function valid()
	{
		return ($this->current instanceof ChainElementInterface);
	}

	#[\ReturnTypeWillChange]
	public function rewind()
	{
		$this->current = $this->base;
	}

	/**
	 *
	 * @var ChainElementInterface
	 */
	private $base;

	/**
	 *
	 * @var ChainElementInterface
	 */
	private $current;

	/**
	 *
	 * @var integer
	 */
	private $direction;
}