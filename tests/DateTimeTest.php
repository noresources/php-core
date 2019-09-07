<?php

namespace NoreSources;

use PHPUnit\Framework\TestCase;

final class DateTimeTest extends TestCase
{

	public function testFromArray()
	{
		$tz = new \DateTimeZone('Europe/Berlin');
		
		foreach (['2019-09-07 12:13:14.500000+0200', 'now'] as $time)
		{
			$dateTime = new \DateTime($time, $tz);

			$exported = Container::createArray($dateTime);
			$this->assertInternalType('array', $exported, $time . ' - DateTime to array');
			$this->assertCount(3, $exported);
			$this->assertArrayHasKey('date', $exported);
			$this->assertArrayHasKey('timezone', $exported);
			$this->assertArrayHasKey('timezone_type', $exported);

			$fromArray = DateTime::createFromArray($exported);
			$this->assertEquals($dateTime->format(\DateTime::ISO8601), $fromArray->format(\DateTime::ISO8601), $time . ' - From array');

			$ctor = new DateTime($exported);
			$this->assertEquals($dateTime->format(\DateTime::ISO8601), $ctor->format(\DateTime::ISO8601), $time . ' - Constructor');
		}
	}
}
