<?php

/**
 * Copyright © 2020 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Container;

class DataTreeNotTraversableException extends InvalidContainerException implements
	DataTreeExceptionInterface
{

	public function getDataTree()
	{
		return $this->dataTree;
	}

	public function __construct(DataTree $tree, $data, $forMethod = null)
	{
		parent::__construct($data, $forMethod);
		$this->dataTree = $tree;
	}

	/**
	 *
	 * @var DataTree
	 */
	private $dataTree;
}
