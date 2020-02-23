<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\DateTime;

class Generator
{

	const TYPE_STRING = 0;

	const TYPE_INTEGER = 1;

	const TYPE_FLOAT = 2;

	/**
	 *
	 * @param unknown $fromType
	 * @return NULL|\NoreSources\DateTime
	 */
	public static function randomDateTime($fromType = self::TYPE_STRING)
	{
		$dt = null;
		if ($fromType == self::TYPE_INTEGER)
		{
			$s = (rand(0, 20) != 0) ? 1 : -1;
			$dt = new DateTime(rand() * $s);
		}
		elseif ($fromType = self::TYPE_FLOAT)
		{
			$s = (rand(0, 10) != 0) ? 1 : -1;
			$i = rand(1, 365) * rand(0, 16000);
			$f = rand(0, 86399) / 86400;

			$julianDay = ($i + $f) * $s;

			$dt = new DateTime($julianDay);
		}
		else
		{
			$y = rand(0, 4000) * ((rand(0, 10) != 0) ? 1 : -1);
			$m = rand(1, 12);
			$d = rand(1, 28);
			$h = rand(0, 23);
			$i = rand(0, 59);
			$s = rand(0, 59);

			$timestamp = sprintf('%d-%02d-%02dT%02d:%02d:%02d', $y, $m, $d, $h, $i, $s);

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

		$tz = new \DateTimeZone($zones[rand(0, \count($zones) - 1)]);
		$dt->setTimezone($tz);

		return $dt;
	}
}