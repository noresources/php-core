<?php
/**
 * Copyright © 2020 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources;

use NoreSources\Type\TypeDescription;

/**
 * Exception raised by ComparableInterface::compare()
 */
class NotComparableException extends \LogicException
{

	public function __construct($a, $b)
	{
		parent::__construct(
			TypeDescription::getName($a) . ' cannot be compared with ' .
			TypeDescription::getName($b));
	}
}
