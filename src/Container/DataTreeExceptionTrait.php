<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

trait DataTreeExceptionTrait
{

	function getDataTree()
	{
		return $this->dataTree;
	}

	function getContainer()
	{
		return $this->dataTree;
	}

	/**
	 *
	 * @var DataTree
	 */
	private $dataTree;
}
