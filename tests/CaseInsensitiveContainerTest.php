<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\Container\CaseInsensitiveKeyMapTrait;

class TestArrayClass implements \ArrayAccess, \Countable,
	\IteratorAggregate
{
	use CaseInsensitiveKeyMapTrait;
}

class CaseInsensitiveContainerTest extends \PHPUnit\Framework\TestCase
{

	public function testStrings()
	{
		$input = [
			'key' => 'value',
			'True' => true,
			'NULL' => null,
			42 => 'The answer to the Ultimate Question of Life, the Universe, and Everything.',
			'one' => 1
		];

		$array = new TestArrayClass($input);
		foreach (\array_keys($input) as $key)
			$this->assertArrayHasKey($key, $array);

		$this->assertArrayHasKey('NuLl', $array,
			'Key exists with a different case');
		$this->assertArrayHasKey('null', $array,
			'Key exists with a different case');
		$this->assertArrayNotHasKey('nil', $array);

		$this->assertEquals(true, $array['tRUe']);
		$this->assertEquals(1, $array['OnE']);

		$array['One'] = 'Un';

		$this->assertEquals('Un', $array['One']);
		$this->assertEquals('Un', $array['one']);

		$array->offsetUnset('true');
		$this->assertArrayNotHasKey('true', $array);
		$this->assertArrayNotHasKey('True', $array);
	}

	public function testIntegers()
	{
		$input = [
			0 => 'zero',
			2 => 'two',
			4 => 'four'
		];

		$array = new TestArrayClass($input);

		$this->assertArrayHasKey(0, $array);
		$this->assertArrayHasKey(2, $array);
		$this->assertArrayNotHasKey(1, $array);

		$this->assertEquals('two', $array[2]);

		$array->offsetUnset(2);
		$this->assertArrayNotHasKey(2, $array);
		$array[2] = 'deux';
		$this->assertEquals('deux', $array[2]);
	}
}
