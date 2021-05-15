<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Container;

/**
 * Exception railsed by DataTree methods implementing the PSR ContainerInterface
 */
class DataTreeElementNotFoundException extends KeyNotFoundException implements
	DataTreeExceptionInterface
{
	use DataTreeExceptionTrait;

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