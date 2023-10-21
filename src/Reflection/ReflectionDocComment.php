<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use NoreSources\Container\Container;
use NoreSources\Type\StringRepresentation;
use a;

/**
 * Documentation comment reflection
 */
class ReflectionDocComment implements StringRepresentation
{

	/**
	 * Get information on PHPDoc type declaration
	 *
	 * @param string $declaration
	 *        	A type declaration appearing in @param, @var or @return
	 * @return string[] A Type properties
	 *         <ul>
	 *         <li>type: The type name without decoration</li>
	 *         * <li>key: For array type, the array keys type/li>
	 *         * <li>value: For array type, The array values type</li>
	 *         </ul>
	 */
	public static function getTypeDeclarationProperties($declaration)
	{
		if (\preg_match(
			chr(1) . self::PATTERN_TYPE_ARRAY_OF_TYPE . chr(1),
			$declaration, $m))
		{
			return [
				'type' => 'array',
				'key' => 'integer',
				'value' => $m['type']
			];
		}
		elseif (\preg_match(chr(1) . self::PATTERN_TYPE_MAP . chr(1),
			$declaration, $m))
		{
			return [
				'type' => 'array',
				'key' => $m['key'],
				'value' => $m['value']
			];
		}

		return [
			'type' => $declaration
		];
	}

	/**
	 *
	 * @param string $text
	 *        	Documentation comment
	 */
	public function __construct($text)
	{
		$lines = \explode(PHP_EOL, $text);
		$content = '';
		foreach ($lines as $line)
		{
			$line = \trim($line);
			if (\preg_match(chr(1) . self::PATTERN_COMMENT_END . chr(1),
				$line))
				continue;
			$line = \preg_replace(
				chr(1) . self::PATTERN_COMMENT_PREFIX . chr(1), '',
				$line);
			$line = \preg_replace(
				chr(1) . self::PATTERN_COMMENT_SUFFIX . chr(1), '',
				$line);

			if (empty($line) || \strpos($line, '@') === 0)
			{
				if (!empty($content))
				{
					$this->lines[] = $content;
					$content = '';
				}

				$content = $line;

				continue;
			}

			if (!empty($content))
				$content .= ' ';
			$content .= $line;
		}

		if (!empty($content))
			$this->lines[] = $content;
	}

	/**
	 *
	 * @return a DocComment text
	 */
	public function __toString()
	{
		return '/**' . PHP_EOL .
			Container::implodeValues($this->lines,
				[
					Container::IMPLODE_BEFORE => ' * ',
					Container::IMPLODE_BETWEEN => PHP_EOL . PHP_EOL
				]) . PHP_EOL . ' */' . PHP_EOL;
	}

	/**
	 * Get all lines starting with the given documentation tag
	 *
	 * @param string $name
	 *        	Tag name
	 * @return string[]
	 */
	public function getTags($name)
	{
		if (!Container::isTraversable($this->lines))
			return [];
		$tags = [];
		$prefix = '@' . $name;
		$length = \strlen($prefix);
		foreach ($this->lines as $line)
		{
			if (\strpos($line, $prefix) !== 0)
				continue;
			$content = \substr($line, $length);
			if (\strlen($content))
			{
				$trimmed = \ltrim($content);
				if ($content == $trimmed) // not $name but $name(AndSomething)
					continue;
				$content = $trimmed;
			}
			$tags[] = $content;
		}
		return $tags;
	}

	/**
	 * Get all lines which are not tags
	 *
	 * @return string[] Text lines
	 */
	public function getTextLines()
	{
		return Container::filterValues($this->lines,
			function ($line) {
				return (\strpos($line, '@') !== 0);
			});
	}

	/**
	 * Get first text line
	 *
	 * @return string|NULL First text line if any
	 */
	public function getAbstract()
	{
		return Container::keyValue($this->getTextLines(), 0);
	}

	/**
	 * Get detailed description lines.
	 *
	 * The detailed description lines are all text lines except the first one.
	 *
	 * @param string|NULL $glue
	 *        	If set, merge line with this glue.
	 * @return NULL|string|string[] Text lines corresponding to the detailed description.
	 */
	public function getDetails($glue = null)
	{
		$textLines = $this->getTextLines();
		\array_shift($textLines);
		if (\count($textLines) == 0)
			return NULL;
		if ($glue)
			return \implode($glue, $textLines);
		return $textLines;
	}

	/**
	 * Indicates if the DocComment has at least one occurence of the given tag.
	 *
	 * @param string $name
	 *        	Tag name.
	 * @return boolean
	 */
	public function hasTag($name)
	{
		if (!Container::isTraversable($this->lines))
			return false;
		$prefix = '@' . $name;
		$length = \strlen($prefix);
		foreach ($this->lines as $line)
		{
			if (\strpos($line, $prefix) !== 0)
				continue;
			$content = \substr($line, $length);
			if (\strlen($content))
			{
				$trimmed = \ltrim($content);
				if ($content == $trimmed) // not $name but $name(AndSomething)
					continue;
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the nth line containing the given documentation tag
	 *
	 * @param string $name
	 *        	Tag name
	 * @param number $index
	 *        	Tag index
	 * @return string
	 */
	public function getTag($name, $index = 0)
	{
		return Container::keyValue($this->getTags($name), $index);
	}

	/**
	 * Get type and documentation of the given function parameter.
	 *
	 * @param string $name
	 *        	Parameter name
	 * @return string[]|NULL Associative array with the following keys
	 *         <ul>²li>types</li><li>documentation</li></ul>
	 */
	public function getParameter($name)
	{
		return $this->findVariableDeclaration('param', $name);
	}

	/**
	 * Get variable type and documentation.
	 *
	 * @param string|NULL $name
	 *        	Variable name. If NULL, Find the first @var tag.
	 * @return string[]|NULL Associative array with the following keys
	 *         <ul>²li>types</li><li>documentation</li></ul>
	 */
	public function getVariable($name = null)
	{
		if ($name)
			return $this->findVariableDeclaration('var', $name);

		$var = $this->getTag('var');
		if (\is_null($var))
			return NULL;

		$type = $var;
		$documentation = '';
		if (\preg_match(
			chr(1) . self::PATTERN_PROPERTY_DECLARATION . chr(1), $var,
			$m))
		{

			$type = $m['types'];
			$documentation = Container::keyValue($m, 'documentation',
				$documentation);
		}

		return [
			'types' => \explode('|', $type),
			'documentation' => $documentation
		];
	}

	/**
	 * Get return value types and return value documentation given by the @return tag.
	 *
	 * @return string[]|NULL Associative array with the following keys
	 *         <ul>²li>types</li><li>documentation</li></ul>
	 */
	public function getReturn()
	{
		$tag = $this->getTag('return');
		if (!$tag)
			return NULL;
		$p = chr(1) . '(?<type>.*?)(?:(?:\s+(?<documentation>.*))|$)' .
			chr(1);
		if (\preg_match($p, $tag, $m))
		{
			return [
				'types' => \explode('|', $m['type']),
				'documentation' => $m['documentation']
			];
		}
		return [
			'documentation' => $tag
		];
	}

	/**
	 * Get cleaned documentation lines
	 *
	 * @return string[]
	 */
	public function getLines()
	{
		return $this->lines;
	}

	const PATTERN_COMMENT_PREFIX = '^(?:/\*{2}\**\s*)|(\*+\s*)';

	const PATTERN_COMMENT_SUFFIX = '\s*\*+/';

	const PATTERN_COMMENT_END = '^\*+/';

	const PATTERN_PROPERTY_DECLARATION = '(?<types>[^\s]+)(?:\s+(?<documentation>.*))?';

	const PATTERN_VARIABLE_DECLARATION = '(?<type>.*?)\s+\$(?<name>[a-zA-Z_][a-zA-Z0-9_]*)(?:\s+(?<documentation>.*))?';

	const PATTERN_TYPE_ARRAY_OF_TYPE = '^(?<type>(?:\\\\)?[a-zA-Z_][a-zA-Z0-9_]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)*)\[\]$';

	const PATTERN_TYPE_MAP = 'array<(?<key>(?:\\\\)?[a-zA-Z_][a-zA-Z0-9_]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)*),(?<value>(?:\\\\)?[a-zA-Z_][a-zA-Z0-9_]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)*)>';

	/**
	 *
	 * @param string $tag
	 *        	Tag name
	 * @param string $name
	 *        	Variable name
	 * @return string[]|NULL Associative array with the following keys
	 *         <ul>²li>types</li><li>documentation</li></ul>
	 */
	private function findVariableDeclaration($tag, $name)
	{
		$tags = $this->getTags($tag);
		$p = chr(1) . self::PATTERN_VARIABLE_DECLARATION . chr(1);
		foreach ($tags as $text)
		{
			if (\preg_match($p, $text, $m))
			{
				if ($m['name'] == $name)
					return [
						'types' => \explode('|', $m['type']),
						'documentation' => $m['documentation']
					];
			}
		}
		return null;
	}

	/**
	 *
	 * @var string[]
	 */
	private $lines;
}
