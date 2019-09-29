<?php
namespace NoreSources;

class TypeDescription
{

	/**
	 * @param mixed $element
	 * @return string @c $element class name or type
	 */
	public static function getName($element)
	{
		if (\is_object($element))
		{
			return get_class($element);
		}
		return gettype($element);
	}
}