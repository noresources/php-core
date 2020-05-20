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

/**
 * Allow object to specify by itself its container nature properties
 */
interface ContainerPropertyInterface
{

	/**
	 *
	 * @return integer Container property flags
	 */
	function getContainerProperties();
}