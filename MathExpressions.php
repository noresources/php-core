<?php

/**
 * Copyright © 2012-2017 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

class SurroundingElementExpression implements IExpression
{

	public function __construct(&$a_expression, $a_cSurroundingStart = '(', $a_cSurroundingEnd = ')')
	{
		$this->m_end = $a_cSurroundingEnd;
		$this->m_expression = $a_expression;
		$this->m_start = $a_cSurroundingStart;
	}

	public function __toString()
	{
		return $this->expressionString();
	}

	public function __clone()
	{
		$this->m_expression = clone $this->m_expression;
	}

	function expressionString($a_options = null)
	{
		return $this->m_start . (($this->m_expression) ? $this->m_expression->expressionString($a_options) : '') . $this->m_end;
	}

	function expression(IExpression &$a_expression = null)
	{
		if (!is_null($a_expression))
		{
			$this->m_expression = $a_expression;
		}
		return $this->m_expression;
	}

	protected $m_start;

	protected $m_end;

	protected $m_expression;
}

/**
 * An expression containing a operator
 */
abstract class IOperatorExpression implements IExpression
{

	/**
	 *
	 * @param string $a_strOperator Operator string
	 */
	public function __construct($a_strOperator)
	{
		$this->m_strOperator = $a_strOperator;
		$this->protect(true);
	}

	public function __toString()
	{
		return $this->expressionString();
	}

	/**
	 * Indicates if the expression must be protected between parenthesis
	 *
	 * @param boolean|null $a_protect
	 * @return boolean
	 */
	function protect($a_protect = null)
	{
		if (!is_null($a_protect))
		{
			$this->m_bProtect = $a_protect;
		}
		return $this->m_bProtect;
	}

	protected function operator($op)
	{
		$this->m_strOperator = $op;
	}

	/**
	 * Operator
	 *
	 * @var string
	 */
	protected $m_strOperator;

	/**
	 *
	 * @var boolean
	 */
	protected $m_bProtect;
}

/**
 * Unary operator (pre or postfixed)
 */
class UnaryOperatorExpression extends IOperatorExpression
{

	/**
	 *
	 * @param string $a_strOperator
	 * @param IExpression $a_expression
	 * @param boolean $a_postFixed
	 */
	public function __construct($a_strOperator, IExpression &$a_expression = null, $a_postFixed = false)
	{
		parent::__construct($a_strOperator);
		$this->m_expression = $a_expression;
		$this->m_bPostFixed = $a_postFixed;
	}

	public function __clone()
	{
		$this->m_expression = clone $this->m_expression;
	}

	public function __toString()
	{
		return $this->expressionString();
	}

	function expressionString($a_options = null)
	{
		if (is_null($this->m_expression))
		{
			Reporter::fatalError($this, __METHOD__ . '(): invalid expression given');
		}
		
		if ($this->m_bPostFixed)
		{
			return ($this->protect() ? ' (' : ' ') 
					. $this->m_expression->expressionString($a_options) 
					. ($this->protect() ? ')' : '') 
					. $this->m_strOperator;
		}
		
		return $this->m_strOperator 
				. ($this->protect() ? ' (' : ' ') 
				. $this->m_expression->expressionString($a_options) 
				. ($this->protect() ? ')' : '');
	}

	function expression(IExpression &$a_expression = null)
	{
		if (!is_null($a_expression))
		{
			$this->m_expression = $a_expression;
		}
		return $this->m_expression;
	}

	protected $m_expression;

	protected $m_bPostFixed;
}

/**
 * Binary operator
 */
class BinaryOperatorExpression extends IOperatorExpression
{

	public function __construct($a_strOperator, IExpression $a_leftExpression = null, IExpression $a_rightExpression = null)
	{
		parent::__construct($a_strOperator);
		$this->m_leftExpression = $a_leftExpression;
		$this->m_rightExpression = $a_rightExpression;
	}

	public function __clone()
	{
		$this->m_leftExpression = clone $this->m_leftExpression;
		$this->m_rightExpression = clone $this->m_rightExpression;
	}

	public function __toString()
	{
		return $this->expressionString();
	}

	/**
	 *
	 * @param IExpression $a_expression        	
	 * @return IExpression
	 */
	function leftExpression(IExpression &$a_expression = null)
	{
		if (!is_null($a_expression))
		{
			$this->m_leftExpression = $a_expression;
		}
		return $this->m_leftExpression;
	}

	/**
	 *
	 * @param IExpression $a_expression        	
	 * @return IExpression
	 */
	function rightExpression(IExpression &$a_expression = null)
	{
		if (!is_null($a_expression))
		{
			$this->m_rightExpression = $a_expression;
		}
		return $this->m_rightExpression;
	}

	/**
	 *
	 * @param unknown_type $a_options        	
	 * @return string
	 */
	function expressionString($a_options = null)
	{
		if (is_null($this->m_leftExpression) || is_null($this->m_rightExpression))
		{
			Reporter::fatalError($this, __METHOD__ . '(): invalid expression given');
		}
		
		return ($this->protect() ? '(' : '') 
				. $this->m_leftExpression->expressionString($a_options) 
				. ($this->protect() ? ') ' : ' ') 
				. $this->m_strOperator 
				. ($this->protect() ? ' (' : ' ') 
				. $this->m_rightExpression->expressionString($a_options) 
				. ($this->protect() ? ')' : '');
	}

	protected $m_leftExpression;

	protected $m_rightExpression;
}

class EqualExpression extends BinaryOperatorExpression
{

	public function __construct(IExpression &$a_left, IExpression &$a_right)
	{
		parent::__construct('=', $a_left, $a_right);
	}
	
	public function __toString()
	{
		return $this->expressionString();
	}
}
