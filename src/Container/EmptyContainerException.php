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
class EmptyContainerException extends \EmptyArgumentException implements
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

	public function __construct($container = null)
	{
		$type = 'Container';
		if ($container)
			$type = TypeDescription::getName($container);
		parent::__construct($type . ' is empty');
		$this->container = $container;
	}

	private $container;
}
