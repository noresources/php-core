<?php
function autoload_NWM2MDUyZTNiODE3MQ($className)
{
	if ($className == 'NoreSources\ArrayUtil')
	{
		require_once(__DIR__ . '/ArrayUtil.php');
	}
 	elseif ($className == 'NoreSources\DataTree')
	{
		require_once(__DIR__ . '/DataTree.php');
	}
 	elseif ($className == 'NoreSources\TokenVisitor')
	{
		require_once(__DIR__ . '/SourceFile.php');
	}
 	elseif ($className == 'NoreSources\SourceFile')
	{
		require_once(__DIR__ . '/SourceFile.php');
	}
 	elseif ($className == 'NoreSources\SurroundingElementExpression')
	{
		require_once(__DIR__ . '/MathExpressions.php');
	}
 	elseif ($className == 'NoreSources\IOperatorExpression')
	{
		require_once(__DIR__ . '/MathExpressions.php');
	}
 	elseif ($className == 'NoreSources\UnaryOperatorExpression')
	{
		require_once(__DIR__ . '/MathExpressions.php');
	}
 	elseif ($className == 'NoreSources\BinaryOperatorExpression')
	{
		require_once(__DIR__ . '/MathExpressions.php');
	}
 	elseif ($className == 'NoreSources\EqualExpression')
	{
		require_once(__DIR__ . '/MathExpressions.php');
	}
 	elseif ($className == 'NoreSources\PathUtil')
	{
		require_once(__DIR__ . '/PathUtil.php');
	}
 	elseif ($className == 'NoreSources\UrlUtil')
	{
		require_once(__DIR__ . '/UrlUtil.php');
	}
 	elseif ($className == 'NoreSources\IExpression')
	{
		require_once(__DIR__ . '/Expressions.php');
	}
 	elseif ($className == 'NoreSources\PODExpression')
	{
		require_once(__DIR__ . '/Expressions.php');
	}
 	elseif ($className == 'NoreSources\TextExpression')
	{
		require_once(__DIR__ . '/Expressions.php');
	}
 	elseif ($className == 'NoreSources\ParameterListExpression')
	{
		require_once(__DIR__ . '/Expressions.php');
	}
 	elseif ($className == 'NoreSources\ReporterInterface')
	{
		include_once(__DIR__ . '/Reporter.inc.php');
	}
 	elseif ($className == 'NoreSources\Reporter')
	{
		include_once(__DIR__ . '/Reporter.inc.php');
	}
 	elseif ($className == 'NoreSources\DummyReporterInterface')
	{
		include_once(__DIR__ . '/Reporter.inc.php');
	}
 }
spl_autoload_register('autoload_NWM2MDUyZTNiODE3MQ');
