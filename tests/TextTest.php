<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\Container\Container;
use NoreSources\Text\StructuredText;
use NoreSources\Text\Text;
use NoreSources\Type\TypeConversionException;

/**
 * Text and structured text tests
 */
final class TextTest extends \PHPUnit\Framework\TestCase
{

	final function testFirstOf()
	{
		$input = 'Acbed';
		$result = Text::firstOf($input, [
			'c',
			'd',
			'A',
			'e',
			'Z',
			"b"
		]);

		$this->assertEquals(
			[
				0 => 'A',
				1 => 'c',
				2 => 'b',
				3 => 'e',
				4 => 'd'
			], $result, 'All results');

		list ($p, $c) = Container::first($result);
		$this->assertEquals(0, $p, 'First position');
		$this->assertEquals('A', $c, 'First string');

		$this->assertEquals([
			-1 => false
		], Text::firstOf('abc', [
			'd',
			'e',
			'f'
		]), 'None of them');
	}

	final function testToHex()
	{
		$this->assertEquals('01', Text::toHexadecimalString(true),
			'true');
		$this->assertEquals('00', Text::toHexadecimalString(false),
			'false');
		$this->assertEquals('00', Text::toHexadecimalString(null),
			'null');

		$tests = [
			1 => '01',
			1585608654548 => '01712da3fed4',
			'hello world' => '68656c6c6f20776f726c64',
			'I çay ¥€$ !' => '4920c3a7617920c2a5e282ac242021'
		];

		foreach ($tests as $input => $expected)
		{
			$this->assertEquals($expected,
				Text::toHexadecimalString($input),
				$input . ' (lowercase)');

			$this->assertEquals(\strtoupper($expected),
				Text::toHexadecimalString($input, true),
				$input . ' (uppercase)');
		}
	}

	final function testToCodeCase()
	{
		$tests = [
			'hello world' => [
				'Camel' => 'helloWorld',
				'Kebab' => 'hello-world',
				'Pascal' => 'HelloWorld',
				'Snake' => 'hello_world'
			],
			'helloWorld' => [
				'Camel' => 'helloWorld',
				'Kebab' => 'hello-world',
				'Pascal' => 'HelloWorld',
				'Snake' => 'hello_world'
			],
			' Hello?world/' => [
				'Camel' => 'helloWorld',
				'Kebab' => 'hello-world',
				'Pascal' => 'HelloWorld',
				'Snake' => 'hello_world'
			],
			'M_id' => [
				'Camel' => 'mId',
				'Kebab' => 'm-id',
				'Pascal' => 'MId',
				'Snake' => 'm_id'
			],
			'PascalThePhilosopher' => [
				'Camel' => 'pascalThePhilosopher',
				'Kebab' => 'pascal-the-philosopher',
				'Pascal' => 'PascalThePhilosopher',
				'Snake' => 'pascal_the_philosopher'
			]
		];

		foreach ($tests as $text => $styles)
		{
			foreach ($styles as $style => $expected)
			{
				$method = 'to' . $style . 'Case';
				$actual = \call_user_func([
					Text::class,
					$method
				], $text);
				$this->assertEquals($expected, $actual,
					$style . ' of "' . $text . '"');
			}
		}
	}

	final function testStructuredTextUrlEncoded()
	{
		$tests = [
			'simple value' => [
				'input' => \urlencode('Simple value'),
				'expected' => 'Simple value'
			],
			'key value' => [
				'input' => \http_build_query([
					'key' => 'value'
				]),
				'expected' => [
					'key' => 'value'
				]
			],
			'text with a "&"' => [
				'input' => \urlencode('text with a "&"'),
				'expected' => 'text with a "&"'
			],
			'multiple key value' => [
				'input' => \http_build_query(
					[
						'key' => 'value',
						'empty' => '',
						'foo' => 'bar'
					]),
				'expected' => [
					'key' => 'value',
					'empty' => '',
					'foo' => 'bar'
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$actual = StructuredText::parseText($test['input'],
				StructuredText::FORMAT_URL_ENCODED);

			$this->assertEquals($test['expected'], $actual, $label);
		}
	}

	final function testStructuredTextJson()
	{
		$json = [
			'a',
			'int',
			'float',
			'null',
			'boolean'
		];

		foreach ($json as $name)
		{
			$filename = __DIR__ . '/data/' . $name . '.json';
			$expected = \json_decode(\file_get_contents($filename), true);
			$actual = StructuredText::parseFile($filename);
			$this->assertEquals($expected, $actual, 'JSON ' . $name);
		}
	}

	final function testStructureTextFromTextFailure()
	{
		$this->expectException(TypeConversionException::class);
		$data = StructuredText::parseFile(__DIR__ . '/data/sample.xml');
	}
}