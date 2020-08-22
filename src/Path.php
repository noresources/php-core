<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

/**
 * File system path utility class
 */
class Path
{

	/**
	 *
	 * @param string $path
	 * @return string
	 */
	public static function cleanup($path)
	{
		$path = str_replace('\\', '/', $path);
		$path = preg_replace(chr(1) . '/[^/]+/\.\.(/|$)' . chr(1), '\1', $path);
		$path = preg_replace(chr(1) . '/\.(/|$)' . chr(1), '\1', $path);
		return $path;
	}

	/**
	 * Indicates if the given path is an absolute path
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function isAbsolute($path)
	{
		// UNIX path
		if (strpos($path, '/') === 0)
			return true;

		// wrappers (URI)

		if (preg_match(chr(1) . '[[:alpha:]]+://' . chr(1), $path))
			return true;

		// Windows drive
		if (preg_match(chr(1) . '^[a-zA-Z]:((/|\\\)|$)' . chr(1), $path))
			return true;

		return false;
	}

	/**
	 * Get the relative path from a path to another
	 *
	 * @param string $from
	 *        	Absolute directory path
	 * @param string $to
	 *        	Absolute directory path
	 *		@relurn Relative path from @param $from to @param $to
	 */
	public static function getRelative($from, $to)
	{
		$from = trim(self::cleanup($from), '/');
		$to = trim(self::cleanup($to), '/');

		$from = explode('/', $from);
		$to = explode('/', $to);
		$fromCount = count($from);
		$toCount = count($to);
		$min = ($fromCount < $toCount) ? $fromCount : $toCount;
		$commonPartsCount = 0;
		$result = [];
		while (($commonPartsCount < $min) && ($from[$commonPartsCount] == $to[$commonPartsCount]))
		{
			$commonPartsCount++;
		}

		for ($i = $commonPartsCount; $i < $fromCount; $i++)
		{
			$result[] = '..';
		}

		for ($i = $commonPartsCount; $i < $toCount; $i++)
		{
			$result[] = $to[$i];
		}

		if (count($result) == 0)
		{
			return '.';
		}

		return implode('/', $result);
	}
}
