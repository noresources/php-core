<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\ComparableInterface;
use NoreSources\ComparisonException;
use NoreSources\Type\IntegerRepresentation;
use NoreSources\Type\TypeComparison;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;
use PHPUnit\Framework\TestCase;

class TypeComparisonTestInteger implements ComparableInterface,
	IntegerRepresentation
{

	public function __construct($v)
	{
		$this->value = $v;
	}

	public function compare($b)
	{
		return $this->value - TypeConversion::toInteger($b);
	}

	public function getIntegerValue()
	{
		return $this->value;
	}

	private $value;
}

final class TypeComparisonTest extends TestCase
{

	public function testComparisons()
	{
		$tests = [
			'nulls' => [
				null,
				null,
				0
			],
			'null vs anything' => [
				null,
				'a',
				-1
			],
			'ints' => [
				3,
				7,
				-1
			],
			'same ints' => [
				13,
				13,
				0
			],
			'int vs float' => [
				1,
				1.5,
				-1
			],
			'int vs text' => [
				42,
				"Fourty two",
				ComparisonException::class
			],
			'int vs text representing an integer' => [
				42,
				'123',
				-1
			],
			'int vs text representing an float' => [
				42,
				'1.23',
				1
			],
			'int vs text representing an integer' => [
				42,
				'123',
				-1
			],
			'float vs text representing an float' => [
				3.14,
				'1.23',
				1
			],
			'strings' => [
				'abc',
				'def',
				-1
			],
			'same string' => [
				'foo',
				'foo',
				0
			],
			'string vs float' => [
				'Pi',
				3.14,
				ComparisonException::class
			],
			'false vs int' => [
				false,
				0,
				0
			],
			'true vs int' => [
				true,
				-2,
				0
			],
			'true vs text' => [
				true,
				"text",
				0
			],
			'true vs empty text' => [
				true,
				"",
				1
			],
			'false vs text' => [
				false,
				"text",
				-1
			],
			'false vs empty text' => [
				false,
				"",
				0
			],
			'array' => [
				'foo',
				[
					'bar'
				],
				ComparisonException::class
			],
			'Same date' => [
				new \DateTime('2010-11-12T13:14:15+0000'),
				new \DateTime('2010-11-12T13:14:15+0000'),
				0
			],
			'Date vs int' => [
				new \DateTime('2010-11-12T13:14:15+0000'),
				(60 * 60 * 30 * 365),
				1
			],
			'Comparable' => [
				new TypeComparisonTestInteger(4),
				new TypeComparisonTestInteger(4),
				0
			],
			'Comparable vs int' => [
				5,
				new TypeComparisonTestInteger(4),
				1
			]
		];

		foreach ($tests as $label => $test)
		{
			$a = $test[0];
			$b = $test[1];
			$expected = $test[2];

			$actual = '?';
			try
			{
				$actual = TypeComparison::compare($a, $b);
				$actual = ($actual > 0) ? 1 : (($actual < 0) ? -1 : 0);
			}
			catch (\Exception $e)
			{
				$actual = TypeDescription::getName($e);
			}

			$this->assertEquals($expected, $actual, $label);

			$actual = '?';
			try
			{
				$actual = -TypeComparison::compare($b, $a);
				$actual = ($actual > 0) ? 1 : (($actual < 0) ? -1 : 0);
			}
			catch (\Exception $e)
			{
				$actual = TypeDescription::getName($e);
			}

			$this->assertEquals($expected, $actual,
				$label . ' (inverted)');
		}
	}
}