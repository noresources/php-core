<?php

/**
 * Copyright © 2012-2018 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

class PathUtil
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
	 * Get the relative path from a path to another
	 * @param string $from Absolute directory path
	 * @param string $to Absolute directory path
	 *        @relurn Relative path from @param $from to @param $to
	 */
	public static function getRelative($from, $to)
	{
		$from = trim(path_cleanup($from), '/');
		$to = trim(path_cleanup($to), '/');
		
		$from = explode('/', $from);
		$to = explode('/', $to);
		$fromCount = count($from);
		$toCount = count($to);
		$min = ($fromCount < $toCount) ? $fromCount : $toCount;
		$commonPartsCount = 0;
		$result = array ();
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

function path_cleanup($path)
{
	PathUtil::cleanup($path);
}

function path_get_relative($from, $to)
{
	PathUtil::getRelative($from, $to);
}

/**
 *
 * @deprecated use path_get_relative()
 *            
 *             http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
 *            
 * @param string $from Absolute directory path
 * @param string $to Absolute directory path
 */
function file_travelpath($from, $to)
{
	// Convert Windows slashes
	$from = str_replace('\\', '/', $from);
	$to = str_replace('\\', '/', $to);
	
	// Make sure directories do not have trailing slashes
	$from = rtrim($from, '\/');
	$to = rtrim($to, '\/');
	
	$from = explode('/', $from);
	$to = explode('/', $to);
	$relPath = $to;
	
	foreach ($from as $depth => $dir)
	{
		// find first non-matching dir
		if ($dir === $to[$depth])
		{
			// ignore this directory
			array_shift($relPath);
		}
		else
		{
			// get number of remaining dirs to $from
			$remaining = count($from) - $depth;
			if ($remaining > 1)
			{
				// add traversals up to first matching dir
				$padLength = (count($relPath) + $remaining - 1) * -1;
				$relPath = array_pad($relPath, $padLength, '..');
				break;
			}
			else
			{
				$relPath[0] = './' . $relPath[0];
			}
		}
	}
	
	return implode('/', $relPath);
}
