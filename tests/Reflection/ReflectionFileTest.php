<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test\Reflection;

use NoreSources\ComparableInterface;
use NoreSources\SingletonTrait;
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionConstant;
use NoreSources\Reflection\ReflectionDocComment;
use NoreSources\Reflection\ReflectionFile;
use NoreSources\Reflection\ReflectionFile\PhpSourceTokenScope;
use NoreSources\Reflection\ReflectionFile\PhpSourceTokenVisitor;
use NoreSources\Type\TypeDescription;
use NoreSources\Type\TypeDescription as TD;
use Space\PSR4Class;
use NamespaceLessClass;
use ReflectionClass;

/**
 * Namespace scope constant with string value
 *
 * @var string A test string
 */
const REFLECTION_TEST_STRING = 'constant-value';

const REFLECTION_TEST_INTEGER = 42;

const REFLECTION_TEST_FLOAT = 6.55957;

const REFLECTION_TEST_BOOLEAN = true;

const REFLECTION_TEST_NULL = null;

const REFLECTION_TEST_ARRAY = [
	'file',
	'constant'
];

function reflectionTestFreeFunction()
{
	return "I'm a poor lonesome function";
}

final class ReflectionFileTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * A class constant documentation
	 *
	 * @var array It's a complex constant value
	 */
	const REFLECTION_CLASS_CONSTANT = [
		'constant',
		'in',
		'class'
	];

	use SingletonTrait;

	public function testToken()
	{
		$code = file_get_contents(__FILE__);
		$visitor = new PhpSourceTokenVisitor($code);

		$cls = new \ReflectionClass(static::class);
		$parentClass = $cls->getParentClass();

		$stats = [
			\token_name(T_OPEN_TAG) => 0,
			\token_name(T_NAMESPACE) => 0,
			\token_name(T_CLASS) => 0,
			\token_name(T_FUNCTION) => 0
		];

		$expected = [
			\token_name(T_OPEN_TAG) => 1,
			\token_name(T_NAMESPACE) => 1,
			\token_name(T_CLASS) => 1,
			\token_name(T_FUNCTION) => 1 + 9
		];

		$first = $visitor->current();
		$this->assertEquals(T_OPEN_TAG, $first->getTokenType(),
			'Source file open tag');

		$visitor->setScopeEventHandler(
			function ($e, PhpSourceTokenScope $scope,
				PhpSourceTokenVisitor $visitor) use (&$stats) {

				$s = str_repeat(' ', $scope->level) . $e;
				if ($scope->entityToken)
				{
					$type = $scope->entityToken->getTokenType();
					if ($e == PhpSourceTokenVisitor::EVENT_SCOPE_START)
						$stats[\token_name($type)]++;
					if ($scope->parentEntityToken)
						$s .= ' ' .
						\str_replace(PHP_EOL, '',
							\strval($scope->parentEntityToken)) . '::';
					else
						$s .= ' ';
					$s .= $scope->entityToken;
				}
				else
					$s .= ' block';
				// echo ('> ' . $s . PHP_EOL);
			});

		$visitor->traverse();
		$this->assertEquals($expected, $stats,
			'Number of entity in ' . \basename(__FILE__));
	}

	public function testReflectionFileFunctions()
	{
		$cls = new \ReflectionClass(static::class);
		$methods = $cls->getMethods();

		$file = new ReflectionFile(__FILE__,
			ReflectionFile::SAFE | ReflectionFile::LOADED |
			ReflectionFile::AUTOLOADABLE);

		$allFunctions = $file->getFunctions();
		$name = __NAMESPACE__ . '\\reflectionTestFreeFunction';
		$this->assertArrayHasKey($name, $allFunctions,
			'File has free function "reflectionTestFreeFunction"');

		$f = $file->getFunction($name);
		$this->assertInstanceOf(\ReflectionFunction::class, $f);

		$this->assertTrue(
			$file->hasFunction('reflectionTestFreeFunction'),
			'Has local function name');

		{
			$filename = \realpath(
				__DIR__ . '/../data/Root/Space/PSR4Class.php');
			require_once ($filename);
			$file = new ReflectionFile($filename,
				ReflectionFile::LOADED | ReflectionFile::AUTOLOADABLE |
				ReflectionFile::SAFE);

			$this->assertEquals($filename, $file->getFilename());

			$this->assertTrue($file->hasFunction('freeHello'),
				'Has freeHello function');

			$func = $file->getFunction('Space\freeHello');
			$this->assertInstanceOf(\ReflectionFunction::class, $func,
				'Get freeHello free function using qualifiedname');
		}
	}

	public function testReflectionFileConstant()
	{
		$cls = new \ReflectionClass(static::class);
		$this->assertTrue(
			$cls->hasConstant('REFLECTION_CLASS_CONSTANT'),
			'This class has REFLECTION_CLASS_CONSTANT constant');
		$constantValue = $cls->getConstant('REFLECTION_CLASS_CONSTANT');

		$this->assertEquals('array',
			TypeDescription::getName($constantValue),
			'REFLECTION_CLASS_CONSTANT value type');

		$file = new ReflectionFile(__FILE__,
			ReflectionFile::SAFE | ReflectionFile::LOADED |
			ReflectionFile::AUTOLOADABLE);
		$constants = $file->getConstants();

		$this->assertCount(6, $constants, 'Number of file constants');

		$fqn = __NAMESPACE__ . '\\REFLECTION_TEST_STRING';
		$this->assertArrayHasKey($fqn, $constants, 'File constant');
		$this->assertTrue($file->hasConstant($fqn), 'hasConstant');

		$this->assertEquals('constant-value', REFLECTION_TEST_STRING,
			'File constant value');
		$this->assertArrayHasKey(
			__NAMESPACE__ . '\\REFLECTION_TEST_INTEGER', $constants,
			'File constant');
		$this->assertEquals(42, REFLECTION_TEST_INTEGER,
			'File constant value');
		$this->assertArrayHasKey(
			__NAMESPACE__ . '\\REFLECTION_TEST_FLOAT', $constants,
			'File constant');
		$this->assertEquals(6.55957, REFLECTION_TEST_FLOAT,
			'File constant value');
		$this->assertArrayHasKey(
			__NAMESPACE__ . '\\REFLECTION_TEST_BOOLEAN', $constants,
			'File constant');
		$this->assertEquals(true, REFLECTION_TEST_BOOLEAN,
			'File constant value');
		$this->assertArrayHasKey(
			__NAMESPACE__ . '\\REFLECTION_TEST_NULL', $constants,
			'File constant');
		$this->assertEquals(null, REFLECTION_TEST_NULL,
			'File constant value');
		$this->assertArrayHasKey(
			__NAMESPACE__ . '\\REFLECTION_TEST_ARRAY', $constants,
			'File constant');
		$this->assertEquals([
			'file',
			'constant'
		], REFLECTION_TEST_ARRAY, 'File constant value');

		$filename = \realpath(
			__DIR__ . '/../data/Root/Space/PSR4Class.php');
		$file = new ReflectionFile($filename,
			ReflectionFile::AUTOLOADABLE | ReflectionFile::SAFE);

		$this->assertEquals([
			'Space\HELLO'
		], $file->getConstantNames(), 'Constant names');

		$cst = $file->getConstant('HELLO')->getValue();
		$this->assertEquals('hello world', $cst, 'Constant value');

		$ns = $file->getNamespaces();
		$this->assertCount(1, $ns, 'Number of namespaces');
		$this->assertEquals('Space', $ns[0], 'Namespace');
	}

	public function testReflectionFileClasses()
	{
		$itf = new \ReflectionClass(ComparableInterface::class);
		$this->assertInstanceOf(\ReflectionClass::class, $itf);

		$trt = new \ReflectionClass(SingletonTrait::class);
		$this->assertInstanceOf(\ReflectionClass::class, $trt);

		$file = new ReflectionFile(__FILE__,
			ReflectionFile::SAFE | ReflectionFile::LOADED |
			ReflectionFile::AUTOLOADABLE);

		$this->assertTrue($file->hasClass(ReflectionFileTest::class),
			'Has qualified class name');
		$this->assertTrue($file->hasClass('ReflectionFileTest'),
			'Has local class name');

		$cls = $file->getClass('ReflectionFileTest');
		$this->assertInstanceOf(\ReflectionClass::class, $cls,
			'Get ReflectionFileTest ReflectionClass');

		// ////////

		$filename = \realpath(
			__DIR__ . '/../data/Root/Space/PSR4Class.php');
		$file = new ReflectionFile($filename,
			ReflectionFile::AUTOLOADABLE | ReflectionFile::SAFE);

		$this->assertTrue($file->hasClass('PSR4Class'), 'Has PSR4Class');
		$this->assertCount(1, $file->getClasses(), 'Class count');
		$this->assertEquals([
			PSR4Class::class
		], $file->getClassNames(), 'Class names');

		$cls = $file->getClass('PSR4Class');
		$this->assertInstanceOf(ReflectionClass::class, $cls,
			'Get PSR4Class ReflectionClass from local name');
		$this->assertEquals(PSR4Class::class, $cls->getName(),
			'PSR4Class name');
		$this->assertCount(1, $file->getClasses(), 'A class is defined');

		foreach ([
			'Trait',
			'Interface'
		] as $type)
		{
			$name = 'PSR4' . $type;
			$filename = \realpath(
				__DIR__ . '/../data/Root/Space/' . $name . '.php');
			$file = new ReflectionFile($filename,
				ReflectionFile::AUTOLOADABLE | ReflectionFile::SAFE);
			$hasMethod = 'has' . $type;
			$getMethod = 'get' . $type;
			$getsMethod = 'get' . $type . 's';

			$this->assertCount(1, call_user_func([
				$file,
				$getsMethod
			]), 'Number of ' . $type . 's');

			$this->assertTrue(
				call_user_func([
					$file,
					$hasMethod
				], $name), 'Has ' . $name);
			$cls = call_user_func([
				$file,
				$getMethod
			], $name);
			$this->assertInstanceOf(\ReflectionClass::class, $cls,
				ReflectionClass::class . ' class');

			$qualifiedName = 'Space\\' . $name;
			$fullyQualifiedName = '\\' . $qualifiedName;

			$this->assertEquals($qualifiedName,
				$file->getQualifiedName($name),
				'Qualified name of ' . $name);
			$this->assertEquals($fullyQualifiedName,
				$file->getFullyQualifiedName($name),
				'Fully qualified name of ' . $name);
		}
	}

	public function testReflectionFileConstants()
	{
		$file = new ReflectionFile(__FILE__, ReflectionFile::SAFE);

		$fqn = __NAMESPACE__ . '\\REFLECTION_TEST_STRING';
		$cst = $file->getConstant($fqn);
		$this->assertInstanceOf(ReflectionConstant::class, $cst,
			'getConstant');
		$this->assertFalse(empty($cst->getDocComment()),
			$fqn . ' has doc comment: ' . $cst->getDocComment());
	}

	public function testReflectionFileStructureConstants()
	{
		$file = new ReflectionFile(__FILE__, ReflectionFile::SAFE);
		$this->assertTrue($file->hasStructure(self::class));

		$cst = $file->getStructureConstant(self::class,
			'REFLECTION_CLASS_CONSTANT');

		$this->assertInstanceOf(ReflectionConstant::class, $cst);
		$value = $cst->getValue();

		$this->assertEquals('array', gettype($value),
			'Constant value type');
		$this->assertFalse(empty($cst->getDocComment()),
			'Constant has comment');
	}

	public function testReflectionFile()
	{
		$file = new ReflectionFile(__FILE__,
			ReflectionFile::SAFE | ReflectionFile::LOADED |
			ReflectionFile::AUTOLOADABLE);

		$u = $file->getUseStatements();
		$this->assertArrayHasKey(TD::class, $u,
			'use statement without alias');
		$this->assertEquals('TD', $u[TD::class],
			'use statement with user-defined alias');

		$classes = $file->getClassnames();
		$this->assertEquals([
			ReflectionFileTest::class
		], $classes, 'List of classes');

		$ns = $file->getNamespaces();
		$this->assertCount(1, $ns,
			\basename(__FILE__) . ' namespace count');
		$this->assertEquals([
			__NAMESPACE__
		], $ns, \basename(__FILE__) . ' namespace');

		$this->assertEquals(ReflectionFile::class,
			$file->getQualifiedName('ReflectionFile'),
			'Qualified name using "use" statement');

		$this->assertEquals(ReflectionFileTest::class,
			$file->getQualifiedName('ReflectionFileTest'),
			'Qualified name using file namespace');

		$file = new ReflectionFile(
			\realpath(__DIR__ . '/../data/MultiNamespace.php'),
			ReflectionFile::SAFE);

		$this->assertTrue($file->hasInterface('AggressiveInterface'),
			'Has AggressiveInterface (local name)');

		$this->assertTrue(
			$file->hasInterface('Food\Fish\AggressiveInterface'),
			'Has Food\Fish\AggressiveInterface (qualifiede name)');

		$interfaces = $file->getInterfaceNames();
		$expectedInterfaceNames = [
			'Food\Fruit\Fallable',
			'Food\Fish\AggressiveInterface'
		];
		$this->assertEquals($expectedInterfaceNames, $interfaces,
			'Interface names');

		$classes = $file->getClassnames();
		$expectedClassNames = [
			'Food\Fruit\Apple',
			'Food\Fruit\Pear',
			'Food\Fish\Shark',
			'Food\Fish\Cat',
			'Food\Fish\Babel'
		];
		$this->assertEquals($expectedClassNames, $classes,
			'Multi namespace file class list');

		$this->assertTrue($file->hasTrait('AggressiveTrait'),
			'Has AggressiveTrait');
		$traits = $file->getTraitNames();
		$expectedTraitNames = [
			'Food\Fish\AggressiveTrait'
		];
		$this->assertEquals($expectedTraitNames, $traits, 'Trait names');

		$expectedStructures = \array_merge($expectedInterfaceNames,
			$expectedTraitNames, $expectedClassNames);

		$this->assertEquals($expectedStructures,
			$file->getStructureNames(), 'All structure names');

		$this->assertEquals($expectedStructures,
			Container::keys($file->getStructures()),
			'All structure names');

		foreach ($expectedStructures as $name)
		{
			$localName = TypeDescription::getLocalName($name, true);
			$this->assertTrue($file->hasStructure($name), 'Has ' . $name);
			$this->assertTrue($file->hasStructure($localName),
				'Has ' . $localName . ' (local name)');
		}

		$expectedNamespaces = [
			'Food\\Fruit',
			'Food\\Fish'
		];
		$this->assertEquals($expectedNamespaces, $file->getNamespaces(),
			'Multiple namespaces in a single file');

		$this->assertTrue($file->hasNamespace('Food\\Fish'),
			'Has Food\\Fish');

		$this->assertEquals('Food\Fruit\Pear',
			$file->getQualifiedName('Pear'),
			'Qualified class name in a multi-namespace file');

		$this->assertEquals('Food\Fish\AggressiveTrait',
			$file->getQualifiedName('AggressiveTrait'),
			'Qualified trait name in a multi-namespace file');

		$this->assertEquals('\Food\Fish\AggressiveTrait',
			$file->getFullyQualifiedName('AggressiveTrait'),
			'Fully qualified trait name in a multi-namespace file');

		$this->assertTrue(\class_exists(NamespaceLessClass::class),
			'Ensure ' . NamespaceLessClass::class . ' is autoloadable');

		$namespaceLessClass = new \ReflectionClass(
			NamespaceLessClass::class);

		$namespaceLessClassFilename = $namespaceLessClass->getFileName();
		$this->assertEquals(
			\realpath(__DIR__ . '/../data/Root/NamespaceLessClass.php'),
			$namespaceLessClassFilename);

		$file = new ReflectionFile($namespaceLessClassFilename,
			ReflectionFile::SAFE | ReflectionFile::AUTOLOADABLE);

		$this->assertEquals(\NamespaceLessClass::class,
			$file->getQualifiedName('NamespaceLessClass'),
			'Qualified class name in a file without namespace');

		$namespaceLessClassFromFile = $file->getClass(
			'NamespaceLessClass');
		$this->assertInstanceOf(\ReflectionClass::class,
			$namespaceLessClassFromFile, 'Get ReflectionClass');
	}

	public function testReflectionDocComment()
	{
		$cls = new \ReflectionClass(static::class);

		$method = $cls->getMethod('dumpTokens');
		$doc = $method->getDocComment();

		$doc = new ReflectionDocComment($doc);

		$this->assertCount(4, $doc->getLines(), 'Clean line count');
	}

	/**
	 * Dump PHP file tokens with literal token type names
	 *
	 * @param array $tokens
	 *        	Tokens
	 * @param boolean $transformTypenames
	 *        	Transform token index to string
	 * @return $tokens Possibly transformed tokens
	 */
	private function dumpTokens($tokens, $transformTypenames = true)
	{
		if ($transformTypenames)
			\array_walk($tokens,
				function (&$t) {
					if (\is_array($t))
						$t = '@' . $t[2] . ' ' . \token_name($t[0]) . ' ' .
						$t[1];
					return $t;
				});
		print_r($tokens);
		return $tokens;
	}
}
