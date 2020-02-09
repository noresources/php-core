<?php
spl_autoload_register(function($className) {
	$className = strtolower ($className);
	$classMap = array (
		'noresources\arrayutil' => 'ArrayUtil.php',
		'noresources\datatree' => 'DataTree.php',
		'noresources\iexpression' => 'Expressions.php',
		'noresources\podexpression' => 'Expressions.php',
		'noresources\textexpression' => 'Expressions.php',
		'noresources\parameterlistexpression' => 'Expressions.php',
		'noresources\surroundingelementexpression' => 'MathExpressions.php',
		'noresources\ioperatorexpression' => 'MathExpressions.php',
		'noresources\unaryoperatorexpression' => 'MathExpressions.php',
		'noresources\binaryoperatorexpression' => 'MathExpressions.php',
		'noresources\equalexpression' => 'MathExpressions.php',
		'noresources\pathutil' => 'PathUtil.php',
		'noresources\reporterinterface' => 'Reporter.inc.php',
		'noresources\reporter' => 'Reporter.inc.php',
		'noresources\dummyreporterinterface' => 'Reporter.inc.php',
		'noresources\tokenvisitor' => 'SourceFile.php',
		'noresources\sourcefile' => 'SourceFile.php',
		'noresources\typeutil' => 'TypeUtil.php',
		'noresources\urlutil' => 'UrlUtil.php'
	); // classMap

	if (\array_key_exists ($className, $classMap)) {
		require_once(__DIR__ . '/' . $classMap[$className]);
	}
});