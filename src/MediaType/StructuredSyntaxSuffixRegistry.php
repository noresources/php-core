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
 * List of
 *
 * @see https://www.iana.org/assignments/media-type-structured-suffix/media-type-structured-suffix.xhtml
 *
 */
class StructuredSyntaxSuffixRegistry
{

	/**
	 * Indicates if the given string is a registered structured syntax suffix
	 *
	 * @param string $suffix
	 *        	Structured syntax suffix. Optioaly prefixed with '+'
	 * @return boolean @c true if the given string is a registered structured syntax suffix
	 */
	public static function isRegistered($suffix)
	{
		self::initialize();
		if (\substr($suffix, 0, 1) == '+')
			$suffix = \substr($suffix, 1);

		return \array_key_exists($suffix, self::$suffixes);
	}

	/**
	 * Initialize the suffixes table
	 */
	private static function initialize()
	{
		if (!\is_array(self::$suffixes))
		{
			self::$suffixes = [ /*
			                      Auto-generated code --<<sufixes>>--*/'xml' => 'Extensible Markup Language (XML)'
, 'json' => 'JavaScript Object Notation (JSON)'
, 'ber' => 'Basic Encoding Rules (BER) message transfer syntax'
, 'cbor' => 'Concise Binary Object Representation (CBOR)'
, 'der' => 'Distinguished Encoding Rules (DER) message transfer syntax'
, 'fastinfoset' => 'Fast Infoset document format'
, 'wbxml' => 'WAP Binary XML (WBXML) document format'
, 'zip' => 'ZIP file storage and transfer format'
, 'tlv' => 'Type Length Value'
, 'json-seq' => 'JSON Text Sequence'
, 'sqlite3' => 'SQLite3 database'
, 'jwt' => 'JSON Web Token (JWT)'
, 'gzip' => 'gzip file storage and transfer format'
, 'cbor-seq' => 'CBOR Sequence'
/*--<</sufixes>>--
			 */
			];
		}
	}

	private static $suffixes;
}