<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Type;

/**
 * Universal type description utility class
 */
class TypeDescription
{

	/**
	 * Constant for unknown type or type family
	 *
	 * @var string
	 */
	const UNKNOWN = 'unknown';

	/**
	 *
	 * @param mixed $element
	 * @return string $element Full class name or data type name
	 *
	 *         This method use get_class () or gettype() depending on argument type.
	 */
	public static function getName($element)
	{
		if (\is_object($element))
			return get_class($element);

		return gettype($element);
	}

	/**
	 * Get the local class name (class name without namespace)
	 *
	 * This function is equivalent to ReflectionClass::getShourName() for classes
	 * and gettype() for other data types
	 *
	 * @param object|string $element
	 *        	Class instance or class name
	 * @param boolean $elementIsClassName
	 *        	Consider the first argument as a class name
	 *
	 * @return Local name of class
	 */
	public static function getLocalName($element,
		$elementIsClassName = false)
	{
		$className = null;
		if (\is_object($element))
			$className = self::getName($element);
		elseif (\is_string($element) && $elementIsClassName)
			$className = $element;
		else
			return self::getName($element);

		$p = \strrpos($className, '\\');
		if ($p === false)
			return $className;

		return \substr($className, $p + 1);
	}

	/**
	 * Get the list of namespace where the class live.
	 *
	 * @param mixed $element
	 *        	Class instance or class name
	 * @param boolean $elementIsClassName
	 *        	Consider the first argument as a class name
	 *
	 * @return array List of namespaces
	 */
	public static function getNamespaces($element,
		$elementIsClassName = false)
	{
		$className = null;
		if (\is_object($element))
			$className = self::getName($element);
		elseif (\is_string($element) && $elementIsClassName)
			$className = $element;
		else
			return [];

		$namespaces = \explode('\\', $className);
		\array_pop($namespaces);
		return $namespaces;
	}

	/**
	 * Indicates if the given element is of given class name.
	 *
	 * This method is equivalent to is_subclass_of
	 *
	 * @param mixed $element
	 *        	Class name or Class instance
	 * @param string $parent
	 *        	Class name
	 *
	 * @return boolean @true if $element is a subclass of $parent
	 */
	public static function isA($element, $parent,
		$elementIsClassName = false)
	{
		$isClassName = false;
		if (!\is_object($element))
		{
			if (\is_string($element) && $elementIsClassName)
				$isClassName = true;
			else
				return false;
		}

		return \is_a($element, $parent, $isClassName);
	}

	/**
	 * Indicates if the type name refer to a PHP primitive type
	 *
	 * @param stringown $typename
	 *        	Type name
	 * @return boolean TRUE if $typename is the name of a PHP primitive type
	 */
	public static function isPrimitiveType($typename)
	{
		return \in_array(\strtolower($typename),
			[
				'string',
				'int',
				'integer',
				'scalar',
				'float',
				'double',
				'bool',
				'boolean',
				'array',
				'object',
				'callable',
				'iterable',
				'resource',
				'null'
			]);
	}

	/**
	 * Indicates if the given type name refer to a pseudo-type.
	 *
	 * Pseudo-types may appear in PHPDoc blocks or in function definitions.
	 *
	 * @param string $typename
	 *        	Type name
	 * @return boolean TRUE if $typename is the name of a pseudo-type.
	 */
	public static function isPseudoType($typename)
	{
		return \in_array(\strtolower($typename),
			[
				'mixed',
				'void',
				'true',
				'false',
				'self',
				'static',
				'$this'
			]);
	}

	/**
	 * PHP primitive type
	 *
	 * @var string
	 */
	const FAMILY_PRIMITIVE = 'primitive';

	/**
	 * Pseudo type (void, mixed)
	 *
	 * @var string
	 */
	const FAMILY_PSEUDO_TYPE = 'pseudo-type';

	/**
	 * Get type name family
	 *
	 * @param string $typename
	 *        	Type name
	 * @return string Type family. One of FAMILY_* constant values
	 */
	public static function getTypenameFamily($typename)
	{
		if (self::isPrimitiveType($typename))
			return self::FAMILY_PRIMITIVE;
		elseif (self::isPseudoType($typename))
			return self::FAMILY_PRIMITIVE;
		return self::UNKNOWN;
	}

	/**
	 * Indicates if the given element is a subclass of a given class name.
	 *
	 * This method is equivalent to is_subclass_of
	 *
	 * @param mixed $element
	 *        	Class name or Class instance
	 * @param string $parent
	 *        	Class name
	 *
	 * @return boolean @true if $element is a subclass of $parent
	 */
	public static function isSubclassOf($element, $parent,
		$elementIsClassName = false)
	{
		$isClassName = false;
		if (!\is_object($element))
		{
			if (\is_string($element) && $elementIsClassName)
				$isClassName = true;
			else
				return false;
		}

		return \is_subclass_of($element, $parent, $isClassName);
	}

	/**
	 * Indicates if the given variable can be converted to string using TypeConversion utility
	 *
	 * @param mixed $element
	 *        	Any type
	 * @param boolean $strict
	 *        	The function will return true only if $element can be converted to string using
	 *        	the \strval() function. Otherwise, any type supported by TypeConversion::toString
	 *        	() will return true
	 * @return boolean
	 */
	public static function hasStringRepresentation($element,
		$strict = true)

	{
		// PHP 8
		if ($element instanceof \Stringable)
			return true;

		if (!$strict)
		{
			if ($element instanceof \DateTimeInterface) // format ()
				return true;
			if ($element instanceof \DateTimeZone)
				return true;
			elseif ($element instanceof \Serializable) // szerialize()
				return true;
			elseif ($element instanceof \JsonSerializable) // encode (jsonSerialize)
				return true;
		}

		if (\is_object($element))
			return \method_exists($element, '__toString');
		return (\is_string($element) || \is_integer($element) ||
			\is_float($element) || \is_bool($element) ||
			\is_null($element));
	}
}