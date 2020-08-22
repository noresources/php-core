<?php

/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

use DateTimeZone;

/**
 * the <code>[NoreSources\DateTime](NoreSources/DateTime)</code> class extends the built-in
 * <code>\DateTIme</code> class by adding more capabilities to be constructed ad exported to
 * various data types.
 */
class DateTime extends \DateTime implements IntegerRepresentation, FloatRepresentation,
	StringRepresentation, ArrayRepresentation
{

	/**
	 *
	 * @param string|integer|float $time
	 *        	Timestamp
	 *        	<ul>
	 *        	<li>string</li> A \DateTime::_construct compatible timestamp format
	 *        	<li>integer</li> UNIX time since ephoc
	 *        	<li>float</li> Julian day
	 *        	</ul>
	 * @param \DateTimeZone $timezone
	 */
	public function __construct($time = null, \DateTimeZone $timezone = null)
	{
		if (\is_integer($time))
		{
			parent::__construct('@' . $time, $timezone);
			if ($timezone instanceof \DateTimeZone)
				$this->setTimezone($timezone);
		}
		elseif (\is_float($time))
		{
			parent::__construct('@0', $timezone);
			$this->setJulianDay($time);
			if ($timezone instanceof \DateTimeZone)
				$this->setTimezone($timezone);
		}
		else
			parent::__construct($time, $timezone);
	}

	/**
	 *
	 * @return string ISO 8601 timestamp
	 */
	public function __toString()
	{
		return $this->format(self::ISO8601);
	}

	/**
	 * Julian day representation of the given DateTime
	 *
	 * @param \DateTimeInterface $dateTime
	 *        	DateTime to convert
	 * @param boolean $utc
	 *        	Indicates if the DateTime time zone must be converted to UTC before calculating
	 *        	the julian day number.
	 * @return integer Julian day number
	 *
	 * @see https://en.wikipedia.org/wiki/Julian_day#Converting_Gregorian_calendar_date_to_Julian_Day_Number
	 */
	public static function toJulianDayNumber(\DateTimeInterface $dateTime, $utc = true)
	{
		if ($utc && ($dateTime->getOffset() != 0))
		{
			$dateTime = clone $dateTime;
			$dateTime->setTimezone(self::getUTCTimezone());
		}

		$Y = intval($dateTime->format('Y'));
		$M = intval($dateTime->format('m'));
		$D = intval($dateTime->format('d'));

		return \intval(
			\floor(
				(1461 * ($Y + 4800 + ($M - 14) / 12)) / 4 +
				(367 * ($M - 2 - 12 * (($M - 14) / 12))) / 12 -
				(3 * (($Y + 4900 + ($M - 14) / 12) / 100)) / 4 + $D - 32075));
	}

	/**
	 * Julian day representation of the given DateTime
	 *
	 * @param boolean $utc
	 *        	Indicates if the DateTime time zone must be converted to UTC before calculating
	 *        	the julian day number.
	 *
	 * @return integer Julian day number
	 *
	 * @see https://en.wikipedia.org/wiki/Julian_day#Converting_Gregorian_calendar_date_to_Julian_Day_Number
	 */
	public function getJulianDayNumber($utc = true)
	{
		return self::toJulianDayNumber($this, $utc);
	}

	/**
	 * Full julian day and time representation of the given DateTime
	 *
	 * @param \DateTimeInterface $dateTime
	 *        	DateTime to convert
	 * @param boolean $utc
	 *        	Indicates if the DateTime time zone must be converted to UTC before calculating
	 *        	the julian day number.
	 *
	 * @return number Julian day number and time
	 *
	 * @see https://en.wikipedia.org/wiki/Julian_day#Finding_Julian_date_given_Julian_day_number_and_time_of_day
	 */
	public static function toJulianDay(\DateTimeInterface $dateTime, $utc = true)
	{
		if ($dateTime->getOffset() != 0)
		{
			$dateTime = clone $dateTime;
			$dateTime->setTimezone(self::getUTCTimezone());
		}

		$hour = \floatval($dateTime->format('H'));
		$minute = \floatval($dateTime->format('i'));
		$seconds = \floatval($dateTime->format('s'));
		$jd = \floatval(self::toJulianDayNumber($dateTime));

		return $jd + ($hour - 12.0) / 24.0 + $minute / 1440.0 + $seconds / 86400.0;
	}

	/**
	 * Full julian day and time representation of the given DateTime
	 *
	 * @param boolean $utc
	 *        	Indicates if the DateTime time zone must be converted to UTC before calculating
	 *        	the julian day number.
	 *
	 * @return number Julian day number and time
	 *
	 * @see https://en.wikipedia.org/wiki/Julian_day#Finding_Julian_date_given_Julian_day_number_and_time_of_day
	 */
	public function getJulianDay()
	{
		return self::toJulianDay($this);
	}

	/**
	 * Set the date and time from a julian day time
	 *
	 * @param float $jd
	 *        	Julian day value
	 *        	Julian day
	 */
	public function setJulianDay($jd)
	{
		$tz = clone $this->getTimezone();
		$this->setTimezone(self::getUTCTimezone());

		$jdn = \intval(\floor($jd));
		$s = \floor(($jd - $jdn) * (60 * 60 * 24));

		if (1) // Method 1 using interval shifting
		{
			$this->setDate(-4713, 11, 24);
			$this->setTime(12, 0, 0);

			$interval = 'P' . abs($jdn) . 'DT' . $s . 'S';
			$interval = new \DateInterval($interval);
			if ($jdn < 0)
				$interval->invert = true;
			$this->add($interval);
		}
		else // Using Richards algotithm
		{
			$y = 4716;
			$j = 1401;
			$m = 2;
			$n = 12;
			$r = 4;
			$p = 1461;
			$v = 3;
			$u = 5;
			$s = 153;
			$w = 2;
			$B = 274277;
			$C = -38;

			$J = $jd;

			// For gregorian calendar
			// $f = $J + $j + \floor((\floor((4 * $J + $B) / 146097) * 3)) / 4 + $C;
			$f = $J + $j;

			// Common part
			$e = $r * $f + $v;
			$g = \floor(($e % $p) / $r);
			$h = $u * $g + $w;
			$D = \floor(($h % $s) / $u) + 1;
			$M = ((\floor($h / $s) + $m) % $n) + 1;
			$Y = \floor($e / $p) - $y + ($n + $m - $M);

			$this->setDate($Y, $M, $D);
			$this->setTime(12, 0, 0);

			$interval = 'PT' . $s . 'S';
			$interval = new \DateInterval($interval);
			$this->add($interval);
		}

		if ($tz instanceof \DateTimeZone)
			$this->setTimezone($tz);
	}

	/**
	 * Timestamp data informations
	 *
	 * @return array Timestamp data in the same format than var_export / var_dump output.
	 */
	public function getArrayCopy()
	{
		$tz = $this->getTimezone()->getName();
		$type = 2;
		if (\preg_match('/(\+|-)[0-9]{2}(,?[0-9]{2})/', $tz))
			$type = 1;
		elseif (\preg_match(',[a-zA-Z]+/[a-zA-Z]+,', $tz))
			$type = 3;

		return [
			'date' => $this->format('Y-m-d H:i:s.u'),
			'timezone_type' => 1,
			'timezone' => $tz
		];
	}

	/**
	 * Create a DateTime from a DateTime description array
	 *
	 * @param array $array
	 *        	An associative array with one of the following key/value pairs
	 *        	<ul>
	 *        	<li>DateTime state. The same array format output by var_export().
	 *        	<ul>
	 *        	<li>date: Timestamp without timezone information</li>
	 *        	<li>timezone_type: Timezone type
	 *        	<ol>
	 *        	<li>UTC offset (ex. +0100)</li>
	 *        	<li>Timezone abbreviation (ex. CET)</li>
	 *        	<li>Timezone identifier (@see
	 *        	https://www.php.net/manual/en/class.datetimezone.php)</li>
	 *        	</ol>
	 *        	</li>
	 *        	<li>timezone: Timezone value</li>
	 *        	</ul>
	 *        	</li>
	 *        	<li>DateTimestamp and format
	 *        	<ul>
	 *        	<li>time Timestamp</li>
	 *        	<li>format: Format string</li>
	 *        	<li>timezone: (optional) \DateTimeZone or valid timezone identifier</li>
	 *        	</ul>
	 *        	</li>
	 *        	</ul>
	 * @param boolean $baseClass
	 *        	Return a built-in \DateTime instance. Otherwise, return a \NoreSources\DateTime
	 *        	(with no
	 *        	particular benefits)
	 * @throws \InvalidArgumentException
	 * @return DateTime
	 *
	 * @ignore Fractional seconds are not supported
	 */
	public static function createFromArray($array, $baseClass = true)
	{
		$timezone = null;
		$instance = null;

		if (self::isDateTimeStateArray($array))
		{
			$instance = @\DateTime::__set_state($array);
			if ($instance instanceof \DateTime)
			{
				$timezone = $instance->getTimezone();
			}
		}
		elseif (Container::keyExists($array, 'format') && Container::keyExists($array, 'time'))
		{
			$timezone = Container::keyValue($array, 'timezone', null);
			if (\is_string($timezone))
			{
				$timezone = new \DateTimeZone($timezone);
			}

			$instance = \DateTime::createFromFormat(Container::keyValue($array, 'format'),
				Container::keyValue($array, 'time'), $timezone);
		}

		if ($instance instanceof \DateTime)
		{
			if ($baseClass)
				return $instance;

			return new DateTime($instance->format(\DateTime::ISO8601), $timezone);
		}

		throw new \InvalidArgumentException(
			var_export($array, true) . ' is not a valid DateTime array');
	}

	/**
	 * Indicate if the given array can be used to create a DateTime using DateTime::__set_state()
	 * magic method.
	 *
	 * @param array $array
	 * @return boolean
	 */
	public static function isDateTimeStateArray($array)
	{
		if (Container::isArray($array))
		{
			$stateKeys = [
				'date',
				'timezone',
				'timezone_type'
			];

			$c = 0;
			foreach ($stateKeys as $key)
			{
				if (Container::keyExists($array, $key))
					$c++;
			}

			return ($c == count($stateKeys));
		}

		return false;
	}

	/**
	 *
	 * @return integer UNIX time
	 */
	public function getIntegerValue()
	{
		return $this->getTimestamp();
	}

	/**
	 *
	 * @return float Julian day
	 */
	public function getFloatValue()
	{
		return $this->getJulianDay();
	}

	/**
	 * Timestamp format token label
	 *
	 * @var string
	 */
	const FORMAT_LABEL = 'label';

	/**
	 * Timestamp format token value range
	 *
	 * @var string
	 */
	const FORMAT_RANGE = 'range';

	/**
	 * Strict equivalent format token for the strftime() function
	 *
	 * @var string
	 */
	const FORMAT_STRFTIME = 'strftime';

	/**
	 * Timestamp format token additional informations
	 *
	 * @var string
	 */
	const FORMAT_DETAILS = 'details';

	/**
	 * Description of PHP date() function tokens
	 *
	 * @param string|null $token
	 *        	PHP date() token
	 * @return mixed|array|\ArrayAccess|\Psr\Container\ContainerInterface|\Traversable|array
	 */
	public static function getFormatTokenDescriptions($token = null)
	{
		if (!\is_array(self::$formatTokenDescriptions))
		{
			self::$formatTokenDescriptions = new \ArrayObject(
				[
					// Year
					'L' => [
						self::FORMAT_LABEL => 'Leap year indicator',
						self::FORMAT_RANGE => [
							0,
							1
						]
					],
					'o' => [
						self::FORMAT_LABEL => 'ISO-8601 week-numbering year',
						self::FORMAT_DETAILS => 'Same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead',
						self::FORMAT_STRFTIME => '%G'
					],
					'Y' => [
						self::FORMAT_LABEL => 'Year',
						self::FORMAT_STRFTIME => '%Y'
					],
					'y' => [
						self::FORMAT_LABEL => '2-letters Year',
						self::FORMAT_STRFTIME => '%y'
					],

					// Week

					'W' => [
						self::FORMAT_LABEL => 'ISO 8601 Week number of the year',
						self::FORMAT_DETAILS => 'Starting on Monday',
						self::FORMAT_RANGE => [
							0,
							53
						],
						self::FORMAT_STRFTIME => '%W'
					],

					// Month

					'F' => [
						self::FORMAT_LABEL => 'Month name',
						self::FORMAT_STRFTIME => '%B'
					],
					'M' => [
						self::FORMAT_LABEL => '3-letters month name',
						self::FORMAT_STRFTIME => '%b'
					],
					'm' => [
						self::FORMAT_LABEL => 'Month number of the year',
						self::FORMAT_RANGE => [
							1,
							12
						],
						self::FORMAT_STRFTIME => '%m'
					],
					'n' => [
						self::FORMAT_LABEL => 'Month number of the year',
						self::FORMAT_DETAILS => 'Without leading zero',
						self::FORMAT_RANGE => [
							1,
							12
						]
					],
					't' => [
						self::FORMAT_LABEL => 'Number of days in the month',
						self::FORMAT_RANGE => [
							28,
							31
						]
					],

					// Day

					'd' => [
						self::FORMAT_LABEL => 'Day number of the month',
						self::FORMAT_RANGE => [
							1,
							31
						],
						self::FORMAT_STRFTIME => '%d'
					],
					'D' => [
						self::FORMAT_LABEL => '3-letters day name'
					],
					'j' => [
						self::FORMAT_LABEL => 'Day number of month',
						self::FORMAT_DETAILS => 'Without leading zero',
						self::FORMAT_RANGE => [
							1,
							31
						],
						self::FORMAT_STRFTIME => '%e'
					],
					'l' => [
						self::FORMAT_LABEL => 'Day name',
						self::FORMAT_STRFTIME => '%A'
					],
					'N' => [
						self::FORMAT_LABEL => 'ISO 8601 day number of the week',
						self::FORMAT_DETAILS => 'From Monday to Sunday',
						self::FORMAT_RANGE => [
							1,
							7
						],
						self::FORMAT_STRFTIME => '%u'
					],
					'S' => [
						self::FORMAT_LABEL => '2-letters english day name'
					],
					'w' => [
						self::FORMAT_LABEL => 'Day number of the week',
						self::FORMAT_DETAILS => 'From Sunday to Saturday',
						self::FORMAT_RANGE => [
							0,
							6
						],
						self::FORMAT_STRFTIME => '%w'
					],
					'z' => [
						self::FORMAT_LABEL => 'Day number of the year',
						self::FORMAT_RANGE => [
							0,
							365
						]
					],

					// Hour

					'a' => [
						self::FORMAT_LABEL => 'Ante meridiem and Post meridiem',
						self::FORMAT_DETAILS => 'Lowercase',
						self::FORMAT_STRFTIME => '%P'
					],
					'A' => [
						self::FORMAT_LABEL => 'Ante meridiem and Post meridiem',
						self::FORMAT_DETAILS => 'Uppercase',
						self::FORMAT_STRFTIME => '%p'
					],

					'g' => [
						self::FORMAT_LABEL => '12-hour day hour',
						self::FORMAT_DETAILS => 'Without leading zero',
						self::FORMAT_RANGE => [
							1,
							12
						],
						self::FORMAT_STRFTIME => '%l'
					],
					'G' => [
						self::FORMAT_LABEL => '24-hour day hour',
						self::FORMAT_DETAILS => 'Without leading zero',
						self::FORMAT_RANGE => [
							0,
							23
						],
						self::FORMAT_STRFTIME => '%k'
					],
					'h' => [
						self::FORMAT_LABEL => '12-hour day hour',
						self::FORMAT_RANGE => [
							1,
							12
						],
						self::FORMAT_STRFTIME => '%I'
					],
					'H' => [
						self::FORMAT_LABEL => '24-hour day hour',
						self::FORMAT_RANGE => [
							0,
							23
						],
						self::FORMAT_STRFTIME => '%H'
					],

					// Minutes

					'i' => [
						self::FORMAT_LABEL => 'Minutes',
						self::FORMAT_RANGE => [
							0,
							56
						],
						self::FORMAT_STRFTIME => '%M'
					],

					// Seconds

					's' => [
						self::FORMAT_LABEL => 'Seconds',
						self::FORMAT_RANGE => [
							0,
							56
						],
						self::FORMAT_STRFTIME => '%S'
					],
					'v' => [
						self::FORMAT_LABEL => 'Milliseconds',
						self::FORMAT_RANGE => [
							0,
							999
						]
					],
					'u' => [
						self::FORMAT_LABEL => 'Microseconds',
						self::FORMAT_RANGE => [
							0,
							999999
						]
					],

					// Time zone

					'I' => [
						self::FORMAT_LABEL => 'Daylight saving time indicator',
						self::FORMAT_RANGE => [
							0,
							1
						]
					],

					'e' => [
						self::FORMAT_LABEL => 'Timezone identifier'
					],

					'O' => [
						self::FORMAT_LABEL => 'GMT offset',
						self::FORMAT_DETAILS => 'Without colon separator'
					],
					'P' => [
						self::FORMAT_LABEL => 'GMT offset'
					],

					'T' => [
						self::FORMAT_LABEL => 'Timezone abbreviation'
					],

					'Z' => [
						self::FORMAT_LABEL => 'Timezone offset in seconds',
						self::FORMAT_STRFTIME => '%z'
					],

					// Shorthands

					'c' => [
						self::FORMAT_LABEL => 'ISO 8601 date'
					],
					'r' => [
						self::FORMAT_LABEL => 'RFC 2822 date'
					],

					// Misc

					'B' => [
						self::FORMAT_LABEL => 'Swatch internet time',
						self::FORMAT_RANGE => [
							0,
							999
						]
					],

					'U' => [
						self::FORMAT_LABEL => 'Number of seconds since UNIX epoch',
						self::FORMAT_DETAILS => 'Since January 1 1970 00:00:00 GM',
						self::FORMAT_STRFTIME => '%s'
					]
				]);
		}

		if (\is_string($token))
			return Container::keyValue(self::$formatTokenDescriptions, $token, false);

		return self::$formatTokenDescriptions;
	}

	/**
	 *
	 * @return DateTimeZone
	 */
	public static function getUTCTimezone()
	{
		if (self::$utcTimezone instanceof \DateTimeZone)
			return self::$utcTimezone;

		self::$utcTimezone = new \DateTimeZone('UTC');
		return self::$utcTimezone;
	}

	/**
	 *
	 * @var \DateTimeZone
	 */
	private static $utcTimezone;

	/**
	 * DateTime formatting token description
	 *
	 * @var array[]
	 *
	 * @see https://www.php.net/manual/en/function.date.php
	 */
	private static $formatTokenDescriptions;
}