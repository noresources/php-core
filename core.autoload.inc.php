<?php
function autoload_NWQxYmE2Y2ZiZjI3Nw($className)
{
	if ($className == 'NoreSources\InvalidContainerException')
	{
		require_once(__DIR__ . '/ContainerUtil.php');
	}
 	elseif ($className == 'NoreSources\ContainerUtil')
	{
		require_once(__DIR__ . '/ContainerUtil.php');
	}
 	elseif ($className == 'NoreSources\ContainerUtil')
	{
		require_once(__DIR__ . '/ContainerUtil.php');
	}
 	elseif ($className == 'NoreSources\DataTree')
	{
		require_once(__DIR__ . '/DataTree.php');
	}
 	elseif ($className == 'NoreSources\PathUtil')
	{
		require_once(__DIR__ . '/PathUtil.php');
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
 	elseif ($className == 'NoreSources\SemanticPostfixedData')
	{
		require_once(__DIR__ . '/SemanticVersion.php');
	}
 	elseif ($className == 'NoreSources\SemanticVersion')
	{
		require_once(__DIR__ . '/SemanticVersion.php');
	}
 	elseif ($className == 'NoreSources\SourceToken')
	{
		require_once(__DIR__ . '/SourceFile.php');
	}
 	elseif ($className == 'NoreSources\TokenVisitor')
	{
		require_once(__DIR__ . '/SourceFile.php');
	}
 	elseif ($className == 'NoreSources\SourceFile')
	{
		require_once(__DIR__ . '/SourceFile.php');
	}
 	elseif ($className == 'NoreSources\TypeDescription')
	{
		require_once(__DIR__ . '/TypeDescription.php');
	}
 	elseif ($className == 'NoreSources\UrlUtil')
	{
		require_once(__DIR__ . '/UrlUtil.php');
	}
 }
spl_autoload_register('autoload_NWQxYmE2Y2ZiZjI3Nw');
