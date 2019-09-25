<?php
spl_autoload_register(function($className) {
	$className = strtolower ($className);
	$classMap = array (
		'noresources\datetime' => 'src/DateTime.php',
		'noresources\typedescription' => 'src/TypeDescription.php',
		'noresources\sourcetoken' => 'src/SourceFile.php',
		'noresources\tokenvisitor' => 'src/SourceFile.php',
		'noresources\sourcefile' => 'src/SourceFile.php',
		'noresources\datatree' => 'src/DataTree.php',
		'noresources\semanticpostfixeddata' => 'src/SemanticVersion.php',
		'noresources\semanticversion' => 'src/SemanticVersion.php',
		'noresources\urlutil' => 'src/UrlUtil.php',
		'noresources\invalidcontainerexception' => 'src/Container.php',
		'noresources\container' => 'src/Container.php',
		'noresources\pathutil' => 'src/PathUtil.php'
	); // classMap

	if (\array_key_exists ($className, $classMap)) {
		require_once(__DIR__ . '/' . $classMap[$className]);
	}
});