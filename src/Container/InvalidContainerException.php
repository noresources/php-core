<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

use NoreSources\Type\TypeDescription;

/**
 * Exception raised when the object given to Container member class is not a valid container
 */
class InvalidContainerException extends \InvalidArgumentException implements
	ContainerExceptionInterface
{

	/**
	 *
	 * @return mixed Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 *
	 * @param mixed $invalidContainer
	 * @param string $forMethod
	 *        	Container method name
	 */
	public function __construct($invalidContainer, $forMethod = null)
	{
		parent::__construct(
			TypeDescription::getName($invalidContainer) .
			' is not a valid container' .
			(\is_string($forMethod) ? ' for method ' . $forMethod : ''));
		$this->container = $invalidContainer;
	}

	private $container;
}
