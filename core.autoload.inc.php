<?php
function autoload_NTY3N2ZiNTk0NjA4NQ($className)
{
	if ($className == 'NoreSources\AccessTokenManagerInterface')
	{
		require_once(__DIR__ . '/AccessToken.php');
	}
 	elseif ($className == 'NoreSources\FileAccessTokenManager')
	{
		require_once(__DIR__ . '/AccessToken.php');
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
 	elseif ($className == 'NoreSources\SettingTable')
	{
		require_once(__DIR__ . '/SettingTable.php');
	}
 	elseif ($className == 'NoreSources\TokenVisitor')
	{
		require_once(__DIR__ . '/tokens.php');
	}
 	elseif ($className == 'NoreSources\SourceFile')
	{
		require_once(__DIR__ . '/tokens.php');
	}
 }
spl_autoload_register('autoload_NTY3N2ZiNTk0NjA4NQ');
