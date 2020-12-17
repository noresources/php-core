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
	 * @param string $text
	 *        	Input string
	 * @param string[] $needles
	 *        	List of strings to look into $text
	 * @param boolean $firstOnly
	 *        	If true, only return the nearest match
	 * @return array|false Position -> needle array or FALSE if none of the $needles can be found in
	 *         $haystack
	 */
	public static function firstOf($haystack, $needles = array(),
		$firstOnly = false)
	{
		$result = [];
		foreach ($needles as $s)
		{
			$p = \strpos($haystack, $s);
			if ($p !== false)
				$result[$p] = $s;
		}

		if (\count($result) == 0)
			return [
				-1 => false
			];
		ksort($result);

		if ($firstOnly)
			$result = Container::first($result);
		return $result;
	}

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
	public static function toHexadecimalString($value,
		$upperCase = false)
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
			'string or integer expected. Got ' .
			TypeDescription::getName($value));
	}

	public static function firstLetterCase($text, $upper = true)
	{
		return ($upper ? \ucfirst($text) : \lcfirst($text));
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

		$parts = \array_values(
			\array_filter($parts,
				function ($v) {
					return \strlen($v) > 0;
				}));

		if (\count($parts) == 0)
			return '';

		$count = \count($parts);
		$i = 0;
		$parts[$i] = self::firstLetterCase($parts[$i],
			(($options[self::CODE_CASE_CAPITALIZE] &
			self::CODE_CASE_CAPITALIZE_FIRST) ==
			self::CODE_CASE_CAPITALIZE_FIRST));

		for ($i = 1; $i < $count; $i++)
		{
			$parts[$i] = self::firstLetterCase($parts[$i],
				(($options[self::CODE_CASE_CAPITALIZE] &
				self::CODE_CASE_CAPITALIZE_OTHER) ==
				self::CODE_CASE_CAPITALIZE_OTHER));
		}

		return \implode($options[self::CODE_CASE_SEPARATOR], $parts);
	}
}