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
		$tags = [];
		foreach ($this->lines as $line)
		{
			if (\strpos($line, '@' . $name) !== 0)
				continue;
			$content = substr($line, \strlen($name) + 1);
			$trimmed = \ltrim($content);
			if ($content == $trimmed) // not $name but $name(AndSomething)
				continue;
			$tags[] = $trimmed;
		}
		return $tags;
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
	 * @param string $name
	 *        	Variable name
	 * @return string[]|NULL Associative array with the following keys
	 *         <ul>²li>types</li><li>documentation</li></ul>
	 */
	public function getVariable($name)
	{
		return $this->findVariableDeclaration('var', $name);
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

	const PATTERN_VARIABLE_DECLARATION = '(?<type>.*?)\s+\$(?<name>[a-zA-Z_][a-zA-Z0-9_]*)(?:\s+(?<documentation>.*))?';

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
