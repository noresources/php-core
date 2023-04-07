<?php
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionDocComment;

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class ReflectionDocCommentTest extends \PHPUnit\Framework\TestCase
{

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

	public function testTags()
	{
		$doc = $this->createDocComment('a.txt');

		$theTags = $doc->getTags('tag');
		$this->assertCount(2, $theTags, 'All @tag');

		$param = $doc->getTag('param');
		$this->assertEquals('type $variableName Parameter description',
			$param, 'Multi line tag value concatenated. ');
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
