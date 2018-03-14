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

if (!defined('NS_PHP_PATH'))
{
	/**
	 *
	 * @var string Path of the root directory of all NoreSources modules
	 */
	define('NS_PHP_PATH', realpath(__DIR__ . '/..'));
}

if (!defined('NS_PHP_CORE_PATH'))
{
	/**
	 *
	 * @var string Path of the NoreSources core source tree
	 */
	define('NS_PHP_CORE_PATH', realpath(__DIR__));
}

/**
 * NoreSources core major version number
 * @var integer [0-99]
 */
const VERSION_MAJOR = 0;

/**
 * NoreSources core minor version number
 * @var integer [0-99]
 */
const VERSION_MINOR = 2;

/**
 * NoreSources core patch version number
 * @var integer [0-99]
 */
const VERSION_PATCH = 0;

/**
 * Version string of NoreSources core module.
 * The version string can be used with the PHP function version_compare()
 *
 * @return NoreSources core module version
 */
function version_string()
{
	return (VERSION_MAJOR . '.' . VERSION_MINOR . '.' . VERSION_PATCH);
}

/**
 * Version number of the NoreSources core module
 * @return integer
 */
function version_number()
{
	return (VERSION_MAJOR * 10000 + VERSION_MINOR * 100 + VERSION_PATCH);
}

include_once (NS_PHP_CORE_PATH . '/core.autoload.inc.php');

/**
 * Indicates if the current script is run through command line mode or not
 * @return @c true if the current script is run through command line mode
 */
function is_cli()
{
	if (PHP_SAPI == 'cli')
	{
		return true;
	}
	
	if (preg_match('/apache/i', PHP_SAPI) || array_key_exists('SERVER_ADDR', $_SERVER))
	{
		return false;
	}
	
	if (array_key_exists('PWD', $_SERVER) || array_key_exists('TERM', $_SERVER))
	{
		return true;
	}
}