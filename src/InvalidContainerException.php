<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

/**
 * Exception raise when the object given to Container member class is not a valid container
 */
class InvalidContainerException extends \InvalidArgumentException
{

	/**
	 *
	 * @param mixed $invalidContainer
	 * @param string $forMethod
	 *        	Container method name
	 */
	public function __construct($invalidContainer, $forMethod = null)
	{
		parent::__construct(
			TypeDescription::getName($invalidContainer) . ' is not a valid container' .
			(\is_string($forMethod) ? ' for method ' . $forMethod : ''));
	}
}
