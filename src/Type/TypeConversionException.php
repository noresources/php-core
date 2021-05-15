<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Type;

/**
 * Type conversion exception
 */
class TypeConversionException extends \Exception
{

	/**
	 *
	 * @var mixed Value that cannot be converted
	 */
	public $value;

	/**
	 *
	 * @param mixed $value
	 *        	Value was not converted
	 * @param string $method
	 *        	Failing method name
	 * @param string $message
	 *        	Failure description
	 */
	public function __construct($value, $method, $message = null)
	{
		parent::__construct(
			'Failed to convert ' . TypeDescription::getName($value) .
			' to ' . preg_replace(',.*::to(.*),', '\1', $method) .
			($message ? (' : ' . $message) : ''));

		$this->value = $value;
	}
}

