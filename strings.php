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
 * @param unknown $text Text to print
 * @param string $mode End of line mode (null -> auto, "html" -> <br>, "xhtml" -> <br />, "cli" -> LF)
 * @param string $returnText If @code true, return the text rather than displaying it
 * @return string if @param $returnText is @code true
 */
function echo_line($text, $mode = "auto", $returnText = false)
{
	$t = "";
	if ($mode == "auto")
	{
		$mode = (PHP_SAPI == "cli") ? "cli" : "xhtml";
	}

	$t = $text;

	if ($mode == "cli")
	{
		$t .= "\n";
	}
	elseif ($mode == "xhtml")
	{
		$t .= "<br />";
	}
	elseif ($mode == "html")
	{
		$t .= "<br>";
	}

	if ($returnText)
	{
		return ($t);
	}

	echo ($t);
}

?>
