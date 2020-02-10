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

final class DateTimeTest extends \PHPUnit\Framework\TestCase
{

	public function testFromArray()
	{
		$tz = new \DateTimeZone('Europe/Berlin');

		foreach ([
			'2019-09-07 12:13:14.500000+0200',
			'now'
		] as $time)
		{
			$dateTime = new \DateTime($time, $tz);

			$exported = Container::createArray($dateTime);
			$this->assertIsArray($exported, $time . ' - DateTime to array');
			$this->assertCount(3, $exported);
			$this->assertArrayHasKey('date', $exported);
			$this->assertArrayHasKey('timezone', $exported);
			$this->assertArrayHasKey('timezone_type', $exported);

			$fromArray = DateTime::createFromArray($exported);
			$this->assertEquals($dateTime->format(\DateTime::ISO8601),
				$fromArray->format(\DateTime::ISO8601), $time . ' - From array');
		}
	}

	public function testToString()
	{
		$dt = new DateTime('2010-11-12T13:14:15.654321 +04:30', new \DateTimeZone('Europe/Berlin'));
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

				$this->assertEquals($expected, $actual, $stringValue . ' ' . $tz->getName());
			}
		}
	}
}
