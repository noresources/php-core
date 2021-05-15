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
}
