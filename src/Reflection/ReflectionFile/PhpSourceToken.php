<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection\ReflectionFile;

use NoreSources\Container\Container;
use NoreSources\Type\ArrayRepresentation;
use NoreSources\Type\StringRepresentation;

/**
 * "Polyfill" of the PHP 8 PhpToken class
 *
 * @see https://www.php.net/manual/en/class.phptoken.php
 *
 */
class PhpSourceToken implements StringRepresentation, \ArrayAccess,
	ArrayRepresentation
{

	/**
	 * Token descriptor given by token_get_all()
	 *
	 * @param array|string $token
	 *        	Token description
	 */
	function __construct($token, $index)
	{
		$this->index = $index;
		$this->token = $token;
	}

	public function __toString()
	{
		return $this->getTokenValue();
	}

	/**
	 *
	 * @return array <ul><li>0 Token type</li>
	 *         <li>1 Value </li>
	 *         <li>2 Line number </li></ul>
	 */
	public function getArrayCopy()
	{
		if (\is_array($this->token))
			return $this->token;
		return [
			T_STRING,
			$this->token,
			-1
		];
	}

	/**
	 *
	 * @return number
	 */
	public function getTokenIndex()
	{
		return $this->index;
	}

	/**
	 *
	 * @return string Token type name
	 */
	public function getTokenName()
	{
		return \is_array($this->token) ? \token_name($this->token[0]) : $this->token;
	}

	/**
	 *
	 * @return integer Token type ID
	 */
	public function getTokenType()
	{
		return \is_array($this->token) ? $this->token[0] : T_STRING;
	}

	/**
	 *
	 * @return string Token textual content
	 */
	public function getTokenValue()
	{
		return \is_array($this->token) ? $this->token[1] : $this->token;
	}

	/**
	 *
	 * @return integer Token line number
	 */
	public function getTokenLine()
	{
		return \is_array($this->token) ? $this->token[2] : -1;
	}

	/**
	 *
	 * @param int|string|array $kind
	 * @return boolean
	 * @see https://www.php.net/manual/en/phptoken.is.php
	 */
	public function is($kind)
	{
		if (\is_string($kind))
			return $this->getTokenName() == $kind ||
				$this->getTokenValue() == $kind;
		elseif (\is_integer($kind))
			return $this->getTokenType() == $kind;

		if (!Container::isTraversable($kind))
		{
			foreach ($kind as $k)
			{
				if ($this->is($k))
					return true;
			}
			return false;
		}
	}

	/**
	 *
	 * @return boolean
	 * @see https://www.php.net/manual/en/phptoken.isignorable.php
	 */
	public function isIgnorable()
	{
		static $ignored = [
			T_WHITESPACE,
			T_COMMENT,
			T_DOC_COMMENT
		];
		return \in_array($this->getTokenType(), $ignored);
	}

	/**
	 *
	 * @var integer
	 */
	private $index;

	/**
	 *
	 * @var array|string
	 */
	private $token;

	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		switch ($offset)
		{
			case 0:
				return $this->getTokenType();
			case 1:
				return $this->getTokenValue();
			case 2:
				return $this->getTokenLine();
		}
		throw new \InvalidArgumentException();
	}

	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return (\is_integer($offset) && $offset < 3 && $offset >= 0);
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		throw new \RuntimeException('Read only ArrayAccess');
	}

	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		throw new \RuntimeException('Read only ArrayAccess');
	}
}
