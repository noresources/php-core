<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
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

	public function __destruct()
	{
		$this->detachElement();
	}

	public function getIterator()
	{
		return new ChainElementIterator($this);
	}
}