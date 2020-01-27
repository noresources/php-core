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

/**
 * Media Type and Media Range factory
 *
 * Constructs Media Type and Media Range from various ways.
 */
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
		$type = null;
		if (\is_file($media) && \is_readable($media))
		{
			$finfo = new \finfo(FILEINFO_MIME_TYPE);
			$type = $finfo->file($media);
		}
		else
			$type = @mime_content_type($media);

		if ($type === false)
			throw \Exception('Unable to recognize media type');

		elseif ($type == 'text/plain')
		{
			if (\is_file($media))
			{
				$byExtension = MediaTypeFileExtensionRegistry::mediaTypeFromExtension(
					pathinfo($media, PATHINFO_EXTENSION));
				if ($byExtension instanceof MediaTypeInterface)
					return $byExtension;
			}
		}

		return MediaType::fromString($type);
	}
}