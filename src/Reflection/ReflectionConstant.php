<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use NoreSources\Type\StringRepresentation;

/**
 * Class or free constant information
 */
class ReflectionConstant implements StringRepresentation
{

	/**
	 *
	 * @param string $name
	 *        	Constant name
	 * @param mixed $value
	 *        	Constante value
	 * @param string $comment
	 *        	Constant documentation comment
	 */
	public function __construct($name, $value = null, $comment = '')
	{
		$this->name = $name;
		$this->value = $value;
		$this->comment = $comment;
	}

	/**
	 *
	 * @return String representation of the constant value
	 */
	#[\ReturnTypeWillChange]
	public function __toString()
	{
		return \strval($this->value);
	}

	/**
	 * Get constant name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get constant value
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @return string
	 */
	public function getDocComment()
	{
		return $this->comment;
	}

	/**
	 *
	 * @value $string
	 */
	private $name;

	/**
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 *
	 * @var string
	 */
	private $comment;
}
