<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

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