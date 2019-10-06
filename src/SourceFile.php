<?php

/**
 * Copyright © 2012-2018 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

class SourceToken
{

	const TYPE_UNKNOWN = -1;

	const TYPE_STRING = 0;

	const TYPE_ELEMENT = 1;

	/**
	 * Move to the next token kind
	 *
	 * @param array $tokens
	 *        	A token arary given by \token_get_all()
	 * @param int $tokenIndex
	 *        	Index of the current token
	 * @param mixed $nextElementType
	 *        	Token to search
	 *        	
	 * @return The
	 */
	public static function move_next(&$tokens, &$tokenIndex, $nextElementType)
	{
		$c = count($tokens);
		$tokenIndex++;
		while ($tokenIndex < $c)
		{
			$token = $tokens[$tokenIndex];
			if (\is_array($token) && \is_int($nextElementType) && ($token[0] == $nextElementType))
			{
				return $token;
			}
			elseif (is_string($token) && is_string($nextElementType) && ($token == $nextElementType))
			{
				return $token;
			}

			$tokenIndex++;
		}

		return null;
	}

	/**
	 * Get token type
	 *
	 * @param mixed $token
	 *        	a element of the token array given by \token_get_all()
	 * @return integer One of Type* constants
	 */
	public static function getType($token)
	{
		if (is_array($token) && (count($token) == 3))
		{
			return SourceToken::TYPE_ELEMENT;
		}
		elseif (is_string($token))
		{
			return SourceToken::TYPE_STRING;
		}

		return TypeUnknown;
	}

	/**
	 * Get the list of namespace declarations
	 *
	 * @param array $tokens
	 *        	A token arary given by \token_get_all()
	 * @return multitype:
	 */
	public static function getNamespaces(&$tokens)
	{
		$namespaces = [];
		$visitor = SourceToken::getVisitor($tokens);

		// Search for namespaces
		$ns = null;
		while (($ns = $visitor->moveToToken(T_NAMESPACE)))
		{
			$search = $visitor->queryNextTokens([
				T_STRING,
				'{',
				';'
			], true);
			ksort($search);
			list ($index, $entry) = each($search);
			$token = $entry['token'];
			$name = '';
			if ((SourceToken::getType($token) == SourceToken::TYPE_ELEMENT) &&
				($token[0] == T_STRING))
			{
				$name = $token[1];
			}

			$item = [
				'index' => $visitor->key(),
				'name' => $name
			];

			$namespaces[] = $item;
		}

		return $namespaces;
	}

	/**
	 *
	 * @param mixed $token
	 *        	An element of the token array given by \token_get_all()
	 */
	public static function value($token)
	{
		if (is_array($token))
		{
			return $token[1];
		}
		elseif (is_string($token))
		{
			return $token;
		}
		return null;
	}

	/**
	 *
	 * @param array $tokens
	 *        	A token array given by \token_get_all()
	 */
	public static function getVisitor(&$tokens)
	{
		return (new TokenVisitor($tokens));
	}

	public static function output(&$tokens, $flags = 0, $namespaces = null)
	{
		$output = '';
		$condensedWhitespace = '';
		$echoTag = false;

		if (!is_array($namespaces))
		{
			$namespaces = SourceToken::getNamespaces($tokens);
		}

		$visitor = SourceToken::getVisitor($tokens);
		if ($flags & SourceFile::OUTPUT_IGNORE_PHPTAGS)
		{
			$openTag = $visitor->moveToToken(T_OPEN_TAG);
		}

		while ($visitor->valid())
		{
			$token = $visitor->current();
			$type = SourceToken::getType($token);
			$value = SourceToken::value($token);

			if ($type == SourceToken::TYPE_STRING)
			{
				$output .= $value;
			}
			elseif ($type == SourceToken::TYPE_ELEMENT)
			{
				switch ($token[0])
				{
					case T_OPEN_TAG_WITH_ECHO:
						{
							if ($flags & SourceFile::OUTPUT_IGNORE_PHPTAGS)
							{
								$output .= 'echo (';
								$echoTag = true;
							}
							else
							{
								$output .= $value;
							}
						}
					break;
					case T_OPEN_TAG:
						{
							if (!($flags & SourceFile::OUTPUT_IGNORE_PHPTAGS))
							{
								$output .= $value;
							}

							if (($flags & SourceFile::OUTPUT_FORCE_WHITESPACE) &&
								(count($namespaces) == 0))
							{
								$output .= 'namespace';
								$s = ($flags & SourceFile::OUTPUT_CONDENSED_WHITESPACES) ? $condensedWhitespace : PHP_EOL;
								$output .= $s . '{' . $s;
							}
						}
					break;
					case T_CLOSE_TAG:
						{
							if ($echoTag)
							{
								echo ');';
							}
							else
							{
								if (($flags & SourceFile::OUTPUT_FORCE_WHITESPACE) &&
									(count($namespaces) == 0))
								{
									$s = ($flags & SourceFile::OUTPUT_CONDENSED_WHITESPACES) ? $condensedWhitespace : PHP_EOL;
									$output .= $s . '}';
								}

								if (!($flags & SourceFile::OUTPUT_IGNORE_PHPTAGS))
								{
									$output .= $value;
								}
							}
							$echoTag = false;
						}
					case T_INLINE_HTML:
						{
							if (!($flags & SourceFile::OUTPUT_IGNORE_INLINE_HTML))
							{
								$output .= $value;
							}
						}
					break;
					case T_WHITESPACE:
						{
							$output .= (($flags & SourceFile::OUTPUT_CONDENSED_WHITESPACES) ? $condensedWhitespace : $value);
						}
					break;
					case T_COMMENT:
					case T_DOC_COMMENT:
						{
							if (!($flags & SourceFile::OUTPUT_IGNORE_COMMENTS))
							{
								$output .= $value;
							}
						}
					break;
					default:
						$output .= $value;
				}
			}

			$visitor->next();
			if (strlen($output))
			{
				$condensedWhitespace = ' ';
			}
		}

		return $output;
	}

	/**
	 * Dump token table to a condensed format
	 *
	 * @param array $tokens
	 *        	Token array given by \token_get_all ()
	 * @param string $eol
	 *        	A string to add after each entry output
	 * @param number $flags
	 *        	Display options
	 */
	public static function dump($tokens, $eol = PHP_EOL, $flags = 0)
	{
		$i = 0;
		$result = '';
		foreach ($tokens as $t)
		{
			$type = SourceToken::getType($t);
			$name = ($type == SourceToken::TYPE_ELEMENT) ? SourceToken::name($t[0]) : 'string';
			$value = SourceToken::value($t);

			if (($flags & SourceFile::DUMP_CONDENSED_WHITESPACES) &&
				($type == SourceToken::TYPE_ELEMENT) && ($t[1] == T_WHITESPACE))
			{
				$value = '';
			}

			if ($flags & SourceFile::DUMP_SINGLE_LINE)
			{
				$value = str_replace("\r", '<CR>', str_replace("\n", '<LF>', $value));
			}

			if ($i > 0)
			{
				$result .= $eol;
			}

			$result .= '[' . $i . ', ' . $name . '] <' . $value . '>';

			$i++;
		}

		return $result;
	}
}

/**
 * Iterate a token array
 */
class TokenVisitor implements \iterator, \ArrayAccess, \Countable
{

	/**
	 *
	 * @param array $tokens
	 *        	A token array given by \token_get_all()
	 */
	public function __construct(&$tokens)
	{
		$this->tokenArray = $tokens;
		$this->tokenCount = count($this->tokenArray);
		$this->tokenIndex = -1;
		$this->state = [];
	}

	/**
	 * Move token index to a given value
	 *
	 * @param integer $index
	 * @return integer
	 */
	public function setTokenIndex($index)
	{
		if ($index < 0)
		{
			$this->tokenIndex = -1;
		}
		elseif ($index >= $this->tokenCount)
		{
			$this->tokenIndex = $this->tokenCount;
		}

		$this->tokenIndex = $index;
		return $this->tokenIndex;
	}

	/**
	 * Move iterator the the next token of the given type
	 *
	 * @param mixed $nextElementType
	 *        	One of the php parser token type or a string
	 * @return mixed An element of the token array or @null if the given token type was not found
	 */
	public function moveToToken($nextElementType)
	{
		return (SourceToken::move_next($this->tokenArray, $this->tokenIndex, $nextElementType));
	}

	/**
	 * Search position of a set of tokens types after the current token
	 *
	 * @param array $nextElementTypes
	 *        	Array of token types
	 * @param boolean $tokenIndexAsResultKey
	 *        	If @true,
	 *        	the result array keys are the search result token indexes.
	 * @return array of search result
	 *         A search result is an associative array with the following keys
	 *         * index: Token index in token array
	 *         * token: Token information
	 */
	public function queryNextTokens($nextElementTypes, $tokenIndexAsResultKey = false)
	{
		if (!is_array($nextElementTypes))
		{
			$nextElementTypes = [
				$nextElementTypes
			];
		}

		$s = $this->key();
		$result = [];
		foreach ($nextElementTypes as $e)
		{
			$t = $this->moveToToken($e);
			$r = [
				'index' => $this->key(),
				'token' => $t
			];
			if ($tokenIndexAsResultKey)
			{
				$result[$this->key()] = $r;
			}
			else
			{
				$result[] = $r;
			}
			$this->setTokenIndex($s);
		}

		return $result;
	}

	/**
	 * Store current token index
	 *
	 * @return integer Current token index
	 */
	public function pushState()
	{
		$this->state = array_push($this->tokenIndex);
		return $this->tokenIndex;
	}

	/**
	 * Pop the last token index state stored and move the visitor token index to this value
	 *
	 * @return integer new token index
	 */
	public function popState()
	{
		if (count($this->state))
		{
			$this->tokenIndex = array_pop();
		}

		return $this->tokenIndex;
	}

	/**
	 * Get the type of the current token
	 *
	 * @return integer One of the Type* constnats
	 */
	public function currentType()
	{
		return SourceToken::getType($this->current());
	}

	// Iterator
	public function current()
	{
		if ($this->tokenIndex < 0)
		{
			if ($this->tokenCount)
			{
				$this->next();
			}
			else
			{
				return null;
			}
		}
		return $this->tokenArray[$this->tokenIndex];
	}

	public function next()
	{
		$this->tokenIndex++;
	}

	public function key()
	{
		return $this->tokenIndex;
	}

	public function valid()
	{
		return (($this->tokenIndex < $this->tokenCount) && ($this->tokenCount > 0));
	}

	public function rewind()
	{
		$this->tokenIndex = -1;
	}

	// ArrayAccess
	public function offsetExists($offset)
	{
		return (($offset >= 0) && ($offset < $this->tokenCount));
	}

	public function offsetGet($offset)
	{
		return $this->tokenArray[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new \Exception('offsetSet is not allowed');
	}

	public function offsetUnset($offset)
	{
		throw new \Exception('offsetUnset is not allowed');
	}

	// Countable
	public function count()
	{
		return $this->tokenCount;
	}

	/**
	 *
	 * @var array
	 */
	private $tokens;

	/**
	 *
	 * @var integer
	 */
	private $tokenIndex;

	/**
	 * Number of elements of $tokens
	 *
	 * @var integer
	 */
	private $tokenCount;

	/**
	 *
	 * @var array $state
	 */
	private $state;
}

/**
 * Tokenized version of a PHP source code file
 */
class SourceFile
{

	/**
	 * Remove line feeds in token content
	 *
	 * @var integer
	 */
	const DUMP_SINGLE_LINE = 0x01;

	/**
	 * Always display whitespaces as a single space
	 *
	 * @var integer
	 */
	const DUMP_CONDENSED_WHITESPACES = 0x02;

	/**
	 * Output all whitespaces as single space
	 *
	 * @var integer
	 */
	const OUTPUT_CONDENSED_WHITESPACES = 0x02;

	/**
	 * Output code inside anonymous namespace if the code does not
	 * reference any namespace
	 *
	 * @var integer
	 */
	const OUTPUT_FORCE_WHITESPACE = 0x04;

	/**
	 * Do not output PHP open/close tags
	 *
	 * @var integer
	 */
	const OUTPUT_IGNORE_PHPTAGS = 0x08;

	/**
	 * Ignore all tokens which are not PHP code.
	 * Implies @c SourceFile::OUTPUT_IGNORE_PHPTAGS
	 *
	 * @var integer
	 */
	const OUTPUT_IGNORE_INLINE_HTML = 0x18;

	/**
	 *
	 * @var integer
	 */
	const OUTPUT_IGNORE_COMMENTS = 0x20;

	/**
	 *
	 * @param string $fileName
	 *        	PHP source file path
	 */
	public function __construct($fileName)
	{
		$this->tokens = \token_get_all(file_get_contents($fileName));
		$this->namespaces = [];
		$this->parse();
	}

	public function __toString()
	{
		return $this->asString(0);
	}

	/**
	 *
	 * @return \NoreSources\TokenVisitor
	 */
	public function getTokenVisitor()
	{
		return SourceToken::getVisitor($this->tokens);
	}

	/**
	 *
	 * @param number $flags
	 * @return string
	 */
	public function asString($flags = 0)
	{
		return SourceToken::output($this->tokens, $flags, $this->namespaces);
	}

	/**
	 *
	 * @param string $eol
	 * @param number $flags
	 * @return string
	 */
	public function dumpTokens($eol = PHP_EOL, $flags = 0)
	{
		return SourceToken::dump($this->tokens, $eol, $flags);
	}

	/**
	 * Get source file namespaces
	 *
	 * @return array
	 */
	public function getNamespaces()
	{
		return $this->namespaces;
	}

	private function parse()
	{
		$this->namespaces = SourceToken::getNamespaces($this->tokens);
	}

	/**
	 * Source file token table
	 *
	 * @var array
	 */
	private $tokens;

	/**
	 * Source file namespaces
	 *
	 * @var array
	 */
	private $namespaces;
}