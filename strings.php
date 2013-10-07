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
 * Print a line of text
 *
 * @param unknown $text
 *        	Text to print
 * @param string $mode
 *        	End of line mode (null -> auto, 'html' -> <br>, 'xhtml' -> <br />, 'cli' -> LF)
 * @param string $returnText
 *        	If @code true, return the text rather than displaying it
 * @return string if @param $returnText is @code true
 */
function echo_line($text, $mode = "auto", $returnText = false)
{
	$t = '';
	if ($mode == 'auto')
	{
		$mode = (PHP_SAPI == 'cli') ? 'cli' : 'xhtml';
	}
	
	$t = $text;
	
	if ($mode == 'cli')
	{
		$t .= PHP_EOL;
	}
	elseif ($mode == 'xhtml')
	{
		$t .= '<br />';
	}
	elseif ($mode == 'html')
	{
		$t .= '<br>';
	}
	
	if ($returnText)
	{
		return ($t);
	}
	
	echo ($t);
}
const kLiteralListCaseSensitive = 0x01;
const kLiteralListBooleanFalse = 0x02;

/**
 * Convert a literal to a boolean value
 * 
 * @param mixed $string
 *        	String to convert
 * @param integer $flags
 *        	Option flags.
 *        	If @c kLiteralListBooleanFalse is not set (the default), @param $literalList will be considered as the list
 *        	of strings that represents <code>true</code>. The function will return <code>true</code> if @param $string is found in @param
 *        	$literalList.
 *        	If @c kLiteralListBooleanFalse is set, @param $literalList will be considered as the list
 *        	of strings that represents <code>false</code>. The function will return <code>false</code> if @param $string is found in @param
 *        	$literalList.
 * @param array $literalList
 *        	a list of string representing <code>true</code> or <code>false</code> values
 * @return boolean
 */
function literalboolval($string, $flags = 0, $literalList = array ("yes", "true", "1"))
{
	if (is_bool($string))
	{
		return $string;
	}
	elseif ($string === null)
	{
		return false;
	}
	elseif (is_numeric($string))
	{
		if (function_exists("boolval"))
		{
			return boolval($string);
		}
		
		return (intval($string) != 0);
	}
	
	if (!($flags & kLiteralListCaseSensitive))
	{
		$res = true;
		if ($flags & kLiteralListBooleanFalse)
		{
			$res = false;
		}

		foreach ($literalList as $l)
		{
			if (strcasecmp($string, $l) == 0)
			{
				return $res;
			}
		}
		
		return !$res;
	}
	
	// case sensitive 
	
	if ($flags & kLiteralListBooleanFalse)
	{
		return !\in_array($string, $literalList);
	}
	
	return \in_array($string, $literalList);
}

?>
