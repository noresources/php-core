<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class BuiltinReflectionTest extends \PHPUnit\Framework\TestCase
{

	public $foo = 'bar';

	public function testReflectionPropertyProperties()
	{
		$inception = new ReflectionClass(ReflectionProperty::class);
		foreach ([
			'name',
			'class'
		] as $name)
		{
			$this->assertTrue($inception->hasProperty($name),
				ReflectionProperty::class . ' has $' . $name .
				' property');
			$property = $inception->getProperty($name);
			$this->assertTrue($property->isPublic(),
				'$' . $name . ' property is public');
		}
	}
}
