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
 * Text manipulation utility class
 */
class Text
{

	/**
	 *
	 * @param integer|string $value
	 *        	Value to convert
	 * @param boolean $upperCase
	 *        	Indicates if hexadecimal digit letters should be written uper case or not.
	 * @throws \InvalidArgumentException
	 * @return string|Hexadecimal representation of the input value. The output string length is always a multiple of 2.
	 */
	public static function toHexadecimalString($value, $upperCase = false)
	{
		if (\is_integer($value))
		{
			$hex = dechex($value);
			if ($upperCase)
				$hex = \strtoupper($hex);
			if (\strlen($value) % 2 == 1)
				$hex = '0' . $hex;
			return $hex;
		}
		elseif (\is_string($value))
		{
			$f = '%02' . ($upperCase ? 'X' : 'x');
			$hex = '';
			$length = \strlen($value);
			for ($i = 0; $i < $length; $i++)
				$hex .= sprintf($f, ord($value[$i]));
			return $hex;
		}

		throw new \InvalidArgumentException(
			'string or integer expected. Got ' . TypeDescription::getName($value));
	}
}