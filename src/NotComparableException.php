<?php

/**
 * Copyright © 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

class NotComparableException extends \LogicException
{

	public function __construct($a, $b)
	{
		parent::__construct(
			TypeDescription::getName($a) . ' cannot be compared with ' .
			TypeDescription::getName($b));
	}
}
