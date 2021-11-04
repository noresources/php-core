<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\SingletonTrait;
use NoreSources\Reflection\ReflectionDocComment;
use NoreSources\Reflection\ReflectionFile;
use NoreSources\Type\TypeDescription as TD;

final class ReflectionTest extends \PHPUnit\Framework\TestCase
{

	use SingletonTrait;

	public function testReflectionFile()
	{
		$file = new ReflectionFile(__FILE__);

		$u = $file->getUseStatements();
		$this->assertArrayHasKey(TD::class, $u,
			'use statement without alias');
		$this->assertEquals('TD', $u[TD::class],
			'use statement with user-defined alias');

		$classes = $file->getClasses();
		$this->assertEquals([
			ReflectionTest::class
		], $classes);

		$ns = $file->getNamespaces();
		$this->assertCount(1, $ns,
			\basename(__FILE__) . ' namespace count');
		$this->assertEquals([
			__NAMESPACE__
		], $ns, \basename(__FILE__) . ' namespace');

		$this->assertEquals(ReflectionFile::class,
			$file->getQualifiedClassName('ReflectionFile'),
			'Class name resolution');

		$file = new ReflectionFile(__DIR__ . '/data/MultiNamespace.php');

		$interfaces = $file->getInterfaces();
		$this->assertEquals(
			[
				'Food\Fruit\Fallable',
				'Food\Fish\AggressiveInterface'
			], $interfaces, 'Interface names');

		$classes = $file->getClasses();
		$this->assertEquals(
			[
				'Food\Fruit\Apple',
				'Food\Fruit\Pear',
				'Food\Fish\Shark',
				'Food\Fish\Cat',
				'Food\Fish\Babel'
			], $classes, 'Multi namespace file class list');

		$traits = $file->getTraits();
		$this->assertEquals([
			'Food\Fish\AggressiveTrait'
		], $traits, 'Trait names');

		$this->assertEquals([
			'Food\\Fruit',
			'Food\\Fish'
		], $file->getNamespaces(),
			'Multiple namespaces in a single file');
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
					if (\is_integer($t[0]))
						$t[0] = \token_name($t[0]);
				});
		return $tokens;
	}
}
