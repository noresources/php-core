<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

/**
 * Text and structured text tests
 */
final class TextTest extends \PHPUnit\Framework\TestCase
{

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

	final function testToCamelCase()
	{
		$tests = [
			'hello world' => [
				'toCamelCase' => 'HelloWorld'
			],
			' Hello?world/' => [
				'toCamelCase' => 'HelloWorld',
				'toSmallCamelCase' => 'helloWorld',
				'toSnakeCase' => 'hello_world'
			],
			'M_id' => [
				'toSmallCamelCase' => 'mId'
			]
		];

		foreach ($tests as $text => $styles)
		{
			foreach ($styles as $style => $expected)
			{
				$actual = \call_user_func([
					Text::class,
					$style
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
		$data = StructuredText::fileToArray(
			__DIR__ . '/data/sample.xml');
	}
}