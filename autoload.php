<?php
spl_autoload_register(function($className) {
	if ($className == 'NoreSources\DateTime') {
		require_once(__DIR__ . '/DateTime.php');
	} elseif ($className == 'NoreSources\TypeDescription') {
		require_once(__DIR__ . '/TypeDescription.php');
	} elseif ($className == 'NoreSources\SourceToken') {
		require_once(__DIR__ . '/SourceFile.php');
	} elseif ($className == 'NoreSources\TokenVisitor') {
		require_once(__DIR__ . '/SourceFile.php');
	} elseif ($className == 'NoreSources\SourceFile') {
		require_once(__DIR__ . '/SourceFile.php');
	} elseif ($className == 'NoreSources\DataTree') {
		require_once(__DIR__ . '/DataTree.php');
	} elseif ($className == 'NoreSources\SemanticPostfixedData') {
		require_once(__DIR__ . '/SemanticVersion.php');
	} elseif ($className == 'NoreSources\SemanticVersion') {
		require_once(__DIR__ . '/SemanticVersion.php');
	} elseif ($className == 'NoreSources\UrlUtil') {
		require_once(__DIR__ . '/UrlUtil.php');
	} elseif ($className == 'NoreSources\ReporterInterface') {
		require_once(__DIR__ . '/Reporter.inc.php');
	} elseif ($className == 'NoreSources\Reporter') {
		require_once(__DIR__ . '/Reporter.inc.php');
	} elseif ($className == 'NoreSources\DummyReporterInterface') {
		require_once(__DIR__ . '/Reporter.inc.php');
	} elseif ($className == 'NoreSources\InvalidContainerException') {
		require_once(__DIR__ . '/Container.php');
	} elseif ($className == 'NoreSources\Container') {
		require_once(__DIR__ . '/Container.php');
	} elseif ($className == 'NoreSources\PathUtil') {
		require_once(__DIR__ . '/PathUtil.php');
	}
});