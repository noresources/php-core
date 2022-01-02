<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\ComparableInterface;
use NoreSources\DateTime;
use NoreSources\NotComparableException;
use NoreSources\Type\StringRepresentation;
use NoreSources\Type\TypeComparison;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

class ComparableInteger implements ComparableInterface,
	StringRepresentation
{

	/**
	 *
	 * @var integer
	 */
	public $value;

	public function __toString()
	{
		return \strval($this->value);
	}

	public function __construct($v)
	{
		$this->value = $v;
	}

	public function compare($value)
	{
		if (!($value instanceof ComparableInteger))
			throw new NotComparableException($this, $value);

		return ($this->value - $value->value);
	}
}

class ComparableString implements ComparableInterface
{

	public $value;

	public function __toString()
	{
		return $this->value;
	}

	public function __construct($v)
	{
		$this->value = $v;
	}

	public function compare($value)
	{
		if (!TypeDescription::hasStringRepresentation($value))
			throw new NotComparableException($this, $value);

		return \strcmp($this->value, \strval($value));
	}
}

final class ComparableTest extends \PHPUnit\Framework\TestCase
{

	public function testInt()
	{
		$a = new ComparableInteger(1);
		$b = new ComparableInteger(10);
		$c = new ComparableInteger(-2);

		$this->assertEquals(0, $a->compare($a), 'Compare to itself');
		$this->assertLessThan(0, $a->compare($b), '1 < 10');
		$this->assertGreaterThan(0, $a->compare($c), '1 > -2');
	}

	public function testString()
	{
		$a = new ComparableString('A');
		$text = new ComparableString('text');
		$number = new ComparableString('42');
		$integer = new ComparableInteger(42);

		$this->assertEquals(0, $a->compare($a), 'A == A');
		$this->assertLessThan(0, $a->compare($text), 'A < text');

		$this->assertEquals(0, $number->compare($integer), '"42" == 42');
	}

	public function testDateTime()
	{
		$utc = new \DateTimeZone('UTC');
		$newYork = new \DateTimeZone('America/New_York');
		$tokyo = new \DateTimeZone('Asia/Tokyo');

		$epochAtTokyo = new \DateTime('@0', $tokyo);
		$epoch = new \DateTime('@0', $utc);
		$epochAtNewYork = new \DateTime('@0', $newYork);

		$nowAtTokyo = new \DateTime('now', $tokyo);
		$now = new \DateTime('now', $utc);
		$nowAtNewYork = new \DateTime('now', $newYork);

		$tests = [
			'epoch' => [
				'a' => $epoch,
				'b' => $epoch,
				'expected' => 0
			],
			'epoch at different time zones' => [
				'a' => $epochAtTokyo,
				'b' => $epoch,
				'expected' => 0
			],
			'epoch and now' => [
				'a' => $epoch,
				'b' => $now,
				'expected' => -1
			],
			'now at different time zones' => [
				'a' => $nowAtNewYork,
				'b' => $now,
				'expected' => 0
			]
		];

		foreach ($tests as $label => $test)
		{
			$a = $test['a'];
			$b = $test['b'];
			$expected = $test['expected'];

			foreach ([
				[
					$a,
					$b
				],
				[
					$a,
					TypeConversion::toString($b)
				],
				[
					$a,
					$b->getTimestamp()
				],
				[
					$a,
					DateTime::toJulianDay($b)
				],
				[
					TypeConversion::toString($a),
					$b
				],
				[
					$a->getTimestamp(),
					$b
				],
				[
					DateTime::toJulianDay($a),
					$b
				]
			] as $pair)
			{
				$a = $pair[0];
				$b = $pair[1];
				$text = $label . ': ' .
					TypeDescription::getLocalName($a) . ' ' .
					TypeConversion::toString($a) . ' vs ' .
					TypeDescription::getLocalName($b) . ' ' .
					TypeConversion::toString($b);

				$actual = TypeComparison::compare($a, $b);
				$actual = max(-1, min(1, $actual));

				$this->assertEquals($expected, $actual, $text);
			}
		}
	}

	public function testException()
	{
		$this->expectException(NotComparableException::class);
		$a = new ComparableInteger(1);
		$s = new ComparableString('text');

		$a->compare($s);
	}
}