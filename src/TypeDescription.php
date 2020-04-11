<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

/**
 * Universal type description utility class
 */
class TypeDescription
{

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
	public static function getLocalName($element, $elementIsClassName = false)
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
	public static function getNamespaces($element, $elementIsClassName = false)
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
	public static function isSubclassOf($element, $parent, $elementIsClassName = false)
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
	 */
	public static function hasStringRepresentation($element)
	{
		if (\is_object($element))
			return \method_exists($element, '__toString');
		return (\is_string($element) || \is_integer($element) || \is_float($element) ||
			\is_bool($element) || \is_null($element));
	}
}