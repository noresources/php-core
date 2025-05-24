<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\DateTime;
use NoreSources\Container\Container;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeConversionException;
use NoreSources\Type\TypeDescription;

class TypeConversionTestClassWithoutToString
{

	public $value = 5;
}

class TypeConversionTestInteger
{

	/**
	 *
	 * @var integer
	 */
	public $value = 0;

	public static function createFromInteger($value)
	{
		$o = new TypeConversionTestInteger();
		$o->value = $value;
		return $o;
	}
}

final class TypeConversionTest extends \PHPUnit\Framework\TestCase
{

	public function testToObject()
	{
		$tests = [
			'From integer to object' => [
				'value' => 42,
				'type' => TypeConversionTestInteger::class,
				'flags' => (TypeConversion::OPTION_FLAG_OBJECT_CONSTRUCTOR |
				TypeConversion::OPTION_FLAG_OBJECT_FACTORY)
			],
			'From float to object' => [
				'value' => 3.14159,
				'type' => TypeConversionTestInteger::class,
				'flags' => (TypeConversion::OPTION_FLAG_OBJECT_CONSTRUCTOR |
				TypeConversion::OPTION_FLAG_OBJECT_FACTORY),
				'expected' => 0
			],
			'From float to object (no constructor)' => [
				'value' => 3.14159,
				'type' => TypeConversionTestInteger::class,
				'flags' => (TypeConversion::OPTION_FLAG_OBJECT_FACTORY),
				'expected' => TypeConversionException::class
			]
		];

		foreach ($tests as $label => $test)
		{
			$value = $test['value'];
			$type = $test['type'];
			$flags = Container::keyValue($test, 'flags', 0);
			$expected = Container::keyValue($test, 'expected', $value);
			$actual = null;
			try
			{
				$actual = TypeConversion::to($type, $value,
					[
						TypeConversion::OPTION_FLAGS => $flags
					]);

				$actual = $actual->value;
			}
			catch (\Exception $e)
			{
				$actual = \get_class($e);
			}

			$this->assertEquals($expected, $actual, $label);
		}
	}

	public function testArrayObectToArray()
	{
		$a = new \ArrayObject([
			'foo',
			'bar'
		]);
		$expected = [
			'foo',
			'bar'
		];
		$actual = TypeConversion::toArray($a);
		$this->assertEquals($expected, $actual, 'ArrayObject to array');
	}

	public function testInvalidArray()
	{
		$input = [
			false,
			'A string',
			null,
			42,
			3.14159
		];

		foreach ($input as $value)
		{
			$dt = TypeConversion::toArray($value,
				[
					TypeConversion::OPTION_FALLBACK => function ($value) {
						return 'fallback';
					}
				]);
			$this->assertEquals('fallback', $dt,
				var_export($value, true));
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
			$actual = TypeConversion::toInteger($value,
				[
					TypeConversion::OPTION_FALLBACK => function ($value) {
						return 'fallback';
					}
				]);
			$this->assertEquals('fallback', $actual,
				var_export($value, true));
		}
	}

	public function testValidInteger()
	{
		$input = [
			128,
			'2014',
			456.125,
			new \DateTime('now'),
			new \DateTimeZone('Europe/Berlin'),
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
			$actual = TypeConversion::toString($value,
				[
					TypeConversion::OPTION_FALLBACK => function ($value) {
						return 'fallback';
					}
				]);
			$this->assertEquals('fallback', $actual,
				var_export($value, true));
		}
	}

	public function testValidString()
	{
		$input = [
			'2014-05-04',
			4096,
			1.2,

			new \DateTime('now'),
			new \DateTimeZone('Europe/Berlin')
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
			$dt = null;
			try
			{
				$dt = TypeConversion::toDateTime($value,
					[
						TypeCOnversion::OPTION_FALLBACK => function (
							$value) {
							return 'fallback';
						}
					]);
			}
			catch (\Exception $e)
			{
				$dt = $e->getMessage();
			}

			$this->assertEquals('fallback', $dt,
				var_export($value, true));
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
			$dt = null;
			try
			{
				$dt = TypeConversion::toDateTime($value);
			}
			catch (\Exception $e)
			{
				$dt = $e->getMessage();
			}
			$this->assertInstanceOf(\DateTIme::class, $dt,
				var_export($value, true));
		}

		$tests = [
			'epoch as UNIX timestamp' => [
				'value' => 0,
				'expected' => new DateTime('@0',
					DateTime::getUTCTimezone())
			]
		];

		foreach ($tests as $label => $test)
		{
			$value = $test['value'];
			$actual = TypeConversion::toDateTime($value,
				[
					TypeConversion::OPTION_TIMEZONE => DateTime::getUTCTimezone()
				]);

			if (($expected = Container::keyValue($test, 'expected')))
			{
				$this->assertEquals($expected, $actual, $label);
			}
		}
	}

	public function testToDateTime()
	{
		$utc = new \DateTimeZone('UTC');
		$tokyo = new \DateTimeZone('Asia/Tokyo');
		$berlin = new \DateTimeZone('Europe/Berlin');
		$system = new \DateTimeZone(\date_default_timezone_get());
		$now = new \DateTime('now');
		$now->setTimezone($utc);

		$tests = [
			[
				'input' => '2010-11-12T13:14:15'
			],
			[
				'input' => '2010-11-12T13:14:15',
				'timezone' => $system
			],
			[
				'input' => '2010-11-12T13:14:15',
				'inputTimezone' => $system,
				'timezone' => $tokyo,
				'expected' => '2010-11-12T13:14:15+0900'
			],
			[
				'input' => '2010-11-12T13:14:15+01:00',
				'inputTimezone' => $berlin,
				'timezone' => $tokyo,
				'expected' => '2010-11-12T21:14:15+0900'
			]
		];

		foreach ($tests as $label => $test)
		{
			$input = Container::keyValue($test, 'input');
			$timezone = Container::keyValue($test, 'timezone', $system);
			$value = TypeConversion::toDateTime($input,
				[
					TypeConversion::OPTION_TIMEZONE => $timezone
				]);

			if (($expected = Container::keyValue($test, 'expected')))
			{
				$actual = $value->format(DateTime::ISO8601);
				$this->assertEquals($expected, $actual, $label);
			}

			$actualOffset = $value->getTimezone()->getOffset($now);
			$expectedOffset = $timezone->getOffset($now);
			$text = $label . ' Time zone offset (' .
				TypeConversion::toString($timezone) . ', ' .
				$expectedOffset . ')';
			$this->assertEquals($expectedOffset, $actualOffset, $text);
		}
	}

	public function testToBoolean()
	{
		$tests = [
			[
				'input' => 0,
				'expected' => false
			],
			[
				'input' => '',
				'expected' => false
			],
			[
				'input' => [],
				'expected' => false
			],
			[
				'input' => 'false',
				'expected' => true
			],
			[
				'input' => 'False',
				'expected' => false,
				'options' => [
					TypeConversion::OPTION_FALSE_STRINGS => [
						'off',
						'false'
					]
				]
			]
		];

		foreach ($tests as $test)
		{
			$input = Container::keyValue($test, 'input');
			$expected = Container::keyValue($test, 'expected');
			$options = Container::keyValue($test, 'options', []);

			$label = TypeDescription::getName($input) . ' ' .
				@\strval($input);

			$actual = TypeConversion::toBoolean($input, $options);
			$this->assertEquals($expected, $actual, $label);
		}
	}

	public function testShorthand()
	{
		$fallback = function ($value) {
			return 'fallback';
		};

		$epoch100 = new \DateTime('now');
		$epoch100->setTimestamp(100);
		$epoch100s = $epoch100->format(\DateTIme::ISO8601);
		$tests = [
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
			// [ [ 'date' => 'fail-01-01T00:01:40', 'timezone_type' => 1, 'timezone' => '+0000' ],
			// 'datetime', 'fallback', $fallback ],
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
		];
		if (\extension_loaded('calendar'))
		{
			$epoch100j = \unixtojd($epoch100->getTimestamp());
			$tests[] = [
				$epoch100,
				'float',
				$epoch100j
			];
		}
		foreach ($tests as $test)
		{
			$value = Container::keyValue($test, 0);
			$type = Container::keyValue($test, 1);
			$flags = Container::keyValue($test, 'flags', 0);
			$expected = Container::keyValue($test, 2);
			$f = Container::keyValue($test, 3, null);
			$actual = null;
			$message = 'success';
			$options = [
				TypeConversion::OPTION_FLAGS => $flags
			];
			if ($f !== null)
				$options[TypeConversion::OPTION_FALLBACK] = $f;
			try
			{
				$actual = TypeConversion::to($type, $value, $options);
			}
			catch (\Exception $e)
			{
				$message = $e->getMessage();
			}

			$this->assertEquals('success', $message,
				'Successful conversion from ' .
				TypeDescription::getName($value) . ' to ' . $type);

			if ($message == 'sucess')
			{
				$this->assertEquals($type,
					TypeDescription::getName($actual), 'Type name');
				$this->assertEquals($expected, $actual,
					'Converted value type');
			}
		}
	}
}
