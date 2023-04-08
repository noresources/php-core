<?php
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionDocComment;
use NoreSources\Test\DerivedFileTestTrait;

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class ReflectionDocCommentTest extends \PHPUnit\Framework\TestCase
{
	use DerivedFileTestTrait;

	public function testLines()
	{
		$doc = $this->createDocComment('a.txt');
		$lines = $doc->getLines();

		$textLines = Container::filterValues($lines,
			function ($v) {
				$f = \substr($v, 0, 1);
				return !\in_array($f, [
					'@'
				]);
			});

		$this->assertEquals('array', \gettype($lines),
			'getLines() return type');

		$this->assertEquals(
			[
				'Abstract description on two lines',
				'More detailed description, remarks, links, PHPDoc tags etc.'
			], $textLines, 'Standard lines');
	}

	public function testToString()
	{
		$method = __METHOD__;
		$suffix = null;
		$extension = 'txt';
		$doc = $this->createDocComment('a.txt');
		$text = \strval($doc);
		$this->assertDataEqualsReferenceFile($text, $method, $suffix,
			$extension, 'DocComment string representation');

		$doc2 = new ReflectionDocComment($text);
		$text2 = \strval($doc2);
		$this->assertDataEqualsReferenceFile($text2, $method, $suffix,
			$extension,
			'DocComment string representation reinterpretation does not change ReflectionDocComment content');
	}

	public function testTags()
	{
		$doc = $this->createDocComment('a.txt');

		$theTags = $doc->getTags('tag');
		$this->assertCount(2, $theTags, 'All @tag');

		$param = $doc->getTag('param');
		$this->assertEquals('type $variableName Parameter description',
			$param, 'Multi line tag value concatenated. ');
	}

	public function testParams()
	{
		$cls = new ReflectionClass(ReflectionDocComment::class);
		$method = $cls->getMethod('getParameter');
		$doc = new ReflectionDocComment($method->getDocComment());
		$name = $doc->getParameter('name');
		$this->assertEquals('array', \gettype($name),
			'Has $name parameter');
		$invalid = $doc->getParameter('Kaoue');
		$this->assertEquals('NULL', \gettype($invalid),
			'Not has $kapoue parameter');
	}

	public function testReturn()
	{
		$cls = new ReflectionClass(ReflectionDocComment::class);
		$method = $cls->getMethod('getParameter');
		$doc = new ReflectionDocComment($method->getDocComment());
		$r = $doc->getReturn();
		$this->assertEquals('array', \gettype($r), 'Has @return');
		$this->assertArrayHasKey('types', $r);
		$this->assertEquals([
			'string[]',
			'NULL'
		], $r['types'], 'Return types');
	}

	public function __construct($name = null, array $data = [],
		$dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->initializeDerivedFileTest(__DIR__);
	}

	/**
	 *
	 * @param string $filename
	 *        	Base name of test file
	 * @return \NoreSources\Reflection\ReflectionDocComment
	 */
	protected function createDocComment($filename)
	{
		$p = __DIR__ . '/data/ReflectionDocComment/' . $filename;
		$this->assertFileExists($p,
			$filename . ' doc comment test file exists');
		$c = \file_get_contents($p);
		return new ReflectionDocComment($c);
	}
}
