<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\Bitset;
use NoreSources\DateTime;

final class BitsetTest extends \PHPUnit\Framework\TestCase
{

	public function testConstructor()
	{
		$six = [
			'from int' => 6,
			'from string' => '110',
			'from array' => [
				0,
				1,
				1
			],
			'from bitset' => new Bitset(6),
			'from ArrayAccess' => new \ArrayObject([
				0,
				1,
				1
			]),
			'from integer representation of a date' => new DateTime(
				'@6')
		];

		$previous = null;
		$previousType = null;
		foreach ($six as $type => $value)
		{
			$actual = new Bitset($value);

			if ($previous instanceof Bitset)
				$this->assertEquals($previous, $actual,
					'Compare constructor ' . $previousType . ' with ' .
					$type);

			$previous = $actual;
			$previousType = $type;
		}
	}

	public function testArrayRepresentation()
	{
		$i = new Bitset(6);
		$actual = $i->getArrayCopy();

		$expected = [
			0,
			1,
			1
		];

		$this->assertEquals($expected, $actual,
			'Array representation of ' . $i);
	}

	public function testStringRepresentation()
	{
		$i = new Bitset(5);

		$this->assertEquals('101', $i->getBinaryString(),
			'Binary string');

		$this->assertEquals('00000101', $i->getBinaryString('0', 8),
			'Binary string');
	}

	public function testMatch()
	{
		$bs = new Bitset(6);

		$this->assertTrue($bs->match(4), 'Lazy match');
		$this->assertTrue($bs->match(2), 'Lazy match');
		$this->assertFalse($bs->match(0), 'Lazy match');
		$this->assertTrue($bs->match(6, true), 'Strict match 6');
		$this->assertTrue($bs->match(0, true), 'Strict match 0');
	}

	public function testArrayAccess()
	{
		$bs = new Bitset('110');

		$this->assertEquals(1, $bs[2], 'ArrayAccess');
		$this->assertEquals(0, $bs[0], 'ArrayAccess');

		$seven = clone $bs;
		$seven[0] = true;
		$this->assertEquals(1, $seven[0], 'ArrayAccess[0] = 1');

		$three = clone $seven;
		$three->offsetUnset(2);
		$this->assertEquals(3, $three->getIntegerValue(), 'offsetUnset');
	}

	public function testOutOfRange()
	{
		$bs = new Bitset(7);
		$this->expectException(\OutOfRangeException::class);
		$bs[-5] = 1;
	}

	public function testOutOfRange2()
	{
		$bs = new Bitset(7);
		$this->expectException(\OutOfRangeException::class);
		$bs[32656] = 1;
	}

	public function testMax()
	{
		$this->assertEquals(255, Bitset::getMaxIntegerValue(8, false),
			'Max value of a byte');
		$this->assertEquals(127, Bitset::getMaxIntegerValue(8, true),
			'Max value of a signed byte');
	}
}