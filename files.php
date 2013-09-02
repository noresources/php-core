<?php

/**
 * Copyright © 2012 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

/**
 *
 *
 *
 * http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
 *
 * @param string $from
 *        	Absolute directory path
 * @param string $to
 *        	Absolute directory path
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
		if ($dir === $to [$depth])
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
				$relPath [0] = './' . $relPath [0];
			}
		}
	}
	
	return implode('/', $relPath);
}

?>
