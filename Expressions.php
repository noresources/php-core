<?php

/**
 * Copyright © 2012 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 * A set of very basic expression
 *
 * @package Core
 */
namespace NoreSources;

require_once (NS_PHP_PATH . "/core/arrays.php");

/**
 * Interface for an object which can be considered
 * as an expression
 */
interface IExpression
{

	/**
	 * Generate a string representing expression
	 *
	 * @param mixed $a_options
	 *        	Display options
	 */
	function expressionString($a_options = null);
}

/**
 * Plain old data
 */
class PODExpression implements IExpression
{

	public $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function __toString()
	{
		return $this->expressionString();
	}

	public function expressionString($a_options = null)
	{
		return "" . $this->value . "";
	}
}

/**
 * A function to use with array_keyvalue()
 *
 * @param $k key
 *        	(unused)
 * @param $v IExpression
 *        	assumed
 * @param $options options
 *        	provided to the IExpression::expressionString($a_options) method
 * @return string
 */
function expressions_glue_expressionString($k, $v, $options)
{
	return $v->expressionString($options);
}

/**
 * A simple string that implements IExpression
 */
class TextExpression implements IExpression
{

	public function __construct($a_strText)
	{
		$this->m_strText = $a_strText;
	}

	public function __toString()
	{
		return $this->expressionString();
	}

	public function expressionString($a_options = null)
	{
		return $this->m_strText;
	}

	protected $m_strText;
}

/**
 * A list of IExpression
 * Used to display a list of comma-separated values
 */
class ParameterListExpression implements IExpression
{

	public function __construct()
	{
		$this->m_parameters = array ();
	}

	public function __toString()
	{
		return $this->expressionString();
	}

	public function expressionString($a_options = null)
	{
		return array_implode_cb($this->m_parameters, ", ", __NAMESPACE__ . "\\expressions_glue_string", $a_options);
	}

	public function add(IExpression $a_expression)
	{
		$this->m_parameters [] = $a_expression;
	}

	public function remove($a_index)
	{
		$this->m_parameters = array_remove_key($a_index, $this->m_parameters);
	}

	protected $m_parameters;
}

/**
 * @param string $k Key (unused)
 * @param IExpression $v value
 * @param $options options provided to the IExpression::string($a_options) method
 * @return string
 */
function expressions_glue_string($k, $v, $options)
{
	return $v->expressionString($options);
}

?>