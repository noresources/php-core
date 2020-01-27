<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\MediaType;

use NoreSources\Container;

/**
 * Associate Media Types and their commonly used file extensions.
 *
 * Base on Apache httpd public list
 *
 * @see https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
 *
 */
class MediaTypeFileExtensionRegistry
{

	/**
	 *
	 * @param string $extension
	 * @return MediaType|false The media type commonly associated with the given file extension or @c false
	 *         if the extension is not recognized.
	 */
	public static function mediaTypeFromExtension($extension)
	{
		if (!\is_array(self::$extensionMap))
		{
			self::$extensionMap = self::getData('extensions');
		}

		$mediaType = Container::keyValue(self::$extensionMap, $extension, false);
		if ($mediaType)
			return MediaType::fromString($mediaType, true);

		return $mediaType;
	}

	/**
	 * Get the extension(s) commonly used with the given Media Type
	 *
	 * @param MediaType $mediaType
	 * @return string[] List of extensions
	 */
	public static function getMediaTypeExtensions(MediaType $mediaType)
	{
		if (!\is_array(self::$typesMap))
			self::$typesMap = [];

		if (!Container::keyExists(self::$typesMap, $mediaType->getMainType()))
			self::$typesMap[$mediaType->getMainType()] = self::getData(
				'types.' . $mediaType->getMainType());

		return Container::keyValue(self::$typesMap[$mediaType->getMainType()], []);
	}

	private static function getData($suffix)
	{
		$filename = __DIR__ . '/' . basename(__FILE__, '.php') . '/' . $suffix . '.json';
		if (!\file_exists($filename))
			throw new \InvalidArgumentException($filename . ' not found');

		return \json_decode(\file_get_contents($filename), true);
	}

	private static $typesMap;

	private static $extensionMap;
}