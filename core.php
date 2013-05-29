<?php 
/**
 * Copyright © 2012 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 * @package NoreSources
 */
namespace NoreSources;

if (!defined("NS_PHP_PATH"))
{
	define("NS_PHP_PATH", realpath(dirname(__FILE__) . "/.."));
}

const VERSION_MAJOR = 0;
const VERSION_MINOR = 1;
const VERSION_PATCH = 0;

/**
 * Version string of NoreSources core module.
 * The version string can be used with the PHP function version_compare()
 * @return NoreSources core module version
 */
function version_string()
{
	return (VERSION_MAJOR . "." . VERSION_MINOR . "." . VERSION_PATCH);
}

function version_number()
{
	return (VERSION_MAJOR * 10000 + VERSION_MINOR * 100 + VERSION_PATCH);
}

/**
 * Load a ns-php module file.
 *  
 * @param $resourceName Source file short name. 
 * $resourceName is the module file path without file extension 
 * and relative to the one defined by NS_PHP_PATH
 * 
 * @example load("core/strings") will call require_once (<path to ns-php root>/core/strings.php)
 */
function load($resourceName)
{
	require_once (NS_PHP_PATH . "/" . $resourceName . ".php");
}

?>