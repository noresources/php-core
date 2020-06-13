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

class StructuredTextSyntaxErrorException extends \ErrorException
{

	public function __construct($format, $message = null, $line = null, $code = null)
	{
		$m = $format . ' syntax error';
		if (\is_numeric($line))
			$m .= ' at line ' . $line;
		if (\is_string($message) && \strlen($message))
			$m .= ': ' . $message;

		parent::__construct($m, $code);
	}
}