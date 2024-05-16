<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Type;

use NoreSources\Container\Container;

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
	 * @param mixed $variable
	 *        	Object or primitive
	 * @return string $variable Full class name or data type name
	 *
	 *         This method use get_class () or gettype() depending on argument type.
	 */
	public static function getName($variable)
	{
		if (\is_object($variable))
			return get_class($variable);

		return gettype($variable);
	}

	/**
	 * Get the local class name (class name without namespace)
	 *
	 * This function is equivalent to ReflectionClass::getShourName() for classes
	 * and gettype() for other data types
	 *
	 * @param object|string $variable
	 *        	Object or class name
	 * @param boolean $variableIsClassName
	 *        	Consider the first argument as a class name
	 *
	 * @return Local name of class
	 */
	public static function getLocalName($variable,
		$variableIsClassName = false)
	{
		$className = null;
		if (\is_object($variable))
			$className = self::getName($variable);
		elseif (\is_string($variable) && $variableIsClassName)
			$className = $variable;
		else
			return self::getName($variable);

		$p = \strrpos($className, '\\');
		if ($p === false)
			return $className;

		return \substr($className, $p + 1);
	}

	/**
	 * Get the list of namespace where the class live.
	 *
	 * @param mixed $variable
	 *        	Object or class name
	 * @param boolean $variableIsClassName
	 *        	Consider the first argument as a class name
	 *
	 * @return array List of namespaces
	 */
	public static function getNamespaces($variable,
		$variableIsClassName = false)
	{
		$className = null;
		if (\is_object($variable))
			$className = self::getName($variable);
		elseif (\is_string($variable) && $variableIsClassName)
			$className = $variable;
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
	 * @param mixed $variable
	 *        	Class name or Object
	 * @param string $parent
	 *        	Class name
	 *
	 * @return boolean @true if $variable is a subclass of $parent
	 */
	public static function isA($variable, $parent,
		$variableIsClassName = false)
	{
		$isClassName = false;
		if (!\is_object($variable))
		{
			if (\is_string($variable) && $variableIsClassName)
				$isClassName = true;
			else
				return false;
		}

		return \is_a($variable, $parent, $isClassName);
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
	 * @param mixed $variable
	 *        	Class name or Object
	 * @param string $parent
	 *        	Class name
	 *
	 * @return boolean @true if $variable is a subclass of $parent
	 */
	public static function isSubclassOf($variable, $parent,
		$variableIsClassName = false)
	{
		$isClassName = false;
		if (!\is_object($variable))
		{
			if (\is_string($variable) && $variableIsClassName)
				$isClassName = true;
			else
				return false;
		}

		return \is_subclass_of($variable, $parent, $isClassName);
	}

	/**
	 *
	 * @param string $typeName
	 *        	Target type name
	 * @param mixed $variable
	 *        	Class name or object
	 * @param array $options
	 *        	Unused.
	 * @return boolean TRUE if class has a factory function for the given input type
	 */
	public static function hasFactoryFrom($typeName, $variable,
		$options = [])
	{
		$className = $variable;
		if (\is_object($variable))
			$className = \get_class($variable);

		return \is_callable([
			$className,
			'createFrom' . $typeName
		]);
	}

	/**
	 * Input value is a class name (instead of object)
	 *
	 * @var string
	 */
	const REPRESENTATION_IS_CLASS_NAME = 'is-class-name';

	/**
	 * String representation.
	 *
	 * Expected value type: boolean
	 *
	 * @used-by hasStringRepresentation
	 *
	 * @var string
	 */
	const REPRESENTATION_STRICT = 'string';

	/**
	 *
	 * @param string $typeName
	 *        	Type name
	 * @param mixed $variable
	 *        	Object or primitive
	 * @param array $options
	 *        	Options
	 * @return boolean TRUE if $variable has a representation or conversion to $typeName.
	 */
	public static function hasRepresentation($typeName, $variable,
		$options = array())
	{
		$specialized = 'has' . $typeName . 'Representation';
		$callable = [
			static::class,
			$specialized
		];

		if (\is_callable($callable))
		{
			$arguments = func_get_args();
			\array_shift($arguments);
			return \call_user_func_array($callable, $arguments);
		}

		if (\is_bool($options))
			$options = [
				self::REPRESENTATION_IS_CLASS_NAME => $options
			];
		$isClassName = Container::keyValue($options,
			self::REPRESENTATION_IS_CLASS_NAME, false);

		if ($isClassName)
		{
			if (!\class_exists($variable))
				return false;
		}
		elseif (!\is_object($variable))
			return false;

		$methods = [
			'get' . $typeName . 'Value',
			'to' . $typeName
		];

		foreach ($methods as $name)
			if (\method_exists($variable, $name))
				return true;

		return false;
	}

	/**
	 * Indicates if the given variable can be converted to string using TypeConversion utility
	 *
	 * @param mixed $variable
	 *        	Any type
	 * @param array|boolean $options
	 *        	If $options is a boolean, assumes $options = ['string' => $options].
	 *        	If string, the function will return true only if $variable can be converted to
	 *        	string using
	 *        	the \strval() function. Otherwise, any type supported by TypeConversion::toString
	 *        	() will return true
	 * @return boolean
	 */
	public static function hasStringRepresentation($variable,
		$options = array())

	{
		if (\is_bool($options))
			$options = [
				self::REPRESENTATION_STRICT => $options
			];
		$strict = Container::keyValue($options,
			self::REPRESENTATION_STRICT, true);
		// PHP 8
		$isClassName = Container::keyValue($options,
			self::REPRESENTATION_IS_CLASS_NAME, false);
		if (\is_a($variable, \stringable::class, $isClassName))
			return true;

		if (!$strict)
		{
			if ($variable instanceof \DateTimeInterface) // format ()
				return true;
			if ($variable instanceof \DateTimeZone)
				return true;
			elseif ($variable instanceof \Serializable) // szerialize()
				return true;
			elseif ($variable instanceof \JsonSerializable) // encode (jsonSerialize)
				return true;
		}

		if (\is_object($variable) || $isClassName)
			return \method_exists($variable, '__toString');

		return (\is_string($variable) || \is_integer($variable) ||
			\is_float($variable) || \is_bool($variable) ||
			\is_null($variable));
	}

	/**
	 *
	 * @param mixed $variable
	 *        	Object or primitive
	 * @param array $options
	 *        	Options
	 * @return boolean
	 */
	public static function hasArrayRepresentation($variable,
		$options = array())
	{
		if (\is_bool($options))
			$options = [
				self::REPRESENTATION_IS_CLASS_NAME => $options
			];
		$isClassName = Container::keyValue($options,
			self::REPRESENTATION_IS_CLASS_NAME, false);

		if (!$isClassName)
		{
			if ($variable instanceof ArrayRepresentation)
				return true;
			if (Container::isArray($variable))
				return true;
			if (Container::isTraversable($variable))
				return true;
		}

		if (!($isClassName || \is_object($variable)))
			return false;

		$methods = [
			'getArrayCopy',
			'getArrayValue',
			'toArray'
		];

		return self::hasTypeConversionMethods($variable, $methods,
			$options);
	}

	private static function hasStandardTypeConversionMethods($variable,
		$typeName, $options = [])
	{
		return self::hasTypeConversionMethods($variable,
			[
				'get' . $typeName . 'Value',
				'to' . $typeName
			]);
	}

	private static function hasTypeConversionMethods($variable, $methods,
		$options = [])
	{
		$isClassName = Container::keyValue($options,
			self::REPRESENTATION_IS_CLASS_NAME, false);
		if (!($isClassName || \is_object($variable)))
			return false;
		foreach ($methods as $method)
		{
			if (\method_exists($variable, $method))
				return true;
		}
		return false;
	}
}