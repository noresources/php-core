<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Text;

use NoreSources\Container\Container;
use NoreSources\Type\TypeDescription;

/**
 * Text manipulation utility class
 */
class Text
{

	/**
	 * Indicates if the given text represents a floating point number
	 *
	 * @param string $text
	 *        	Input text
	 * @return boolean TRUE if $text represents a floating point number
	 */
	public static function isFloat($text)
	{
		return ($text == \strval(\floatval($text)));
	}

	/**
	 * Indicates if the given text represents an integer
	 *
	 * @param string $text
	 *        	Input text
	 * @return boolean TRUE if $text represents an integer
	 */
	public static function isInteger($text)
	{
		return ($text == \strval(\intval($text)));
	}

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
		Container::ksort($result);

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

	/**
	 * Set the first letter case
	 *
	 * @param string $text
	 *        	Text Input text
	 * @param boolean $upper
	 *        	Indicate if the first letter must be upper case
	 * @return string
	 */
	public static function firstLetterCase($text, $upper = true)
	{
		return ($upper ? \ucfirst($text) : \lcfirst($text));
	}

	/**
	 * Transform text to follow theCamelCase style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @return string Transformed text
	 */
	public static function toCamelCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_OTHER
			]);
	}

	/**
	 * Transform text to "Something a normal human is happy to read"
	 *
	 * @param string $text
	 *        	Text to transform
	 * @return string Transformed text
	 */
	public static function toHumanCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => ' ',
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_FIRST
			]);
	}

	/**
	 * Transform text to follow the-kebab-case style
	 *
	 * @param string $text
	 *        	ext to transform
	 * @return string Transformed text
	 */
	public static function toKebabCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '-',
				self::CODE_CASE_CAPITALIZE => 0
			]);
	}

	/**
	 * Transform text to follow the MACRO_CASE style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @return string Transformed text
	 */
	public static function toMacroCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '_',
				self::CODE_CASE_UPPER => true
			]);
	}

	/**
	 * Transform text to follow ThePascalCase style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @return string Transformed text
	 */
	public static function toPascalCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_ALL
			]);
	}

	/**
	 * Transform text to follow the_snake_case style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @return string Transformed text
	 */
	public static function toSnakeCase($text)
	{
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '_',
				self::CODE_CASE_CAPITALIZE => 0
			]);
	}

	const CODE_CASE_SEPARATOR = 'separator';

	/**
	 * Non-initial word letter case option
	 *
	 * Expected value is a boolean.
	 * The default value is FALSE.
	 */
	const CODE_CASE_UPPER = 'uppercase';

	/**
	 * Initial word letter case option
	 */
	const CODE_CASE_CAPITALIZE = 'capitalize';

	/**
	 * Capitalize first letter of the first word
	 */
	const CODE_CASE_CAPITALIZE_FIRST = 0x1;

	/**
	 * Capitalize first letter of other words
	 */
	const CODE_CASE_CAPITALIZE_OTHER = 0xFE;

	/**
	 * Capitalize first letter of all words
	 */
	const CODE_CASE_CAPITALIZE_ALL = 0xFF;

	/**
	 * Transform text to a user defined code style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @param array $options
	 *        	Style options
	 * @return string Transformed text
	 */
	public static function toCodeCase($text, $options)
	{
		$options = \array_merge(
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_ALL,
				self::CODE_CASE_UPPER => false
			], $options);

		$text = \trim($text);

		// Add space between camel/pascaled names
		$text = \preg_replace('/(?<=[a-z0-9])([A-Z]+)/', ' $1', $text);

		// // Replace any non-alnum by a single space
		$text = \preg_replace('/[^a-z0-9]+/i', ' ', $text);

		if ($options[self::CODE_CASE_UPPER])
			$text = \strtoupper($text);
		else
			$text = \strtolower($text);

		// Split words
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