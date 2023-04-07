<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use NoreSources\Container\Container;

/**
 * Documentation comment reflection
 */
class ReflectionDocComment
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

	/**
	 *
	 * @var string[]
	 */
	private $lines;
}
