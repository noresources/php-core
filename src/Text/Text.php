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

	public static function explodeCodeWords($text, $options = 0)
	{
		$parts = \preg_split('/[^a-zA-Z0-9]+/', $text);
		$parts = \array_filter($parts, '\strlen');

		if ($options & self::WORD_CASELESS)
			return $parts;

		$words = [];
		foreach ($parts as $part)
		{
			if (\ctype_lower($part) || \ctype_upper($part) ||
				\ctype_digit($part))
			{
				$words[] = $part;
				continue;
			}

			//
			// 1 lowercase
			// 2 uppercase
			// 3 number
			$characterType = 0;
			$word = '';
			$chars = \preg_split('//', $part);

			foreach ($chars as $c)
			{
				if ($characterType == 0)
				{
					if (\ctype_digit($c))
						$characterType = 3;
					elseif (\ctype_upper($c))
						$characterType = 2;
					elseif (\ctype_lower($c))
						$characterType = 1;

					$word = $c;
					continue;
				}

				if (\ctype_lower($c))
				{
					if ($characterType == 3)
					{
						$words[] = $word;
						$word = '';
					}

					$characterType = 1;
				}
				elseif (\ctype_upper($c))
				{
					// Digit -³ upper
					if ($characterType == 3)
					{
						$words[] = $word;
						$word = '';
					}
					// lower -> upper
					elseif ($characterType != 2 &&
						($options & self::WORD_CASELESS) == 0)
					{
						$words[] = $word;
						$word = '';
					}

					$characterType = 2;
				}
				elseif (\ctype_digit($c))
				{
					if ($characterType != 3)
					{
						$words[] = $word;
						$word = '';
					}

					$characterType = 3;
				}

				$word .= $c;
			} // caracters of part

			$words[] = $word;
		} // each parts

		return $words;
	}

	/**
	 * Transform text to follow theCamelCase style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @param $capitalizationOptions Additional
	 *        	apitalization options.
	 * @return string Transformed text
	 */
	public static function toCamelCase($text, $capitalizationOptions = 0)
	{
		$capitalizationOptions &= self::CODE_CASE_PRESERVE_CAPITAL_WORDS;
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => ($capitalizationOptions |
				self::CODE_CASE_CAPITALIZE_FOLLOWING_INITIALS)
			]);
	}

	/**
	 * Transform text to "Something a normal human is happy to read"
	 *
	 * @param string $text
	 *        	Text to transform
	 * @param $capitalizationOptions Additional
	 *        	apitalization options.
	 * @return string Transformed text
	 */
	public static function toHumanCase($text, $capitalizationOptions = 0)
	{
		$capitalizationOptions &= self::CODE_CASE_PRESERVE_CAPITAL_WORDS;
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => ' ',
				self::CODE_CASE_CAPITALIZE => ($capitalizationOptions |
				self::CODE_CASE_CAPITALIZE_FIRST_INITIAL)
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
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_WORDS
			]);
	}

	/**
	 * Transform text to follow ThePascalCase style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @param $capitalizationOptions Additional
	 *        	apitalization options.
	 * @return string Transformed text
	 */
	public static function toPascalCase($text,
		$capitalizationOptions = 0)
	{
		$capitalizationOptions &= self::CODE_CASE_PRESERVE_CAPITAL_WORDS;
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '',
				self::CODE_CASE_CAPITALIZE => ($capitalizationOptions |
				self::CODE_CASE_CAPITALIZE_INITIALS)
			]);
	}

	/**
	 * Transform text to follow the_snake_case style
	 *
	 * @param string $text
	 *        	Text to transform
	 * @param $capitalizationOptions Additional
	 *        	apitalization options.
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

	public static function toTrainCase($text, $capitalizationOptions = 0)
	{
		$capitalizationOptions &= self::CODE_CASE_PRESERVE_CAPITAL_WORDS;
		return self::toCodeCase($text,
			[
				self::CODE_CASE_SEPARATOR => '_',
				self::CODE_CASE_CAPITALIZE => ($capitalizationOptions |
				self::CODE_CASE_CAPITALIZE_FOLLOWING_INITIALS)
			]);
	}

	const WORD_CASELESS = 0x01;

	const CODE_CASE_SEPARATOR = 'separator';

	/**
	 * Non-initial word letter case option
	 *
	 * Expected value is a boolean.
	 * The default value is FALSE.
	 *
	 * @deprecated Replaced by CODE_CASE_CAPITALIZE_WORDS
	 */
	const CODE_CASE_UPPER = 'uppercase';

	/**
	 * Initial word letter case option
	 */
	const CODE_CASE_CAPITALIZE = 'capitalize';

	/**
	 * Capitalize first letter of the first word
	 */
	const CODE_CASE_CAPITALIZE_FIRST_INITIAL = 0x01;

	/**
	 *
	 * @deprecated
	 */
	const CODE_CASE_CAPITALIZE_FIRST = self::CODE_CASE_CAPITALIZE_FIRST_INITIAL;

	/**
	 * Capitalize first letter of other words
	 */
	const CODE_CASE_CAPITALIZE_FOLLOWING_INITIALS = 0x02;

	/**
	 *
	 * @deprecated
	 */
	const CODE_CASE_CAPITALIZE_OTHER = self::CODE_CASE_CAPITALIZE_FOLLOWING_INITIALS;

	/**
	 * Capitalize first letter of all words
	 */
	const CODE_CASE_CAPITALIZE_INITIALS = 0x03;

	/**
	 *
	 * @deprecated
	 */
	const CODE_CASE_CAPITALIZE_ALL = self::CODE_CASE_CAPITALIZE_INITIALS;

	const CODE_CASE_CAPITALIZE_WORDS = 0x07;

	const CODE_CASE_PRESERVE_CAPITAL_WORDS = 0x08;

	const CODE_CASE_WORD_OPTIONS = 'wordOptions';

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
				self::CODE_CASE_CAPITALIZE => self::CODE_CASE_CAPITALIZE_INITIALS,
				self::CODE_CASE_UPPER => false,
				self::CODE_CASE_WORD_OPTIONS => 0
			], $options);

		// Legacy
		if ($options[self::CODE_CASE_UPPER])
			$options[self::CODE_CASE_CAPITALIZE] |= self::CODE_CASE_CAPITALIZE_WORDS;

		$words = self::explodeCodeWords($text,
			$options[self::CODE_CASE_WORD_OPTIONS]);
		$first = true;
		if (\count($words) == 0)
			return '';

		if (($options[self::CODE_CASE_CAPITALIZE] &
			self::CODE_CASE_CAPITALIZE_WORDS) ==
			self::CODE_CASE_CAPITALIZE_WORDS)
			return \implode($options[self::CODE_CASE_SEPARATOR],
				\array_map('\strtoupper', $words));

		$count = \count($words);
		$i = 0;
		$keepCapitalWord = ($options[self::CODE_CASE_CAPITALIZE] &
			self::CODE_CASE_PRESERVE_CAPITAL_WORDS);
		$capitalizeInitial = (($options[self::CODE_CASE_CAPITALIZE] &
			self::CODE_CASE_CAPITALIZE_FIRST_INITIAL) ==
			self::CODE_CASE_CAPITALIZE_FIRST_INITIAL);

		$words[$i] = self::applyWordCodeCase($words[$i],
			$capitalizeInitial, $capitalizeInitial && $keepCapitalWord);

		$capitalizeInitial = (($options[self::CODE_CASE_CAPITALIZE] &
			self::CODE_CASE_CAPITALIZE_FOLLOWING_INITIALS) ==
			self::CODE_CASE_CAPITALIZE_FOLLOWING_INITIALS);

		for ($i = 1; $i < $count; $i++)
		{
			$words[$i] = self::applyWordCodeCase($words[$i],
				$capitalizeInitial, $keepCapitalWord);
		}

		return \implode($options[self::CODE_CASE_SEPARATOR], $words);
	}

	protected static function applyWordCodeCase($word,
		$capitalizeInitial = false, $keepCapitalWord = false)
	{
		if ($keepCapitalWord && \ctype_upper($word))
			return $word;
		$word = \strtolower($word);

		return self::firstLetterCase($word, $capitalizeInitial);
	}
}