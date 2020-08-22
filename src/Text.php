<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
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
	 * @return string|Hexadecimal representation of the input value. The output string length is
	 *         always a multiple of 2.
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
		elseif (\is_bool($value))
			return (($value) ? '01' : '00');
		elseif (\is_null($value))
			return '00';
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

	public static function firstLetterCase($text, $upper = true)
	{
		$f = ($upper ? '\strtoupper' : '\strtolower');
		return $f(\substr($text, 0, 1)) . \substr($text, 1);
	}

	/**
	 *
	 * @param string $text
	 * @return string
	 */
	public static function toCamelCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_ALL
			]);
	}

	public static function toSmallCamelCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_OTHER
			]);
	}

	public static function toSnakeCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '_',
				self::CODE_CASE_CAPITALIZE => 0
			]);
	}

	const CODE_CASE_SEPARATOR = 'separator';

	const CODE_CASE_CAPITALIZE = 'capitalize';

	const CODE_CASE_CAPITALIZE_FIRST = 0x1;

	const CODE_CASE_CAPITALIZE_OTHER = 0xFE;

	const CODE_CASE_CAPITALIZE_ALL = 0xFF;

	public static function toCodeCase($text, $options)
	{
		$options = \array_merge(
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_ALL
			], $options);

		$text = \preg_replace('/\s/', ' ', trim($text));
		$parts = \preg_split('/[^a-zA-Z0-9]/', $text);

		$parts = \array_values(\array_filter($parts, function ($v) {
			return \strlen($v) > 0;
		}));

		if (\count($parts) == 0)
			return '';

		$count = \count($parts);
		$i = 0;
		$parts[$i] = self::firstLetterCase($parts[$i],
			(($options[self::CODE_CASE_CAPITALIZE] & self::CODE_CASE_CAPITALIZE_FIRST) ==
			self::CODE_CASE_CAPITALIZE_FIRST));

		for ($i = 1; $i < $count; $i++)
		{
			$parts[$i] = self::firstLetterCase($parts[$i],
				(($options[self::CODE_CASE_CAPITALIZE] & self::CODE_CASE_CAPITALIZE_OTHER) ==
				self::CODE_CASE_CAPITALIZE_OTHER));
		}

		return \implode($options[self::CODE_CASE_SEPARATOR], $parts);
	}
}