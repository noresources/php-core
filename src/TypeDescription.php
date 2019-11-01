<?php
namespace NoreSources;

class TypeDescription
{

	/**
	 *
	 * @param mixed $element
	 * @return string @c $element Full class name or type
	 */
	public static function getName($element)
	{
		if (\is_object($element))
		{
			return get_class($element);
		}
		return gettype($element);
	}

	/**
	 * Get the local class name
	 *
	 * @param object|string $element
	 */
	public static function getLocalName($element)
	{
		$element = (\is_object($element) ? self::getName($element) : $element);
		$p = \strrpos($element, '\\');
		if ($p === false)
			return $element;

		return substr($element, $p + 1);
	}

	/**
	 * Get class namespaces
	 *
	 * @param object $element
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public static function getNamespaces($element)
	{
		$name = $element;
		if (\is_object($element))
			$name = self::getName($element);
		elseif (!\is_string($element))
			throw new \InvalidArgumentException('string or class instance expected for argument #1');

		$namespaces = explode('\\', $name);
		\array_pop($namespaces);
		return $namespaces;
	}

	/**
	 *
	 * @param $mixed $object
	 *        	Class name or Class instance
	 * @param string $parent
	 *        	Class name
	 *
	 * @return boolean @true if @c $object is a subclass of @c $parent
	 */
	public static function isSubclassOf($object, $parent)
	{
		return \is_subclass_of($object, $parent, true);
	}

	/**
	 * Indicates if the given variable can be converted to string using TypeConversion utility
	 *
	 * @param mixed $element
	 *        	Any type
	 */
	public static function hasStringConversion($element)
	{
		return (\is_string($element) ||
			(\is_object($element) && \method_exists($element, '__toString')));
	}
}