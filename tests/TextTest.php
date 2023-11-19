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

	final function testWord()
	{
		foreach ([
			'Hello world' => [
				'Hello',
				'world'
			],
			'MACRO_CASE' => [
				'MACRO',
				'CASE'
			],
			'A real assertion.' => [
				'A',
				'real',
				'assertion'
			],
			'someSkunkFunk()' => [
				'some',
				'Skunk',
				'Funk'
			],
			'pide-or-durum' => [
				'pide',
				'or',
				'durum'
			]
		] as $text => $expected)
		{
			$actual = Text::explodeCodeWords($text);
			$this->assertEquals(\implode(', ', $expected),
				\implode(', ', $actual), $text);
		}
	}

	final function testToCodeCase()
	{
		$tests = [
			'hello world' => [
				'Camel' => 'helloWorld',
				'Human' => 'Hello world',
				'Kebab' => 'hello-world',
				'Macro' => 'HELLO_WORLD',
				'Pascal' => 'HelloWorld',
				'Snake' => 'hello_world',
				'Train' => 'hello_World'
			],
			'helloWorld' => [
				'Camel' => 'helloWorld',
				'Human' => 'Hello world',
				'Kebab' => 'hello-world',
				'Macro' => 'HELLO_WORLD',
				'Pascal' => 'HelloWorld',
				'Snake' => 'hello_world',
				'Train' => 'hello_World'
			],
			' Hello?world/' => [
				'Camel' => 'helloWorld',
				'Human' => 'Hello world',
				'Kebab' => 'hello-world',
				'Macro' => 'HELLO_WORLD',
				'Pascal' => 'HelloWorld',
				'Snake' => 'hello_world',
				'Train' => 'hello_World'
			],
			'M_id' => [
				'Camel' => 'mId',
				'Human' => 'M id',
				'Kebab' => 'm-id',
				'Macro' => 'M_ID',
				'Pascal' => 'MId',
				'Snake' => 'm_id',
				'Train' => 'm_Id'
			],
			'PascalThePhilosopher' => [
				'Camel' => 'pascalThePhilosopher',
				'Human' => 'Pascal the philosopher',
				'Kebab' => 'pascal-the-philosopher',
				'Macro' => 'PASCAL_THE_PHILOSOPHER',
				'Pascal' => 'PascalThePhilosopher',
				'Snake' => 'pascal_the_philosopher',
				'Train' => 'pascal_The_Philosopher'
			],
			'ACME' => [
				'Camel' => 'acme',
				'Human' => 'Acme',
				'Kebab' => 'acme',
				'Macro' => 'ACME',
				'Pascal' => 'Acme',
				'Snake' => 'acme',
				'Train' => 'acme'
			],
			'UBER ACME' => [
				'Camel' => [
					0 => 'uberAcme',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'uberACME'
				],
				'Human' => [
					0 => 'Uber acme',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'UBER ACME'
				],
				'Kebab' => 'uber-acme',
				'Macro' => 'UBER_ACME',
				'Pascal' => [
					0 => 'UberAcme',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'UBERACME'
				],
				'Snake' => 'uber_acme',
				'Train' => [
					0 => 'uber_Acme',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'uber_ACME'
				]
			],
			'MIDI is more rich than the DMX protocol' => [
				'Camel' => [
					0 => 'midiIsMoreRichThanTheDmxProtocol',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'midiIsMoreRichThanTheDMXProtocol'
				],
				'Human' => [
					0 => 'Midi is more rich than the dmx protocol',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'MIDI is more rich than the DMX protocol'
				],
				'Kebab' => 'midi-is-more-rich-than-the-dmx-protocol',
				'Macro' => 'MIDI_IS_MORE_RICH_THAN_THE_DMX_PROTOCOL',
				'Pascal' => [
					0 => 'MidiIsMoreRichThanTheDmxProtocol',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'MIDIIsMoreRichThanTheDMXProtocol'
				],
				'Snake' => 'midi_is_more_rich_than_the_dmx_protocol',
				'Train' => [
					0 => 'midi_Is_More_Rich_Than_The_Dmx_Protocol',
					Text::CODE_CASE_PRESERVE_CAPITAL_WORDS => 'midi_Is_More_Rich_Than_The_DMX_Protocol'
				]
			]
		];

		foreach ($tests as $text => $styles)
		{
			foreach ($styles as $style => $expected)
			{
				$method = 'to' . $style . 'Case';

				$options = [];
				if (\is_array($expected))
					$options = $expected;
				else
					$options = [
						$expected
					];

				foreach ($options as $option => $expected)
				{
					$label = $style . ' of "' . $text . '"';
					$arguments = [
						$text
					];
					if ($option > 0)
					{
						$arguments[] = $option;
						$label .= ' with additional option ' .
							sprintf('%02X', $option);
					}
					$actual = \call_user_func_array(
						[
							Text::class,
							$method
						], $arguments);
					$this->assertEquals($expected, $actual, $label);
				}
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