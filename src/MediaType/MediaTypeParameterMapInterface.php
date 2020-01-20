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

use NoreSources\ArrayRepresentation;

/**
 * MediaType and MediaRange parameter map
 *
 * According to RFC 7231 section 3.1.1.1, parameter names are case insensitive.
 *
 * For historical reasons, "q" should not be used a a parameter name to avoid ambiguity in the
 * Accept HTTP header field value.
 *
 * Implementation nodes
 * <ul>
 * <li>Parameter name matching MUST be case-insensitive.</li>
 * </ul>
 *
 * @see https://tools.ietf.org/html/rfc7231#section-3.1.1.1
 * @see https://tools.ietf.org/html/rfc4288#section-4.3
 */
interface MediaTypeParameterMapInterface extends \ArrayAccess, \Countable, \IteratorAggregate,
	ArrayRepresentation
{

	/**
	 *
	 * @param string $name
	 *        	Parameter name
	 * @param mixed $dflt
	 *        	Default value returned if the parameter does not exists in the map.
	 *
	 * @return string
	 */
	function getParameterValue($name, $dflt = null);
}