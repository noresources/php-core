<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\ComparableInterface;
use NoreSources\NotComparableException;
use NoreSources\Type\StringRepresentation;
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

	public function testException()
	{
		$this->expectException(NotComparableException::class);
		$a = new ComparableInteger(1);
		$s = new ComparableString('text');

		$a->compare($s);
	}
}