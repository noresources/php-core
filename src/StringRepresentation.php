<?php
namespace NoreSources;

/**
 * Object have a string representation.
 * This interface is a syntaxic sugar to indicates the object redefines the __toString() magic method
 */
interface StringRepresentation
{

	/**
	 *
	 * @return string The string representation of the class instance
	 */
	function __toString();
}
