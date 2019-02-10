<?php

/**
 * Copyright © 2018 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

class UrlUtil
{
	/**
	 * Get server host name
	 * @return string, null Server hostname or IP address or @c null if none can be found
	 */
	public static function getHost()
	{
		return \array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : (\array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : (\array_key_exists('SERVER_ADDR', $_SERVER) ? $_SERVER['SERVER_ADDR'] : null));
	}

	/**
	 * Get URL scheme from current server protocol
	 * @return string 'http' or 'https'
	 */
	public static function getScheme()
	{
		if (\array_key_exists('SERVER_PROTOCOL', $_SERVER))
		{
			return strtolower(preg_replace(chr(1) . '([A-Za-z]+)/.*' . chr(1), '$1', $_SERVER['SERVER_PROTOCOL']));
		}
		
		return 'file';
	}

	/**
	 * Cleanup URL
	 * - Remove unecessary /../ etc.
	 * @param string $url
	 * @return string Cleaned URL
	 */
	public static function cleanup ($url)
	{
		$url = preg_replace(chr(1) . '/[^/]+/\.\.(/|$)' . chr(1), '\1', $url);
		$url = preg_replace(chr(1) . '/\.(/|$)' . chr(1), '\1', $url);
		return $url;
	}
}


function url_get_host()
{
	return UrlUtil::getHost();
}

function url_get_http_scheme()
{
	return UrlUtil::getScheme();
}


function url_cleanup($url)
{
	return UrlUtil::cleanup($url); 
}

/**
 * Get server document root URL
 * @return string or @c null
 * 
 * @deprecated Unreliable
 */
function url_get_root()
{
	if (is_cli())
	{
		return false;
	}
	
	$r = (url_get_http_scheme() . '://' . url_get_host());
	if (array_key_exists('CONTEXT_PREFIX', $_SERVER))
	{
		$r .= $_SERVER['CONTEXT_PREFIX'];
	}
	
	return $r;
}

/**
 * Get current requested URL without query parameters
 * @return string
 * 
 * @deprecated Unreliable
 */
function url_get_current()
{
	if (is_cli())
	{
		return 'file://' . realpath($_SERVER['SCRIPT_FILENAME']);
	}
	
	$scheme = url_get_http_scheme();
	$host = url_get_host();
	
	if (!is_string($host))
	{
		return null;
	}
	
	return $scheme . '://' . $host . '/' . preg_replace(chr(1) . '(.*?)\?.*' . chr(1), '$1', $_SERVER['REQUEST_URI']);
}

/**
 * Get current requested directory URL
 * @return string
 * 
 * @deprecated Unreliable
 */
function url_get_current_directory()
{
	if (is_cli())
	{
		return 'file://' . dirname(realpath($_SERVER['SCRIPT_FILENAME']));
	}
	
	$url = url_get_http_scheme() . '://' . url_get_host() . preg_replace(chr(1) . '(.*)/.*' . chr(1), '$1', $_SERVER['REQUEST_URI']);
	$len = strlen($url);
	if ($url[$len - 1] == '/')
	{
		$url = substr($url, 0, $len - 1);
	}
	
	return $url;
}

/**
 * Get URL of a path relative to the current script url
 *
 * @param string $path Path to an existing file or folder inside server document root
 * @param string $documentRoot The server document root. If not set, use PHP DOCUMENT_ROOT
 * @return string Url of @param $path relative tu url of the current script or @c false if
 * @param $path is not inside document root tree
 * 
 * @deprecated Use \NoreSources\PathUtil\getRelative
 */
function url_get_relative($path, $documentRoot = null)
{
	$path = realpath($path);
	if (!is_string($documentRoot))
	{
		if (is_cli())
		{
			throw new \InvalidArgumentException('Document root must be specified in CLI mode');
		}
		else
		{
			$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
		}
	}
	
	if (substr($path, 0, strlen($documentRoot)) != $documentRoot)
	{
		return Reporter::error(__METHOD__, 'Path is not part of server document root tree', __FILE__, __LINE__);
	}
	
	$currentFilePath = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
	$sourceParts = explode('/', $currentFilePath);
	
	$sourcePartCount = count($sourceParts);
	$targetParts = explode('/', $path);
	$targetPartCount = count($targetParts);
	
	$min = ($sourcePartCount < $targetPartCount) ? $sourcePartCount : $targetPartCount;
	
	$index = 0;
	
	while (($index < $min) && ($sourceParts[$index] == $targetParts[$index]))
	{
		$index++;
	}
	
	$result = '';
	
	for ($i = $index; $i < $sourcePartCount; $i++)
	{
		if (strlen($result))
		{
			$result .= '/';
		}
		
		$result .= '..';
	}
	
	for ($i = $index; $i < $targetPartCount; $i++)
	{
		if (strlen($result))
		{
			$result .= '/';
		}
		
		$result .= $targetParts[$i];
	}
	
	if (strlen($result) == 0)
	{
		$result = '.';
	}
	
	return $result;
}

/**
 * Get absolute url of the given file system path
 * @param string $path Path to an existing file of folder
 * @param string $documentRoot The server document root. If not set, use PHP DOCUMENT_ROOT
 * @return URL of the path or @c false if @param $path is not inside document root tree
 * 
 * @deprecated
 */
function url_get_absolute($path, $documentRoot = null)
{
	$current = url_get_current_directory();
	$relative = url_get_relative($path, $documentRoot);
	if ($relative === false)
	{
		return false;
	}
	return url_cleanup($current . '/' . $relative);
}