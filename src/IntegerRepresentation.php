<?php
namespace NoreSources;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Boolean;

/*
 * Object have a integer representation
 */
interface IntegerRepresentation
{

	/**
	 *
	 * @return integer Integer representation of the class instance
	 */
	function getIntegerValue();
}
