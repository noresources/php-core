<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Container;

interface DataTreeExceptionInterface extends
	ContainerExceptionInterface
{

	/**
	 *
	 * @return DataTree
	 */
	function getDataTree();
}
