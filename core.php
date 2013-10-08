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

use \InvalidArgumentException;

if (!defined('NS_PHP_PATH'))
{
	define('NS_PHP_PATH', realpath(__DIR__ . '/..'));
}

if (!defined('NS_PHP_CORE_PATH'))
{
	define('NS_PHP_CORE_PATH', realpath(__DIR__));
}
const VERSION_MAJOR = 0;
const VERSION_MINOR = 1;
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

function version_number()
{
	return (VERSION_MAJOR * 10000 + VERSION_MINOR * 100 + VERSION_PATCH);
}

/**
 * Load a ns-php module file
 *
 * @param $resourceName Source
 *        	file short name.
 *        	$resourceName is the module file path without file extension
 *        	and relative to the path defined by NS_PHP_PATH
 *        	
 * @param boolean $silent
 *        	If <code>true</code>, no exception will be raised if the resource can't be found
 *        	
 *        	If the resourceName was loaded and a file named
 *        	NS_PHP_PATH . '/' . @param $resourceName . '.inc.php' exists, then
 *        	this file will be included (<code>include_once</code>)
 *        	
 * @example load('core/strings') will call require_once (<path to ns-php root>/core/strings.php)
 *         
 * @return <code>true</code> if the file can be loaded.
 *         <code>false</code> If the file can't be loaded and @param $silent is set to <code>true</code>.
 *         If @param $silent is <code>false</code> and the file can't be loaded, a InvalidArgumentException exception is raised.
 */
function load($resourceName, $silent = false)
{
	$fileName = NS_PHP_PATH . '/' . $resourceName . '.php';
	$exists = file_exists($fileName);
	
	if ($exists)
	{
		require_once ($fileName);
	}
	else if (!$silent)
	{
		throw new InvalidArgumentException($resourceName . ' not found');
	}
	
	$fileName = NS_PHP_PATH . '/' . $resourceName . '.inc.php';
	if (file_exists($fileName))
	{
		include_once ($fileName);
	}
	
	return $exists;
}

?>