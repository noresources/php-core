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
 * @param array $tokenArray A token array given by token_get_all()
 */
function token_get_iterator(&$tokenArray)
{
	return (new TokenIterator($tokenArray));
}

/**
 * Iterate a token array
 */
class TokenIterator implements \iterator, \ArrayAccess, \Countable
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
	}

	public function goToNext($nextElementType)
	{
		return (token_move_next($this->tokenArray, $this->tokenIndex, $nextElementType));
	}

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

	private $tokenArray;

	private $tokenIndex;

	private $tokenCount;
}