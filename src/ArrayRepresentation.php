<?php
namespace NoreSources;


/**
 * Object can be converted to array
 */
interface ArrayRepresentation
{

	/**
	 *
	 * @return array Array representation of the class instance
	 */
	function getArrayCopy();
}
