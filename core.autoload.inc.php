<?php
function autoload_NTUwNWIwZjhmMmZlYw($className)
{
	if ($className == 'NoreSources\Reporter')
	{
		require_once(__DIR__ . '/Reporter.php');
	}
 	elseif ($className == 'NoreSources\DummyReporterInterface')
	{
		require_once(__DIR__ . '/Reporter.php');
	}
 	elseif ($className == 'NoreSources\SettingTable')
	{
		require_once(__DIR__ . '/SettingTable.php');
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
 }
spl_autoload_register('autoload_NTUwNWIwZjhmMmZlYw');
?>
