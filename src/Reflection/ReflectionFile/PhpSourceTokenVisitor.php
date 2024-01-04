<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection\ReflectionFile;

use NoreSources\Container\Container;
use NoreSources\Container\Stack;
use ArrayObject;
use Traversable;

/**
 * PHP source token visitor.
 */
class PhpSourceTokenVisitor implements \Iterator, \Countable
{

	/**
	 * Traverser event
	 *
	 * Raised when a opening curly-bracket ("{") is encountered
	 *
	 * @var string
	 */
	const EVENT_SCOPE_START = 'open';

	/**
	 * Traverser event
	 *
	 * Raised when a closing curly bracket ("}") is encountered
	 * or a semi-colon (";") delimiting a block or an element declaration.
	 *
	 * @var string
	 */
	const EVENT_SCOPE_END = 'close';

	/**
	 *
	 * @param string|\ArrayObject|Traversable $codeOrArray
	 *        	Token source
	 * @throws \InvalidArgumentException
	 */
	public function __construct($codeOrArray)
	{
		if (\is_string($codeOrArray))
			$this->tokens = new ArrayObject(
				\token_get_all($codeOrArray));
		elseif ($codeOrArray instanceof \ArrayObject)
			$this->tokens = $codeOrArray;
		elseif (Container::isTraversable($codeOrArray))
			$this->tokens = new \ArrayObject(
				Container::createArray($codeOrArray));
		else
			throw new \InvalidArgumentException(
				'Array of token or PHP source code text expected');
		$this->rangeStart = 0;
		$this->rangeEnd = $this->tokens->count();
		$this->rewind();
	}

	public function setStartIndex($offset)
	{
		$this->rangeStart = max(0, min($this->tokens->count(), $offset));
		$this->rangeEnd = max($this->rangeStart, $this->rangeEnd);
		$this->rewind();
	}

	public function setEndIndex($offset)
	{
		$this->rangeEnd = max(0, min($this->tokens->count(), $offset));
		$this->rangeStart = min($this->rangeEnd, $this->rangeStart);
		$this->rewind();
	}

	public function setIndexRange($s, $e)
	{
		if ($e < $s)
			throw new \LogicException('START > END');
		$this->rangeStart = max(0, min($this->tokens->count(), $s));
		$this->rangeEnd = max(0, min($this->tokens->count(), $e));
		$this->rewind();
	}

	/**
	 *
	 * @param callable $callable
	 *        	Callable to invoke on each encountered token.
	 *        	The callable will receive the token index, the token and the current scope
	 * @throws \InvalidArgumentException
	 */
	public function traverse($callable = null)
	{
		if (!\is_callable($callable))
		{
			foreach ($this as $index => $token)
			{}
			return;
		}

		foreach ($this as $index => $token)
		{
			\call_user_func_array($callable,
				[
					$index,
					$token,
					$this->getCurrentScope()
				]);
		}
	}

	/**
	 *
	 * @return Number of tokens
	 */
	#[\ReturnTypeWillChange]
	public function count()
	{
		return $this->rangeEnd - $this->rangeStart;
	}

	#[\ReturnTypeWillChange]
	public function next()
	{
		$this->index++;
		if (!($this->valid() && Container::isArray($this->tokens)))
		{
			while ($this->scope->count())
				$this->closeScope();
			return;
		}

		$this->processCurrentToken();
	}

	#[\ReturnTypeWillChange]
	public function valid()
	{
		return ($this->index >= 0) && ($this->index < $this->rangeEnd);
	}

	/**
	 *
	 * @return PhpSourceToken
	 */
	#[\ReturnTypeWillChange]
	public function current()
	{
		return $this->getToken($this->index);
	}

	#[\ReturnTypeWillChange]
	public function rewind()
	{
		$this->index = $this->rangeStart;
		$this->scope = new Stack();
		$this->pendingScopeIndex = -1;
		if ($this->rangeEnd)
			$this->processCurrentToken();
	}

	#[\ReturnTypeWillChange]
	public function key()
	{
		return $this->index;
	}

	/**
	 * Register a callable that will receive scope events while traversing tokens
	 *
	 * @param callable $callable
	 *        	A callable to invoke on each scope event. Callable will receive
	 *        	the event type, the scope and the visitor itself
	 */
	public function setScopeEventHandler($callable = null)
	{
		if ($callable && !\is_callable($callable))
			throw new \InvalidArgumentException(
				'NULL or callable expected');
		$this->eventHandler = $callable;
	}

	/**
	 * Get current scope
	 *
	 * @return PhpSourceTokenScope
	 */
	public function getCurrentScope()
	{
		return $this->scope->top();
	}

	/**
	 *
	 * @param PhpSourceToken $tokens
	 *        	Source tokens
	 * @param integer $index
	 *        	Start token index
	 * @return integer Token index of the first non-whitespace token
	 */
	public static function skipWhitespace($tokens, $index, $count = -1)
	{
		if ($count < 0)
			$count = Container::count($tokens);
		do
		{
			if ($tokens[$index][0] != T_WHITESPACE)
				return $index;
			$index++;
		}
		while ($index < $count);
		return $index;
	}

	/**
	 *
	 * @param PhpSourceToken[] $tokens
	 *        	Source file tokens
	 * @param integer $index
	 *        	Element declaration first token index
	 * @return string
	 */
	public static function getDocComment($tokens, $index)
	{
		$comment = '';
		/** @var PhpSourceToken $token */

		while ($index >= 0)
		{
			$token = $tokens[$index];
			if ($token->getTokenType() == T_DOC_COMMENT)
				$comment = $token->getTokenValue() . $comment;
			elseif (!$token->isIgnorable())
				break;
			$index--;
		}
		return $comment;
	}

	private function getToken($index)
	{
		$token = $this->tokens[$index];
		if (!($token instanceof PhpSourceToken))
		{
			$token = new PhpSourceToken($token, $index);
			$this->tokens[$index] = $token;
		}
		return $token;
	}

	private function processCurrentToken()
	{
		$token = $this->tokens[$this->index];
		$type = (\is_string($token) ? T_STRING : $token[0]);

		if ($type == T_OPEN_TAG)
		{
			$this->pendingScopeIndex = $this->index;
			$this->openScope();
		}
		elseif ($type == T_NAMESPACE || $type == T_INTERFACE ||
			$type == T_TRAIT ||
			($type == T_CLASS && $this->index &&
			$this->tokens[$this->index - 1][0] != T_DOUBLE_COLON))
		{
			$this->pendingScopeIndex = $this->index;
		}
		elseif ($type == T_FUNCTION &&
			($scope = $this->getCurrentScope()) &&
			($t = $scope->entityToken) && ($t instanceof PhpSourceToken) &&
			\in_array($t->getTokenType(),
				[
					T_OPEN_TAG, // Free function
					T_NAMESPACE, // Free function in namespace
					// T_INTERFACE,
					T_TRAIT, // Class method in trait
					T_CLASS // Class method
				]))
		{
			$this->pendingScopeIndex = $this->index;
		}
		elseif ($type == T_STRING)
		{
			$value = \is_string($token) ? $token : $token[1];
			if ($value == ';')
			{
				if (($pendingIndex = $this->pendingScopeIndex) >= 0)
				{
					if ($this->tokens[$pendingIndex][0] == T_NAMESPACE)
						$this->openScope();
					elseif ($this->tokens[$pendingIndex][0] == T_FUNCTION)
						$this->pendingScopeIndex = -1;
				}
			}
			elseif ($value == '{')
			{
				$this->openScope();
			}
			elseif ($type == T_STRING && $value == '}')
			{
				$this->closeScope();
			}
		}
	}

	private function openScope()
	{
		$scope = new PhpSourceTokenScope();
		if ($this->scope->count())
			$scope->parentEntityToken = $this->scope->top()->entityToken;
		$scope->level = $this->scope->count();
		$scope->startTokenIndex = $this->index;
		if ($this->pendingScopeIndex >= 0)
			$scope->entityToken = $this->getToken(
				$this->pendingScopeIndex);
		$this->pendingScopeIndex = -1;
		$this->scope->push($scope);
		if (\is_callable($this->eventHandler))
			call_user_func_array($this->eventHandler,
				[
					self::EVENT_SCOPE_START,
					$scope,
					$this
				]);
		return $scope;
	}

	private function closeScope()
	{
		/**
		 *
		 * @var PhpSourceTokenScope $scope
		 */
		$scope = $this->scope->pop();
		$scope->endTokenIndex = min($this->index, $this->rangeEnd - 1);
		if (\is_callable($this->eventHandler))
			call_user_func_array($this->eventHandler,
				[
					self::EVENT_SCOPE_END,
					$scope,
					$this
				]);
	}

	/**
	 * Token array
	 *
	 * @var \ArrayObject
	 */
	private $tokens;

	/**
	 *
	 * @var integer
	 */
	private $rangeStart;

	/**
	 *
	 * @var integer
	 */
	private $rangeEnd;

	/**
	 *
	 * @var integer
	 */
	private $index;

	/**
	 *
	 * @var Stack Stack of PhpSourceTokenScope
	 */
	private $scope;

	private $pendingScopeIndex;

	private $eventHandler;
}
