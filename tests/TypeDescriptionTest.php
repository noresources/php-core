<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

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
				'type' => (new \DateTime('now')),
				'namespaces' => []
			],
			[
				'type' => new TypeDescription(),
				'namespaces' => [
					'NoreSources'
				]
			],
			[
				'type' => TypeDescription::class,
				'namespaces' => [
					'NoreSources'
				]
			]
		];

		foreach ($types as $test)
		{
			$name = \is_object($test['type']) ? TypeDescription::getName($test['type']) : $test['type'];
			$ns = TypeDescription::getNamespaces($test['type']);
			$this->assertEquals('array', TypeDescription::getName($ns), 'Result is an array');
			$this->assertCount(count($test['namespaces']), $ns,
				'Number of namespace parts for ' . $name);
		}
	}
}
