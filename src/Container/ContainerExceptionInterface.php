<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Container;

interface ContainerExceptionInterface
{

	/**
	 *
	 * @return mixed Container object that raised the exception
	 */
	function getContainer();
}
