<?php

/**
 * Copyright © 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

class DataTreeNotTraversableException extends \Exception
{

	public function __construct($data)
	{
		parent::__construct(
			'Traversable or array expected. Got ' .
			TypeDescription::getName($data));
	}
}
