<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

/**
 *
 * @deprecated
 *
 */
interface DataTreeExceptionInterface extends
	ContainerExceptionInterface
{

	/**
	 *
	 * @return DataTree
	 */
	function getDataTree();
}
