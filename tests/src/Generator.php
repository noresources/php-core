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

class Generator
{

	const TYPE_STRING = 0;

	const TYPE_INTEGER = 1;

	const TYPE_FLOAT = 2;

	/**
	 * Generate a random date
	 *
	 * @param array $options
	 *        	* fromType (int) : generate date time from integer, float or string timestamp
	 *        	* yearRange (array): Year range
	 *
	 * @return NULL|\NoreSources\DateTime
	 */
	public static function randomDateTime($options = array())
	{
		$fromType = Container::keyValue($options, 'fromType',
			rand(self::TYPE_STRING, self::TYPE_FLOAT));
		$yearRange = Container::keyValue($options, 'yearRange',
			[
				// -400, // Death of Socrates
				0,
				2123
			]);

		$timezone = Container::keyValue($options, 'timezone', null);

		$dt = null;
		if ($fromType == self::TYPE_INTEGER)
		{
			$s = (rand(0, 20) != 0) ? 1 : -1;
			$dt = new DateTime(rand() * $s);
		}
		elseif ($fromType == self::TYPE_FLOAT)
		{
			$s = (rand(0, 10) != 0) ? 1 : -1;
			$i = rand(1, 365) * rand(0, 16000);
			$f = rand(0, 86399) / 86400;

			$julianDay = ($i + $f) * $s;

			$dt = new DateTime($julianDay);
		}
		else
		{
			$y = rand($yearRange[0], $yearRange[1]);
			$m = rand(1, 12);
			$d = rand(1, 28);
			$h = rand(0, 23);
			$i = rand(0, 59);
			$s = rand(0, 59);

			$timestamp = sprintf('%d-%02d-%02dT%02d:%02d:%02d', $y, $m,
				$d, $h, $i, $s);

			$dt = new DateTIme($timestamp);
		}

		$zones = [
			'UTC',
			'Africa/Bamako',
			'America/Aruba',
			'Europe/Berlin',
			'Asia/Tokyo',
			'Indian/Mayotte'
		];

		$y = \intval($dt->format('Y'));
		$range = $yearRange[1] - $yearRange[0];
		if ($y < $yearRange[0])
		{
			$offset = \abs($yearRange[0] - $y) + rand(0, $range / 2);
			$interval = new \DateInterval('P' . $offset . 'Y');
			$dt->add($interval);
		}

		if ($y > $yearRange[1])
		{
			$offset = $y - $yearRange[1] + rand(0, $range / 2);
			$interval = new \DateInterval('P' . $offset . 'Y');
			$interval->invert = true;
			$dt->add($interval);
		}

		if (!($timezone instanceof \DateTimeZone))
			$timezone = new \DateTimeZone(
				$zones[rand(0, \count($zones) - 1)]);
		$dt->setTimezone($timezone);

		return $dt;
	}
}