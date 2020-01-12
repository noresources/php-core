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
			$this->assertInternalType('array', $exported, $time . ' - DateTime to array');
			$this->assertCount(3, $exported);
			$this->assertArrayHasKey('date', $exported);
			$this->assertArrayHasKey('timezone', $exported);
			$this->assertArrayHasKey('timezone_type', $exported);

			$fromArray = DateTime::createFromArray($exported);
			$this->assertEquals($dateTime->format(\DateTime::ISO8601),
				$fromArray->format(\DateTime::ISO8601), $time . ' - From array');
		}
	}
}
