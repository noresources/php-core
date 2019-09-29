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

class URI
{

	/**
	 * Get server host name
	 *
	 * @return string, null Server hostname or IP address or @c null if none can be found
	 */
	public static function getHost()
	{
		return \array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : (\array_key_exists(
			'SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : (\array_key_exists('SERVER_ADDR',
			$_SERVER) ? $_SERVER['SERVER_ADDR'] : null));
	}

	/**
	 * Get URL scheme from current server protocol
	 *
	 * @return string 'http' or 'https'
	 */
	public static function getScheme()
	{
		if (\array_key_exists('SERVER_PROTOCOL', $_SERVER))
		{
			return strtolower(
				preg_replace(chr(1) . '([A-Za-z]+)/.*' . chr(1), '$1', $_SERVER['SERVER_PROTOCOL']));
		}

		return 'file';
	}

	/**
	 * Cleanup URL
	 * - Remove unecessary /../ etc.
	 *
	 * @param string $url
	 * @return string Cleaned URL
	 */
	public static function cleanup($url)
	{
		$url = preg_replace(chr(1) . '/[^/]+/\.\.(/|$)' . chr(1), '\1', $url);
		$url = preg_replace(chr(1) . '/\.(/|$)' . chr(1), '\1', $url);
		return $url;
	}
}

