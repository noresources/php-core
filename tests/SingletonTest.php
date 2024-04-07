<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\SingletonTrait;

class SingletonTestImplementation
{

	public $firstCallArgument;

	public $name;

	public function __construct($argument = null)
	{
		$this->firstCallArgument = $argument;
	}

	use SingletonTrait;
}

class DerivedSingletonTestImplementation extends SingletonTestImplementation
{
}

final class SingletonTest extends \PHPUnit\Framework\TestCase
{

	final function testInstance()
	{
		$instance = SingletonTestImplementation::getInstance('Fooo');
		$this->assertInstanceOf(SingletonTestImplementation::class,
			$instance);
		$this->assertEquals('Fooo', $instance->firstCallArgument);

		$instance = SingletonTestImplementation::getInstance('Baaar');
		$this->assertEquals('Fooo', $instance->firstCallArgument);
	}

	final function testDerived()
	{
		$parent = SingletonTestImplementation::getInstance();
		$derived = DerivedSingletonTestImplementation::getInstance();
		$this->assertNotEquals($parent, $derived,
			'Singleton of derived is a different instance');
		$parent->name = 'parent';
		$derived->name = 'Derived';
		$this->assertNotEquals($parent->name, $derived->name,
			'Name property');
	}
}