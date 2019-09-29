<?php
namespace NoreSources;

class TypeDescription
{

	public static function getName($element)
	{
		if (\is_object($element))
		{
			return get_class($element);
		}
		return gettype($element);
	}
}