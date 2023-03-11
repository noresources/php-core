<?php
/**
 * Copyright © 2020 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

class DataTreeNotTraversableException extends InvalidContainerException implements
	DataTreeExceptionInterface
{

	/**
	 *
	 * @return DataTree
	 */
	public function getDataTree()
	{
		return $this->dataTree;
	}

	/**
	 *
	 * @param DataTree $tree
	 *        	DataTree which is not traversable
	 * @param unknown $data
	 * @param unknown $forMethod
	 *        	Method that raised the exception
	 */
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
