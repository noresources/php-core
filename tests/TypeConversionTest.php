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

class TypeConversionTestClassWithoutToString
{

	public $value = 5;
}

final class TypeConversionTest extends \PHPUnit\Framework\TestCase
{

	public function testInvalidArray()
	{
		$input = [
			false,
			'A string'
		];

		foreach ($input as $value)
		{
			$dt = TypeConversion::toArray($value, function ($value) {
				return 'fallback';
			});
			$this->assertEquals('fallback', $dt, var_export($value, true));
		}
	}

	public function testInvalidInteger()
	{
		$input = [
			new TypeConversionTestClassWithoutToString(),
			[
				1,
				2,
				3
			],
			'text'
		];

		foreach ($input as $value)
		{
			$actual = TypeConversion::toInteger($value, function ($value) {
				return 'fallback';
			});
			$this->assertEquals('fallback', $actual, var_export($value, true));
		}
	}

	public function testValidInteger()
	{
		$input = [
			128,
			'2014',
			456.125,
			new \DateTime('now'),
			false,
			true,
			null
		];

		foreach ($input as $value)
		{
			$actual = TypeConversion::toInteger($value);
			$this->assertEquals('integer', \gettype($actual),
				var_export($value, true) . ' is string type');
		}
	}

	public function testInvalidString()
	{
		$input = [
			new TypeConversionTestClassWithoutToString(),
			[
				1,
				2,
				3
			]
		];

		foreach ($input as $value)
		{
			$actual = TypeConversion::toString($value, function ($value) {
				return 'fallback';
			});
			$this->assertEquals('fallback', $actual, var_export($value, true));
		}
	}

	public function testValidString()
	{
		$input = [
			'2014-05-04',
			4096,
			1.2,
			new DataTree([
				'key' => "value"
			]),
			new \DateTime('now')
		];

		foreach ($input as $value)
		{
			$actual = TypeConversion::toString($value);
			$this->assertEquals('string', \gettype($actual),
				var_export($value, true) . ' is string type');
		}
	}

	public function testInvalidDateTime()
	{
		$input = [
			new DataTree(),
			'2017 04 08 @ 15:22',
			false,
			[
				'foo-date' => '2012-10-14T16:32:45',
				'bar-timezone_type' => 1,
				'timezone' => '+0100'
			]
		];

		foreach ($input as $value)
		{
			$dt = TypeConversion::toDateTime($value, function ($value) {
				return 'fallback';
			});
			$this->assertEquals('fallback', $dt, var_export($value, true));
		}
	}

	public function testValidDateTime()
	{
		$input = [
			'2014-05-04',
			4096,
			1.2,
			[
				'date' => '2012-10-14T16:32:45',
				'timezone_type' => 1,
				'timezone' => '+0100'
			],
			[
				'time' => '2018-06-15T17:42:18+01:30',
				'format' => \DateTIme::ISO8601
			]
		];

		foreach ($input as $value)
		{
			$dt = TypeConversion::toDateTime($value);
			$this->assertInstanceOf(\DateTIme::class, $dt, var_export($value, true));
		}
	}

	public function testShorthand()
	{
		$fallback = function ($value) {
			return 'fallback';
		};

		$epoch100 = new \DateTime(null);
		$epoch100->setTimestamp(100);
		$epoch100j = unixtojd($epoch100->getTimestamp());
		$epoch100s = $epoch100->format(\DateTIme::ISO8601);

		foreach ([
			[
				'456',
				'integer',
				456
			],
			[
				7,
				'integer',
				7
			],
			[
				$epoch100,
				'integer',
				100
			],
			[
				$epoch100,
				'float',
				$epoch100j
			],
			[
				100,
				'datetime',
				$epoch100
			],
			[
				$epoch100s,
				'string',
				'1970-01-01T00:01:40+0000'
			],
			[
				[
					'date' => '1970-01-01T00:01:40',
					'timezone_type' => 1,
					'timezone' => '+0000'
				],
				'datetime',
				$epoch100
			],
			//[ [ 'date' => 'fail-01-01T00:01:40', 'timezone_type' => 1, 'timezone' => '+0000' ],
			//		'datetime', 'fallback', $fallback ],
			[
				'256.123',
				'float',
				256.123
			],
			[
				8,
				'boolean',
				true
			],
			[
				'text',
				'boolean',
				true
			],
			[
				'',
				'boolean',
				false
			]
		] as $test)
		{
			$value = Container::keyValue($test, 0);
			$type = Container::keyValue($test, 1);
			$expected = Container::keyValue($test, 2);
			$f = Container::keyValue($test, 3, null);
			$actual = null;
			$message = 'success';
			try
			{
				$actual = TypeConversion::to($value, $type, $f);
			}
			catch (\Exception $e)
			{
				$message = $e->getMessage();
			}

			$this->assertEquals('success', $message,
				'Successful conversion from ' . TypeDescription::getName($value) . ' to ' . $type);

			if ($message == 'sucess')
			{
				$this->assertEquals($type, TypeDescription::getName($actual), 'Type name');
				$this->assertEquals($expected, $actual, 'Converted value type');
			}
		}
	}
}
