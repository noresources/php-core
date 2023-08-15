<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test\Reflection;

use NoreSources\Reflection\ReflectionDocComment;
use NoreSources\Test\DerivedFileTestTrait;
use ReflectionClass;

class ReflectionDocCommentTest extends \PHPUnit\Framework\TestCase
{
	use DerivedFileTestTrait;

	public function testLines()
	{
		$doc = $this->createDocComment('a.txt');
		$lines = $doc->getLines();

		$textLines = $doc->getTextLines();

		$this->assertEquals('array', \gettype($lines),
			'getLines() return type');

		$abstract = 'Abstract description on two lines';
		$details = [
			'More detailed description, remarks, links, PHPDoc tags etc.'
		];
		$all = \array_merge([
			$abstract
		], $details);

		$this->assertEquals($all, $textLines, 'Standard lines');

		$this->assertEquals($abstract, $doc->getAbstract(), 'Abstract');
		$this->assertEquals($details, $doc->getDetails(),
			'Details (array)');
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

		$this->assertTrue($doc->hasTag('tag'), 'Has @tag');
		$theTags = $doc->getTags('tag');
		$this->assertCount(2, $theTags, 'All @tag');

		$this->assertTrue($doc->hasTag('param'), 'Has @param');
		$param = $doc->getTag('param');
		$this->assertEquals('type $variableName Parameter description',
			$param, 'Multi line tag value concatenated. ');

		$this->assertTrue($doc->hasTag('text-less-tag'),
			'Textless tag exists');
		$this->assertEquals('', $doc->getTag('text-less-tag'),
			'Textless tag content is an empty string');
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

	public function testTypeProperties()
	{
		$tests = [
			'basic' => [
				'declaration' => 'string',
				'properties' => [
					'type' => 'string'
				]
			],
			'class' => [
				'declaration' => ReflectionDocComment::class,
				'properties' => [
					'type' => ReflectionDocComment::class
				]
			],
			'array of class' => [
				'declaration' => ReflectionDocComment::class . '[]',
				'properties' => [
					'type' => 'array',
					'key' => 'integer',
					'value' => ReflectionDocComment::class
				]
			],
			'map of bool' => [
				'declaration' => 'array<string,bool>',
				'properties' => [
					'type' => 'array',
					'key' => 'string',
					'value' => 'bool'
				]
			]
		];
		foreach ($tests as $label => $test)
		{
			$declaration = $test['declaration'];
			$expected = $test['properties'];
			$actual = ReflectionDocComment::getTypeDeclarationProperties(
				$declaration);

			$this->assertEquals($expected, $actual, $label);
		}
	}

	public function __construct($name = null, array $data = [],
		$dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->setUpDerivedFileTestTrait(__DIR__ . '/..');
	}

	public function __destruct()
	{
		$this->tearDownDerivedFileTestTrait();
	}

	/**
	 *
	 * @param string $filename
	 *        	Base name of test file
	 * @return \NoreSources\Reflection\ReflectionDocComment
	 */
	protected function createDocComment($filename)
	{
		$p = __DIR__ . '/../data/ReflectionDocComment/' . $filename;
		$this->assertFileExists($p,
			$filename . ' doc comment test file exists');
		$c = \file_get_contents($p);
		return new ReflectionDocComment($c);
	}
}
