<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\Type\TypeDescription;

final class TypeDescriptionTest extends \PHPUnit\Framework\TestCase
{

	public function testGetName()
	{
		$types = [
			'integer' => 1,
			'string' => "a string",
			'boolean' => true,
			'NULL' => null,
			'DateTime' => new \DateTime('now'),
			TypeDescription::class => new TypeDescription()
		];

		foreach ($types as $name => $mixed)
		{
			$desc = TypeDescription::getName($mixed);
			$this->assertEquals($name, $desc);
		}
	}

	public function testGetLocalName()
	{
		$types = [
			'DateTime' => (new \DateTime('now')),
			'TypeDescription' => (new TypeDescription())
		];

		foreach ($types as $name => $mixed)
		{
			$desc = TypeDescription::getLocalName($mixed);
			$this->assertEquals($name, $desc);
		}
	}

	public function testGetNamespaces()
	{
		$types = [
			[
				'args' => [
					(new \DateTime('now'))
				],
				'namespaces' => []
			],
			[
				'args' => [
					new TypeDescription()
				],
				'namespaces' => [
					'NoreSources',
					'Type'
				]
			],
			[
				'args' => [
					TypeDescription::class,
					true
				],
				'namespaces' => [
					'NoreSources',
					'Type'
				]
			]
		];

		foreach ($types as $test)
		{
			$name = $test['args'][0];
			$name = (\is_object($name) ? TypeDescription::getName($name) : $name);
			$ns = \call_user_func_array(
				[
					TypeDescription::class,
					'getNamespaces'
				], $test['args']);
			$this->assertEquals('array', TypeDescription::getName($ns),
				'Result is an array');
			$this->assertCount(count($test['namespaces']), $ns,
				'Number of namespace parts for ' . $name);
		}
	}

	public function testStringRepresentation()
	{
		$tests = [
			'null' => [
				null,
				true,
				true
			],
			'bool' => [
				true,
				true,
				true
			],
			'integer' => [
				42,
				true,
				true
			],
			'float' => [
				3.14,
				true,
				true
			],
			"array" => [
				[
					1,
					2
				],
				false,
				false
			],
			'string' => [
				'Obvious',
				true,
				true
			],
			'DateTime' => [
				new \DateTime('now'),
				false,
				true
			],
			'Serializable' => [
				new TypeDescriptionSerializableSampleClass(),
				false,
				true
			],
			'JsonSerialisable' => [
				new TypeDescriptionJsonSerializableSampleClass(),
				false,
				true
			],
			'ArrayObject' => [
				new \ArrayObject([
					1,
					2
				]),
				false,
				true // Serializable
			]
		];

		foreach ($tests as $label => $test)
		{
			$this->assertEquals($test[1],
				TypeDescription::hasStringRepresentation($test[0], true),
				$label . ' strict conversion');
			$this->assertEquals($test[2],
				TypeDescription::hasStringRepresentation($test[0], false),
				$label . ' non strict conversion');
		}
	}
}
