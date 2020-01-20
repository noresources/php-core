<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\MediaType;

trait MediaTypeParameterMapTrait
{

	public function initializeParameterMap($parameters = array())
	{
		$this->parameterMap = new MediaTypeParameterMap($parameters);
	}

	public function getParameters()
	{
		return $this->parameterMap;
	}

	/**
	 *
	 * @var MediaTypeParameterMapInterface
	 */
	private $parameterMap;
}