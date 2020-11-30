<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

final class DateTimeTest extends \PHPUnit\Framework\TestCase
{

	public function testFromInteger()
	{
		$tz = new \DateTimeZone('Europe/Berlin');
		$arbitraryDate = new \DateTime('910-11-12T13:14:15+0400', $tz);
		$tests = [
			'epoch' => [
				'int' => 0,
				'text' => new \DateTime('@0', $tz)
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

			$dt = new DateTime($test->int, $tz);
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
			$exported = [];
			foreach ($dateTime as $key => $value)
				$exported[$key] = $value;
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

	public function testToString()
	{
		$dt = new DateTime('2010-11-12T13:14:15.654321 +04:30',
			new \DateTimeZone('Europe/Berlin'));
		$this->assertEquals('2010-11-12T13:14:15+0430', \strval($dt));
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
}
