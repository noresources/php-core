<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

/**
 * Reference implementation of the ChainElementInterface
 */
class ChainElement implements ChainElementInterface, \IteratorAggregate
{
	use ChainElementTrait;

	public function __construct()
	{}

	/**
	 * Detach element from owning list
	 */
	public function __destruct()
	{
		$this->detachElement();
	}

	#[\ReturnTypeWillChange]
	public function getIterator()
	{
		return new ChainElementIterator($this);
	}
}