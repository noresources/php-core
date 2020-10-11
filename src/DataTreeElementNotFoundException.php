<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Exception railsed by DataTree methods implementing the PSR ContainerInterface
 */
class DataTreeElementNotFoundException extends KeyNotFoundException
{

	/**
	 * Reference to the DataTree that throws the exception
	 *
	 * @varDataTree
	 */
	public $dataTree;

	/**
	 *
	 * @param DataTree $tree
	 * @param string $key
	 */
	public function __construct(DataTree $tree, $key)
	{
		parent::__construct($key);
		$this->dataTree = $tree;
	}
}