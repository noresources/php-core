<?php
use NoreSources\Text\Tokenifier;

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class TokenifierTest extends \PHPUnit\Framework\TestCase
{

	public function testValidInput()
	{
		$tokenizer = new Tokenifier();

		$tests = [
			'Empty' => [
				'input' => '',
				'expected' => []
			],
			[
				'input' => 'word',
				'expected' => [
					'word'
				]
			],
			[
				'input' => 'two words',
				'expected' => [
					'two',
					'words'
				]
			],
			'Escape white space' => [
				'input' => 'one\\ word',
				'expected' => [
					'one word'
				]
			],
			'Escape escape' => [
				'input' => 'two\\\\ words',
				'expected' => [
					'two\\',
					'words'
				]
			],
			"Quoting pair" => [
				'input' => '"one word"',
				'expected' => [
					'one word'
				]
			],
			'Other quoting pairs' => [
				'input' => ' "three" \'simple\' "words" ',
				'expected' => [
					'three',
					'simple',
					'words'
				]
			],
			'Inner quoting pair' => [
				'input' => 'this "will \'mix\' quoting pairs"',
				'expected' => [
					'this',
					"will 'mix' quoting pairs"
				]
			],
			'Escape quote' => [
				'input' => '"Escape \\"quotes"',
				'expected' => [
					'Escape "quotes'
				]
			],
			'Not escapable' => [
				'input' => 'The \\letter "l" is not escapable',
				'expected' => [
					'The',
					'\\letter',
					'l',
					'is',
					'not',
					'escapable'
				]
			],
			'Apostrophe' => [
				'input' => 'L\\\'aventure c\\\'est l\\\'aventure',
				'expected' => [
					'L\'aventure',
					'c\'est',
					'l\'aventure'
				]
			],
			'Apostrophe 2' => [
				'input' => "L\\'aventure c\\'est l\\'aventure",
				'expected' => [
					'L\'aventure',
					'c\'est',
					'l\'aventure'
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$actual = $tokenizer($test['input']);
			$this->assertEquals($test['expected'], $actual, $label);
		}
	}

	public function testInvaokdInput()
	{
		$tokenifier = new Tokenifier();
		$this->expectException(\InvalidArgumentException::class,
			'Unterminated quoted string');
		$tokenifier('Foo "bar');
	}

	public function testUnicode()
	{
		if (!\extension_loaded('mbstring'))
			return $this->assertFalse(\extension_loaded('mbstring'),
				'No mbstring implementation');

		$this->assertTrue(\function_exists('\mb_strlen'),
			'mb_strlen() exists()');

		$split = function ($text) {
			return \array_values(
				\array_filter(\preg_split('//u', $text),
					function ($c) {
						return !empty($c);
					}));
		};
		if (\function_exists('\mb_str_split'))
			$split = '\mb_str_split';

		$tokenizer = new Tokenifier();
		$tokenizer->setStringFunctions('\mb_strlen', $split);

		$tokenizer->clearQuotingPairs();
		$tokenizer->addQuotingPair('€');
		$input = 'Euro is €a quote€';
		$expected = [
			'Euro',
			'is',
			'a quote'
		];
		$actual = $tokenizer($input);
		$this->assertEquals($expected, $actual);
	}
}
