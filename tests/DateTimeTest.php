<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\DateTime;
use NoreSources\DateTimeZone;
use NoreSources\Container\Container;
use NoreSources\Type\TypeConversion;

final class DateTimeTest extends \PHPUnit\Framework\TestCase
{

	public function testConstructorBehavior()
	{
		$utc = new \DateTimeZone('UTC');
		$berlin = new \DateTimeZone('Europe/Berlin');
		$tokyo = new \DateTimeZone('Asia/Tokyo');
		$system = new \DateTimeZone(\date_default_timezone_get());

		$now = new \DateTime('now', $utc);
		$this->assertEquals($utc->getName(),
			$now->getTimezone()
				->getName(), 'Now time zone');

		$nowAtTokyo = clone $now;
		$nowAtTokyo->setTimezone($tokyo);
		$tokyoStringOffset = $nowAtTokyo->format('P');

		$tests = [
			'default' => [
				'arguments' => [],
				'timezone' => $system
			],
			'now with time zone' => [
				'arguments' => [
					'now',
					$tokyo
				],
				'timezone' => $tokyo
			],
			'"now"' => [
				'arguments' => [
					'now'
				],
				'timezone' => $system
			],
			'"now" with time zone' => [
				'arguments' => [
					"now",
					$tokyo
				],
				'timezone' => $tokyo
			],
			'Epoch' => [
				'arguments' => [
					'@0'
				],
				'timezone' => $system
			],
			'Epoch with time zone' => [
				'arguments' => [
					'@0',
					$tokyo
				],
				'timezone' => $utc
			],
			'datetime' => [
				'arguments' => [
					'2010-11-12T13:14:15'
				],
				'timezone' => $system
			],
			'datetime with time zone' => [
				'arguments' => [
					'2010-11-12T13:14:15',
					$tokyo
				],
				'timezone' => $tokyo
			],
			'timestamp' => [
				'arguments' => [
					'2010-11-12T13:14:15' . $tokyoStringOffset
				],
				'timezone' => $tokyo
			],
			'timestamp with (another) time zone' => [
				'arguments' => [
					'2010-11-12T13:14:15' . $tokyoStringOffset,
					$berlin
				],
				'timezone' => $tokyo
			]
		];

		$builtinClass = new \ReflectionClass(\DateTime::class);
		$outClass = new \ReflectionClass(DateTime::class);

		foreach ($tests as $label => $test)
		{
			$args = Container::keyValue($test, 'arguments');

			$this->assertEquals('array', gettype($args),
				$label . ' constructor arguments');

			/** @var \DateTimeZone $timezone */
			$timezone = Container::keyValue($test, 'timezone');

			foreach ([
				'built-in' => $builtinClass->newInstanceArgs($args),
				'ours' => $outClass->newInstanceArgs($args)
			] as $type => $value)
			{
				$this->assertInstanceOf(\DateTimeInterface::class,
					$value);

				$valueTimezone = $value->getTimezone();

				$this->assertEquals($timezone->getOffset($now),
					$valueTimezone->getOffset($now),
					$type . ' ' . $label . ' | Time zone offset (' .
					$timezone->getName() . ')');
			}
		}
	}

	public function testFromInteger()
	{
		$tz = new \DateTimeZone('Europe/Berlin');
		$arbitraryDate = new \DateTime('910-11-12T13:14:15+0400');
		$tests = [
			'epoch' => [
				'int' => 0,
				'text' => new \DateTime('@0')
			],
			'arbitrary date' => [
				'int' => $arbitraryDate->getTimestamp(),
				'text' => $arbitraryDate
			]
		];

		foreach ($tests as $label => $test)
		{
			$test = (object) $test;
			if ($test->text instanceof \DateTime)
			{
				$test->text->setTimezone($tz);
				$test->text = $test->text->format(\DateTime::ISO8601);
			}

			$dt = new DateTime($test->int);
			$dt->setTimezone($tz);
			$this->assertEquals($test->text,
				$dt->format(\DateTime::ISO8601), $label);
		}
	}

	public function testFromArray()
	{
		$tz = new \DateTimeZone('Europe/Berlin');

		foreach ([
			'2019-09-07 12:13:14.500000+0200',
			'now'
		] as $time)
		{
			$dateTime = new \DateTime($time, $tz);
			$exported = \json_decode(\json_encode($dateTime), true);
			$this->assertTrue(\is_array($exported),
				$time . ' - DateTime to array');
			$this->assertCount(3, $exported);
			$this->assertArrayHasKey('date', $exported);
			$this->assertArrayHasKey('timezone', $exported);
			$this->assertArrayHasKey('timezone_type', $exported);

			$fromArray = DateTime::createFromArray($exported);
			$this->assertEquals($dateTime->format(\DateTime::ISO8601),
				$fromArray->format(\DateTime::ISO8601),
				$time . ' - From array');
		}
	}

	public function testToArray()
	{
		$timezones = [
			new \DateTimeZone('Asia/Tokyo'),
			new \DateTimeZone('Europe/Berlin')
		];

		$datetimes = [
			'@0',
			'2010-11-12T13:14:15.654321 +04:30'
		];

		foreach ($datetimes as $stringValue)
		{
			foreach ($timezones as $tz)
			{
				$builtin = new \DateTime($stringValue, $tz);
				$ns = new DateTime($stringValue, $tz);

				$expected = var_export($builtin, true);
				$expected = explode("\n", $expected);
				$expected = implode("\n", array_slice($expected, 1, 3));
				$expected = preg_replace('/[ \t]*(.*)/', '\1', $expected);
				$actual = var_export($ns->getArrayCopy(), true);
				$actual = explode("\n", $actual);
				$actual = implode("\n", array_slice($actual, 1, 3));
				$actual = preg_replace('/[ \t]*(.*)/', '\1', $actual);

				$this->assertEquals($expected, $actual,
					$stringValue . ' ' . $tz->getName());
			}
		}
	}

	public function testToJulian()
	{
		if (!\extension_loaded('calendar'))
			return $this->assertFalse(\extension_loaded('calendar'),
				'Cannot test this without calendar extension');

		$timezones = [
			'UTC' => DateTIme::getUTCTimezone(),
			'Europe/Berlin' => new \DateTimeZone('Europe/Berlin')
		];
		$tests = [
			'epoch' => [
				'timestamp' => '1970-01-01T12:00:00+0000',
				'jdn' => \gregoriantojd(1, 1, 1970)
			],
			'wikipedia example' => [
				'timestamp' => '2000-01-01T18:00:00+0000',
				'jdn' => \gregoriantojd(1, 1, 2000),
				'jd' => 2451545.25
			],
			'wikipedia example 6 hours later' => [
				'timestamp' => '2000-01-02T00:00:00+0000',
				'jdn' => \gregoriantojd(1, 2, 2000),
				'jd' => 2451545.50
			],
			'wikipedia example 12 hours later' => [
				'timestamp' => '2000-01-02T06:00:00+0000',
				'jdn' => \gregoriantojd(1, 2, 2000),
				'jd' => 2451545.75
			],
			'julian period' => [
				'timestamp' => '-4713-11-24T12:00:00+0000',
				'jd' => 0,
				'jdn' => 0
			]
		];

		foreach ($tests as $label => $test)
		{
			$dt = new DateTime($test['timestamp'],
				DateTime::getUTCTimezone());

			foreach ($timezones as $tzName => $tz)
			{
				$dt->setTimezone($tz);
				if (Container::keyExists($test, 'jdn'))
				{
					$this->assertEquals($test['jdn'],
						$dt->getJulianDayNumber(),
						'Julian day number of ' . $label . ' ' .
						\strval($dt));
				}

				if (Container::keyExists($test, 'jd'))
				{
					$this->assertEquals($test['jd'], $dt->getJulianDay(),
						'Julian day of ' . $label . ' ' . \strval($dt));
				}

				$actual = new DateTime('now', $tz);
				if (Container::keyExists($test, 'jd'))
				{
					$actual->setJulianDay($test['jd']);
					$expected = new \DateTime($test['timestamp'], $tz);
					$this->assertEquals($expected, $actual,
						$label . ' DateTime from Julian day ' .
						$test['jd']);
				}
				elseif (Container::keyExists($test, 'jdn'))
				{
					$actual->setJulianDay($test['jdn']);
					$expected = new \DateTime($test['timestamp'], $tz);
					$this->assertEquals($expected, $actual,
						$label . ' DateTime from Julian day number ' .
						$test['jdn']);
				}
			}
		}

		$epoch = new DateTime('1970-01-01');
		$expected = \gregoriantojd(1, 1, 1970);
		$actual = $epoch->getJulianDayNumber();

		$this->assertEquals($expected, $actual,
			'Julian day number of ' . $epoch);

		$wikipediaExample = '2000-01-01T18:00:00+0000';
		$wikipediaExampleJulian = 2451545.25;

		$w = new DateTime($wikipediaExample);
		$expected = $wikipediaExampleJulian;
		$this->assertEquals($expected, $w->getJulianDay(),
			'Julian day of ' . $wikipediaExample);

		$expected_bis = gregoriantojd(1, 1, 2000);

		$this->assertEquals($expected_bis, $w->getJulianDayNumber(),
			'Julian day number of ' . $wikipediaExample);

		$w2 = new DateTime('2000-01-01T18:00:00-0600');
		$expected = $wikipediaExampleJulian + 0.25;
		$this->assertEquals($expected, $w2->getJulianDay(),
			'Julian day of ' . $w2);

		$fromJD = new DateTime('now', DateTIme::getUTCTimezone());
		$fromJD->setJulianDay($wikipediaExampleJulian);
		$this->assertEquals($wikipediaExample, \strval($fromJD),
			'From julian day');
	}

	public function testTimeZoneCreate()
	{
		$tests = [
			'UTC numeric' => 0,
			'UTC offset string with a leading minus' => '-00:00',
			'UTC offset string' => '00:00',
			'UTC+1 string (P)' => '+01:00',
			'UTC+1 string (O)' => '+0100',
			'UTC+1 name' => 'Europe/Berlin',
			'Offset description' => [
				'offset' => '+02:00'
			],
			'Offset description' => [
				'offset' => '+0200',
				'format' => 'O'
			],
			'Name description' => [
				'name' => 'Asia/Tokyo'
			]
		];

		foreach ($tests as $label => $description)
		{
			$timezone = null;
			try
			{
				$timezone = DateTimeZone::createFromDescription(
					$description);
			}
			catch (\Exception $e)
			{
				$timezone = $e->getMessage();
			}
			$this->assertInstanceOf(\DateTimeZone::class, $timezone,
				'Create from ' . $label);
		}
	}

	public function testTimezone()
	{
		$tests = [
			[
				'format' => 'P',
				'value' => '+01:00'
			],
			[
				'format' => 'O',
				'value' => '+0100'
			],
			[
				'value' => '+0000'
			],
			[
				'value' => '+00:00'
			]
		];

		foreach ($tests as $test)
		{
			$format = Container::keyValue($test, 'format');
			$value = $test['value'];

			if (\is_string($value))
			{
				$valueFormat = DateTimeZone::getOffsetFormat($value);
				if ($format)
					$this->assertEquals($format, $valueFormat,
						$value . ' format');
			}

			$timezone = DateTimeZone::createFromOffset($value, $format);
			$this->assertInstanceOf(\DateTimeZone::class, $timezone);

			if (\is_string($value) && $format)
			{
				$e = new DateTime('2010-11-12T13:14:15', $timezone);
				$e->setTimezone($timezone);
				$this->assertEquals($e->format($format), $value,
					'Convert time zone to string (using DateTime ' .
					$e->format(DateTime::ISO8601) . ')');
			}
		}

		$timezoneName = 'Europe/Paris';

		$timezone = new DateTimeZone($timezoneName);
		$matches = DateTimeZone::listMatchingOffsetTimezones($timezone,
			\DateTimeZone::EUROPE, 'FR');
		$matches = Container::map($matches,
			function ($k, $v) {
				return TypeConversion::toString($v);
			});

		$this->assertContains($timezoneName, $matches,
			'Time zone with the same offset');
	}
}
