<?php
spl_autoload_register(function($className) {
	$className = strtolower ($className);
	$classMap = array (
		'noresources\path' => 'src/Path.php',
		'noresources\stack' => 'src/Stack.php',
		'noresources\uri' => 'src/URI.php',
		'noresources\datetime' => 'src/DateTime.php',
		'noresources\typedescription' => 'src/TypeDescription.php',
		'noresources\sourcetoken' => 'src/SourceFile.php',
		'noresources\tokenvisitor' => 'src/SourceFile.php',
		'noresources\sourcefile' => 'src/SourceFile.php',
		'noresources\datatree' => 'src/DataTree.php',
		'noresources\semanticversionruleexception' => 'src/SemanticVersion.php',
		'noresources\semanticpostfixeddata' => 'src/SemanticVersion.php',
		'noresources\semanticversion' => 'src/SemanticVersion.php',
		'noresources\typeconversionexception' => 'src/TypeConversion.php',
		'noresources\integerrepresentation' => 'src/TypeConversion.php',
		'noresources\floatrepresentation' => 'src/TypeConversion.php',
		'noresources\booleanrepresentation' => 'src/TypeConversion.php',
		'noresources\arrayrepresentation' => 'src/TypeConversion.php',
		'noresources\stringrepresentation' => 'src/TypeConversion.php',
		'noresources\typeconversion' => 'src/TypeConversion.php',
		'noresources\mediatype' => 'src/MediaType.php',
		'noresources\invalidcontainerexception' => 'src/Container.php',
		'noresources\container' => 'src/Container.php'
	); // classMap

	if (\array_key_exists ($className, $classMap)) {
		require_once(__DIR__ . '/' . $classMap[$className]);
	}
});