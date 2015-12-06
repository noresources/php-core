<?php

/**
 * Copyright © 2012-2015 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

const kTokenTypeUnknown = -1;
const kTokenTypeString = 0;
const kTokenTypeElement = 1;

/**
 * Remove line feeds in token content
 * @var integer
 */
const kTokenDumpSingleLine = 0x1;

/**
 * Always display whitespaces as a single space
 * @var integer
 */
const kTokenDumpCondensedWhitespaces = 0x2;

/**
 * Move to the next token kind
 * @param array $tokenArray A token arary given by token_get_all()
 * @param int $tokenIndex Index of the current token
 * @param mixed $nextElementType Token to search
 *       
 * @return The
 */
function token_move_next(&$tokenArray, &$tokenIndex, $nextElementType)
{
	$c = count($tokenArray);
	$tokenIndex++;
	while ($tokenIndex < $c)
	{
		$token = $tokenArray [$tokenIndex];
		if (\is_array($token) && \is_int($nextElementType) && ($token [0] == $nextElementType))
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
 * @param mixed $token a element of the token array given by token_get_all()
 * @return integer One of kTokenType* constants
 */
function token_type($token)
{
	if (is_array($token) && (count($token) == 3))
	{
		return kTokenTypeElement;
	}
	elseif (is_string($token))
	{
		return kTokenTypeString;
	}
	
	return kTokenTypeUnknown;
}

/**
 *
 * @param mixed $token An element of the token array given by token_get_all()
 */
function token_value($token)
{
	if (is_array($token))
	{
		return $token [1];
	}
	elseif (is_string($token))
	{
		return $token;
	}
	return null;
}

/**
 *
 * @param array $tokenArray A token array given by token_get_all()
 */
function token_get_visitor(&$tokenArray)
{
	return (new TokenVisitor($tokenArray));
}

/**
 * Dump token table to a condensed format
 * @param unknown $tokens Token array given by token_get_all ()
 * @param string $eol A string to add after each entry output
 * @param number $flags Display options
 */
function token_dump($tokens, $eol = PHP_EOL, $flags = 0)
{
	$i = 0;
	$result = '';
	foreach ($tokens as $t)
	{
		$type = token_type($t);
		$name = ($type == kTokenTypeElement) ? token_name($t [0]) : 'string';
		$value = token_value($t);
		
		if (($flags & kTokenDumpCondensedWhitespaces) && ($type == kTokenTypeElement) && ($t [1] == T_WHITESPACE))
		{
			$value = '';
		}
		
		if ($flags & kTokenDumpSingleLine)
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

/**
 * Iterate a token array
 */
class TokenVisitor implements \iterator, \ArrayAccess, \Countable
{

	/**
	 *
	 * @param array $tokenArray A token array given by token_get_all()
	 */
	public function __construct(&$tokenArray)
	{
		$this->tokenArray = $tokenArray;
		$this->tokenCount = count($this->tokenArray);
		$this->tokenIndex = -1;
		$this->state = array ();
	}

	/**
	 * Move token index to a given value
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
	 * @param mixed $nextElementType One of the php parser token type or a string
	 * @return mixed An element of the token array or @null if the given token type was not found
	 */
	public function moveToToken($nextElementType)
	{
		return (token_move_next($this->tokenArray, $this->tokenIndex, $nextElementType));
	}

	/**
	 * Search position of a set of tokens types after the current token
	 * @param array $nextElementTypes Array of token types
	 * @param boolean $tokenIndexAsResultKey If @true,
	 *        the result array keys are the search result token indexes.
	 * @return array of search result
	 *         A search result is an associative array with the following keys
	 *         * index: Token index in token array
	 *         * token: Token information
	 */
	public function queryNextTokens($nextElementTypes, $tokenIndexAsResultKey = false)
	{
		if (!is_array($nextElementTypes))
		{
			$nextElementTypes = array (
					$nextElementTypes 
			);
		}
		
		$s = $this->key();
		$result = array ();
		foreach ($nextElementTypes as $e)
		{
			$t = $this->moveToToken($e);
			$r = array (
					'index' => $this->key(),
					'token' => $t 
			);
			if ($tokenIndexAsResultKey)
			{
				$result [$this->key()] = $r;
			}
			else
			{
				$result [] = $r;
			}
			$this->setTokenIndex($s);
		}
		
		return $result;
	}

	/**
	 * Store current token index
	 * @return integer Current token index
	 */
	public function pushState()
	{
		$this->state = array_push($this->tokenIndex);
		return $this->tokenIndex;
	}

	/**
	 * Pop the last token index state stored and move the visitor token index to this value
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
	 * @return integer One of the kTokenType* constnats
	 */
	public function currentType()
	{
		return token_type($this->current());
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
		return $this->tokenArray [$this->tokenIndex];
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
		return $this->tokenArray [$offset];
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
	private $tokenArray;

	/**
	 *
	 * @var integer
	 */
	private $tokenIndex;

	/**
	 * Number of elements of $tokenArray
	 * @var integer
	 */
	private $tokenCount;

	/**
	 *
	 * @var array $state
	 */
	private $state;
}

class SourceFile
{

	public function __construct($fileName)
	{
		$this->tokens = token_get_all(file_get_contents($fileName));
		$this->namespaces = array ();
		$this->parse();
	}

	public function getTokenVisitor()
	{
		return token_get_visitor($this->tokens);
	}

	public function dumpTokens($eol = PHP_EOL, $flags = 0)
	{
		return token_dump($this->tokens, $eol, $flags);
	}

	public function getNamespaces()
	{
		return $this->namespaces;
	}

	private function parse()
	{
		$visitor = token_get_visitor($this->tokens);
		
		// Search for namespaces
		$ns = null;
		while (($ns = $visitor->moveToToken(T_NAMESPACE)))
		{
			$search = $visitor->queryNextTokens(array (
					T_STRING,
					'{',
					';' 
			), true);
			ksort($search);
			list ( $index, $entry ) = each($search);
			$token = $entry ['token'];
			$name = '';
			if ((token_type($token) == kTokenTypeElement) && ($token [0] == T_STRING))
			{
				$name = $token [1];
			}
			
			$item = array (
					'index' => $visitor->key(),
					'name' => $name 
			);
			
			$this->namespaces [] = $item;
		}
	}

	private $tokens;

	private $namespaces;
}