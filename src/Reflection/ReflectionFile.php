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
 * PHP source file informations
 */
class ReflectionFile
{

	/**
	 *
	 * @param string $filename
	 *        	PHP source file path
	 * @throws \ReflectionException::
	 */
	public function __construct($filename)
	{
		if (!\file_exists($filename))
			throw new \ReflectionException(
				$filename . ': File not found', 404);
		$this->filename = \realpath($filename);
	}

	/**
	 *
	 * @return string File absolute path
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Get a map of all file-space "use" statements
	 *
	 * @return string[] class name -> alias map
	 */
	public function getUseStatements()
	{
		if (isset($this->useStatements))
			return $this->useStatements;
		$this->getTokens();
		$this->useStatements = [];
		$index = 0;
		while ($index < $this->tokenCount)
		{
			$type = $this->tokens[$index][0];
			if ($type == T_CLASS ||
				$type == T_TRAIT && $type == T_INTERFACE)
				break;

			$index++;
			if ($type != T_USE)
			{
				continue;
			}

			$index = $this->skipWhitespace($index);
			$name = '';
			$index = $this->readQualifiedName($name, $index);

			if (($index + 3) < ($this->tokenCount) &&
				$this->tokens[$index][0] == T_WHITESPACE &&
				$this->tokens[$index + 1][0] == T_AS &&
				$this->tokens[$index + 2][0] == T_WHITESPACE &&
				$this->tokens[$index + 3][0] == T_STRING &&
				\preg_match(chr(1) . self::PATTERN_IDENTIFIER . chr(1),
					$this->tokens[$index + 3][1]))
			{
				$index += 3;
				$alias = $this->tokens[$index][1];
			}
			else
				$alias = \preg_replace('/.*\\\\/', '', $name);

			$this->useStatements[$name] = $alias;

			$index++;
		}

		return $this->useStatements;
	}

	/**
	 * Get the qualified name of a class, interface or trait
	 * declared or used in this file.
	 *
	 * @param string $name
	 *        	Local PHP entity name
	 * @return string Qualified entity name. Name is resolved by looking into "use" statements
	 *         first,
	 *         then by assuming the class is part of the file namespace.
	 *
	 */
	public function getQualifiedClassName($name)
	{
		if (\substr($name, 0, 1) == '\\')
			return $name;

		$map = $this->getUseStatements();
		$map = \array_flip($map);

		if (($qualifiedName = Container::keyValue($map, $name, false)))
			return $qualifiedName;

		$ns = $this->getNamespaces();
		switch (\count($ns))
		{
			case 0:
				return $name;
			case 1:
				return $ns[0] . '\\' . $name;
		}

		throw new \ReflectionException(
			'Unable to determine class name in file with ' . \count($ns) .
			' namespaces');
	}

	/**
	 * Get the names of namespaces declared in the PHP file
	 *
	 * @return string[]
	 */
	public function getNamespaces()
	{
		return $this->getDefinition(T_NAMESPACE);
	}

	/**
	 * Get interface names defined in this file
	 *
	 * @return array
	 */
	public function getInterfaces()
	{
		return $this->getDefinition(T_INTERFACE);
	}

	/**
	 * Get trait names defined in this file
	 *
	 * @return array
	 */
	public function getTraits()
	{
		return $this->getDefinition(T_TRAIT);
	}

	/**
	 * Get class names defined in this file
	 *
	 * @return array
	 */
	public function getClasses()
	{
		return $this->getDefinition(T_CLASS,
			function ($type, $index) {
				if ($index == 0)
					return true;
				if (!\is_array($this->tokens[$index - 1]))
					return true;
				return $this->tokens[$index - 1][0] !== T_DOUBLE_COLON;
			});
	}

	/**
	 * Get all file tokens
	 *
	 * @return array Token array given by the PHP token_get_all() function
	 */
	public function getTokens()
	{
		if (!isset($this->tokens))
		{
			$this->tokens = \token_get_all(
				\file_get_contents($this->filename));
			$this->tokenCount = \count($this->tokens);
		}

		return $this->tokens;
	}

	private function normalizeIdentifierPath($name)
	{}

	private function findNextTokenOfType($index, $type)
	{
		for (; $index < $this->tokenCount; $index++)
		{
			if (!\is_array($this->tokens[$index]))
				continue;
			if ($this->tokens[$index][0] === $type)
				return $index;
		}

		return false;
	}

	private function skipWhitespace($index)
	{
		do
		{
			if ($this->tokens[$index][0] != T_WHITESPACE)
				return $index;
			$index++;
		}
		while ($index < $this->tokenCount);
		return $index;
	}

	private function readQualifiedName(&$name, $index)
	{
		$name = '';
		$i = $index;

		if (defined('T_NAME_QUALIFIED')) // PHP 8
		{
			$expected = [
				T_NAME_FULLY_QUALIFIED,
				T_NAME_QUALIFIED,
				T_STRING
			];
			$token = $this->tokens[$index];
			if (!(\is_array($token) && \in_array($token[0], $expected)))
				throw new \ReflectionException(
					'Invalid token. Expect T_STRING or T_NAME_[FULLY_]QUALIFIED. Got ' .
					(\is_array($token) ? \token_name($token[0]) . ' ' .
					$token[1] : $token));
			$name = $token[1];
			$index++;
			return $index;
		}

		while ($index < $this->tokenCount)
		{
			$token = $this->tokens[$index];
			switch ($token[0])
			{
				case T_NS_SEPARATOR:
					$name .= $token[1];
				break;
				case T_STRING:
					if (!\preg_match(
						chr(1) . self::PATTERN_IDENTIFIER . chr(1),
						$token[1]))
						return $index;
					$name .= $token[1];
				break;
				default:
					return $index;
			}
			$index++;
		}
		return $index;
	}

	private function getDefinition($type, $validator = null,
		$finalizer = null)
	{
		if (!isset($this->definitions))
			$this->definitions = [];
		if (!isset($this->definitions[$type]))
			$this->definitions[$type] = $this->parseDefinition($type,
				$validator, $finalizer);
		return \array_values($this->definitions[$type]);
	}

	private function parseDefinition($type, $validator = null,
		$finalizer = null)
	{
		if (!isset($this->tokens))
			$this->getTokens();

		if ($type != T_NAMESPACE)
			$this->getNamespaces();

		$definitions = [];
		$index = 0;
		do
		{
			$index = $this->findNextTokenOfType($index, $type);
			if ($index === false)
				break;
			$at = $index;
			$index++;

			if (\is_callable($validator) &&
				!\call_user_func_array($validator, [
					$type,
					$at
				]))
				continue;

			$index = $this->skipWhitespace($index);

			$name = '';
			$index = $this->readQualifiedName($name, $index);

			if ($type != T_NAMESPACE)
			{
				$ns = null;
				foreach ($this->definitions[T_NAMESPACE] as $ni => $n)
				{
					if ($ni > $at)
						break;
					$ns = $n;
				}

				if ($ns)
					$name = $ns . '\\' . $name;
			}

			if (\is_callable($finalizer))
				$name = \call_user_func_array($finalizer,
					[
						$type,
						$at,
						$name,
						$index
					]);
			$definitions[$at] = $name;
		}
		while ($index < $this->tokenCount);

		return $definitions;
	}

	const PATTERN_IDENTIFIER = '[a-zA-Z_][a-zA-Z0-9_]*';

	/**
	 *
	 * @var string
	 */
	private $filename;

	/**
	 *
	 * @var string[]
	 */
	private $useStatements;

	/**
	 * Classes, interfaces, trait and namespace definitions
	 *
	 * @var string[][]
	 */
	private $definitions;

	/**
	 *
	 * @var array
	 */
	private $tokens;

	/**
	 *
	 * @var integer
	 */
	private $tokenCount;
}
