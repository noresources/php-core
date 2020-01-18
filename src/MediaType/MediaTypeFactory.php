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

class MediaTypeFactory
{

	/**
	 * Parse a media type string
	 *
	 * @param string $mediaTypeString
	 *        	Medig type string
	 * @param boolean $acceptRange
	 *        	Accept Media ranges
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaType \NoreSources\MediaType\MediaRange
	 */
	public static function fromString($mediaTypeString, $acceptRange = true)
	{
		try
		{
			return MediaType::fromString($mediaTypeString);
		}
		catch (MediaTypeException $e)
		{
			if (!$acceptRange)
				throw $e;
			return MediaRange::fromString($mediaTypeString);
		}
	}

	/**
	 * Get media type of a file or stream
	 *
	 * @param string|resource $media
	 *        	File path or stream
	 * @return \NoreSources\MediaType\MediaType
	 */
	public static function fromMedia($media)
	{
		$type = @mime_content_type($media);

		if ($type === false)
			throw \Exception('Unable to recognize media type');

		elseif ($type == 'text/plain')
		{
			if (\is_file($media))
			{
				$x = self::mediaTypeStringFroomFileExtension(pathinfo($media, PATHINFO_EXTENSION));
				if ($x !== false)
					$type = $x;
			}
		}

		return MediaType::fromString($type);
	}

	/**
	 * Get Media type string from file extension
	 *
	 * @param string $extension
	 *        	File extension
	 * @return string if extension is recognized, @c false otherwise
	 */
	public static function mediaTypeStringFroomFileExtension($extension)
	{
		if (!\is_array(self::$extensions))
			self::$extensions = [
				// Text files
				'css' => 'text/css',
				'html' => 'text/html',
				'htm' => 'text/html',
				'js' => 'text/javascript',
				'json' => 'application/json',
				'xml' => 'text/xml',
				'yaml' => 'text/yaml',

				// Fonts
				'aat' => 'text/sfnt',
				'cff' => 'text/sfnt',
				'otf' => 'font/otf',
				'sil' => 'text/sfnt',
				'ttf' => 'font/ttf'
			];

		$extension = strtolower($extension);
		if (\array_key_exists($extension, self::$extensions))
			return self::$extensions[$extension];

		return false;
	}

	/**
	 *
	 * @var array<string, string>
	 */
	private static $extensions;
}