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

use Psr\Container\NotFoundExceptionInterface;

class DataTreeElementNotFoundException extends \InvalidArgumentException implements
	NotFoundExceptionInterface
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
	 * @param unknown $key
	 */
	public function __construct(DataTree $tree, $key)
	{
		parent::\__construct(\strval($key) . ' element not found', 404);
		$this->dataTree;
	}
}