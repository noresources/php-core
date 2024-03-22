<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Text;

/**
 * Transforms a string to a list of tokens.
 */
class Tokenifier
{

	/**
	 * White space character list
	 *
	 * @var string[]
	 */
	public $whitespaces = [
		' ',
		"\t",
		"\n",
		"\r"
	];

	/**
	 * List of additional escapable characters
	 *
	 * @var string[]
	 */
	public $escapable = [];

	/**
	 * Add new quoting pair
	 *
	 * @param stringown $start
	 *        	Opening quote
	 * @param string|NULL $end
	 *        	Closing quoute. If NULL, use opening quote.
	 */
	public function addQuotingPair($start, $end = null)
	{
		$this->quotingPairs[] = [
			$start,
			($end !== null) ? $end : $start
		];
	}

	/**
	 *
	 * @param callable $length
	 *        	strlen compatible function
	 * @param callable $split
	 *        	str_split compatible function
	 */
	public function setStringFunctions($length, $split)
	{
		if (!\is_callable($length))
			throw new \InvalidArgumentException(
				'$lenght is not callable');
		if (!\is_callable($split))
			throw new \InvalidArgumentException(
				'$split is not callable');
		$this->strlen = $length;
		$this->str_split = $split;
	}

	/**
	 * Remove all quoting pair definitions
	 */
	public function clearQuotingPairs()
	{
		$this->quotingPairs = [];
	}

	/**
	 *
	 * @param string $text
	 * @throws \InvalidArgumentException
	 * @return string[] List of tokens
	 */
	public function __invoke($text)
	{
		$ws = \implode('', $this->whitespaces);
		$text = \trim($text, $ws);

		$length = \call_user_func($this->strlen, $text);
		$characters = \call_user_func($this->str_split, $text);

		$escaping = false;
		$quotingPair = -1;
		$tokens = [];
		$token = null;
		$escapable = $this->getEscapables($quotingPair);

		for ($i = 0; $i < $length; $i++)
		{

			$c = $characters[$i];
			if ($escaping)
			{
				if (!\in_array($c, $escapable))
					$token .= $this->escape;
				$token .= $c;
				$escaping = false;
				continue;
			}

			if ($quotingPair >= 0)
			{
				if ($c === $this->quotingPairs[$quotingPair][1])
				{
					$tokens[] = ($token . '');
					$token = null;
					$quotingPair = -1;
					$escapable = $this->getEscapables($quotingPair);
					continue;
				}
			}
			elseif (\in_array($c, $this->whitespaces))
			{
				if ($token !== null)
				{
					$tokens[] = $token;
					$token = null;
				}
				continue;
			}
			elseif ($this->findStartingQuute($quotingPair, $c))
			{
				if ($token !== null)
				{
					$tokens[] = $token;
					$token = null;
				}

				$escapable = $this->getEscapables($quotingPair);
				continue;
			}

			if (!empty($this->escape) && $c == $this->escape)
			{
				$escaping = true;
				continue;
			}

			$token .= $c;
		}

		if ($escaping)
			throw new \InvalidArgumentException(
				'Unterminated escpae sequence');

		if ($quotingPair >= 0)
			throw new \InvalidArgumentException(
				'Unterminated quoted string using ' .
				\implode('', $this->quotingPairs[$quotingPair]) .
				' quoting pair');

		if ($token !== null)
			$tokens[] = $token;

		return $tokens;
	}

	protected function getEscapables($quotingPair)
	{
		$list = [];
		if ($quotingPair >= 0)
			$list = $this->quotingPairs[$quotingPair];
		else
			foreach ($this->quotingPairs as $pair)
			{
				$list[] = $pair[0];
				$list[] = $pair[1];
			}
		if (!empty($this->escape))
			$list[] = $this->escape;
		return \array_unique(
			\array_merge($list, $this->escapable, $this->whitespaces));
	}

	protected function findStartingQuute(&$offset, $character)
	{
		$o = 0;
		foreach ($this->quotingPairs as $p)
		{
			if ($character === $p[0])
			{
				$offset = $o;
				return true;
			}
			$o++;
		}
		return false;
	}

	private $escape = '\\';

	/**
	 * Quoting pairs
	 *
	 * @var array
	 */
	private $quotingPairs = [
		[
			'"',
			'"'
		],
		[
			"'",
			"'"
		]
	];

	/**
	 * strlen implementation
	 *
	 * @var callable
	 */
	private $strlen = '\strlen';

	/**
	 * str_split implementation
	 *
	 * @var callable
	 */
	private $str_split = '\str_split';
}
