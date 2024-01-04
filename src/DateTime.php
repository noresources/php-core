<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources;

use NoreSources\Container\Container;
use NoreSources\Type\ArrayRepresentation;
use NoreSources\Type\FloatRepresentation;
use NoreSources\Type\IntegerRepresentation;
use NoreSources\Type\StringRepresentation;

/**
 * the <code>[NoreSources\DateTime](NoreSources/DateTime)</code> class extends the built-in
 * <code>\DateTIme</code> class by adding more capabilities to be constructed ad exported to
 * various data types.
 */
class DateTime extends \DateTime implements IntegerRepresentation,
	FloatRepresentation, StringRepresentation, ArrayRepresentation
{

	/**
	 * Leap year indicator
	 */
	const FORMAT_YEAR_LEAP = 'L';

	/**
	 * ISO-8601 week-numbering year.
	 * This has the same value as Y, except that if the ISO week number (W) belongs to the previous
	 * or next year, that year is used instead.
	 */
	const FORMAT_YEAR_ISO8601 = 'o';

	/**
	 * A two digit representation of a year
	 */
	const FORMAT_YEAR_DIGIT_2 = 'y';

	/**
	 * A full numeric representation of a year, 4 digits
	 */
	const FORMAT_YEAR_NUMBER = 'Y';

	/**
	 * Day of the year, starting from zero.
	 */
	const FORMAT_YEAR_DAY_NUMBER = 'z';

	/**
	 * Abbreviated month name, based on the locale
	 */
	const FORMAT_MONTH_ALPHA_3 = 'M';

	/**
	 * Full month name, based on the locale
	 */
	const FORMAT_MONTH_NAME = 'F';

	/**
	 * Two digit representation of the month
	 */
	const FORMAT_MONTH_DIGIT_2 = 'm';

	/**
	 * Month number without leading zero
	 */
	const FORMAT_MONTH_NUMBER = 'n';

	/**
	 * Number of day in the current month
	 */
	const FORMAT_MONTH_DAY_COUNT = 't';

	/**
	 * A numeric representation of the week of the year, starting with the first Monday as the first
	 * week
	 */
	const FORMAT_WEEK_DIGIT_2 = 'W';

	/**
	 * ISO-8601 numeric representation of the day of the week.
	 *
	 * 1 = Monday
	 * 7 = Sunday
	 */
	const FORMAT_WEEK_DAY_ISO8601 = 'N';

	/**
	 * Numeric representation of the day of the week.
	 *
	 * 0 = Sunday
	 * 6 = Saturday
	 */
	const FORMAT_WEEK_DAY_NUMBER = 'w';

	/**
	 * English ordinal suffix for the day of the month, 2 character
	 */
	const FORMAT_WEEK_DAY_EN_ALPHA_2 = 'S';

	/**
	 * A textual representation of a day, three letters
	 */
	const FORMAT_DAY_ALPHA_3 = 'D';

	/**
	 * A full textual representation of the day
	 */
	const FORMAT_DAY_NAME = 'l';

	/**
	 * Two-digit day of the month (with leading zeros)
	 */
	const FORMAT_DAY_DIGIT_2 = 'd';

	/**
	 * Day of the month without leading zeros
	 */
	const FORMAT_DAY_NUMBER = 'j';

	/**
	 * Two digit representation of the hour in 24-hour format
	 */
	const FORMAT_HOUR_24_DIGIT_2 = 'H';

	/**
	 * Hour in 24-hour format, with a space preceding single digits
	 */
	const FORMAT_HOUR_24_PADDED = 'G';

	/**
	 * Two digit representation of the hour in 12-hour format
	 */
	const FORMAT_HOUR_12_DIGIT_2 = 'h';

	/**
	 * Hour in 12-hour format, with a space preceding single digits.
	 *
	 * 1 through 12
	 */
	const FORMAT_HOUR_12_PADDED = 'g';

	/**
	 * Swatch internet time
	 *
	 * 0 to 999
	 */
	const FORMAT_SWATCH_TIME = 'B';

	/**
	 * Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
	 *
	 * @var string
	 */
	const FORMAT_EPOCH_OFFSET = 'U';

	/**
	 * UPPER-CASE 'AM' or 'PM' based on the given time
	 */
	const FORMAT_HOUR_AM_UPPERCASE = 'A';

	/**
	 * lower-case 'am' or 'pm' based on the given time
	 */
	const FORMAT_HOUR_AM_LOWERCASE = 'a';

	/**
	 * Two digit representation of the minute.
	 *
	 * 00 through 59
	 */
	const FORMAT_MINUTE_DIGIT_2 = 'i';

	/**
	 * Two digit representation of the second.
	 *
	 * 00 through 59
	 */
	const FORMAT_SECOND_DIGIT_2 = 's';

	/**
	 * Millisecondes
	 */
	const FORMAT_MILLISECOND = 'v';

	/**
	 * Microsecondes
	 */
	const FORMAT_MICROSECOND = 'u';

	/**
	 * Timezone offset in seconds
	 */
	const FORMAT_TIMEZONE_OFFSET = 'Z';

	/**
	 * GMT offset (colon)
	 */
	const FORMAT_TIMEZONE_GMT_OFFSET_COLON = 'P';

	/**
	 * GMT offset (colon) or Z for UTC offset
	 * PHP 8+
	 *
	 * @var string
	 */
	const FORMAT_TIMEZONE_GMT_OFFSET_COLON_Z = 'p';

	/**
	 * GMT offset
	 */
	const FORMAT_TIMEZONE_GMT_OFFSET = 'O';

	/**
	 * Time zone identifier
	 *
	 * Ex: Europe/Berlin
	 */
	const FORMAT_TIMEZONE_NAME = 'e';

	/**
	 * Daylight indicator
	 *
	 * 0 or 1
	 */
	const FORMAT_TIMEZONE_DST = 'I';

	/**
	 * The time zone abbreviation
	 */
	const FORMAT_TIMEZONE_ALPHA_3 = 'T';

	/**
	 * ISO 8601 date
	 *
	 * @var string
	 */
	const FORMAT_TIMESTAMP_ISO8601 = 'c';

	/**
	 * RFC 2822 formatted date
	 *
	 * @var string
	 */
	const FORMAT_TIMESTAMP_RFC2822 = 'r';

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
	 *        	Time zone hint.
	 *        	Use this time zone ONLY if the $time does not provide time zone information
	 */
	public function __construct($time = null,
		\DateTimeZone $timezone = null)
	{
		if (\is_integer($time))
			parent::__construct('@' . $time);
		elseif (\is_float($time))
		{
			parent::__construct('@0');
			$this->setJulianDay($time);
		}
		else
			parent::__construct(($time === NULL) ? 'now' : $time,
				$timezone);
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
	public static function toJulianDayNumber(
		\DateTimeInterface $dateTime, $utc = true)
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
				(3 * (($Y + 4900 + ($M - 14) / 12) / 100)) / 4 + $D -
				32075));
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
	public static function toJulianDay(\DateTimeInterface $dateTime,
		$utc = true)
	{
		if ($dateTime->getOffset() != 0)
		{
			$dateTime = clone $dateTime;
			$dateTime->setTimezone(self::getUTCTimezone());
		}

		$hour = \floatval(
			$dateTime->format(self::FORMAT_HOUR_24_DIGIT_2));
		$minute = \floatval(
			$dateTime->format(self::FORMAT_MINUTE_DIGIT_2));
		$seconds = \floatval(
			$dateTime->format(self::FORMAT_SECOND_DIGIT_2));
		$jd = \floatval(self::toJulianDayNumber($dateTime));

		return $jd + ($hour - 12.0) / 24.0 + $minute / 1440.0 +
			$seconds / 86400.0;
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

		$this->setDate(-4713, 11, 24);
		$this->setTime(12, 0, 0);

		$interval = 'P' . abs($jdn) . 'DT' . $s . 'S';
		$interval = new \DateInterval($interval);
		if ($jdn < 0)
			$interval->invert = true;
		$this->add($interval);

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
			if ($instance instanceof \DateTimeInterface)
				$timezone = $instance->getTimezone();
		}
		elseif (Container::keyExists($array, 'format') &&
			Container::keyExists($array, 'time'))
		{
			$timezone = Container::keyValue($array, 'timezone', null);
			if ($timezone)
				$timezone = \DateTimeZone::createFromDescription(
					$timezone);

			$instance = \DateTime::createFromFormat(
				Container::keyValue($array, 'format'),
				Container::keyValue($array, 'time'), $timezone);
		}

		if ($instance instanceof \DateTimeInterface)
		{
			if ($baseClass || ($instance instanceof DateTime))
				return $instance;

			return new DateTime($instance->format(\DateTime::ISO8601),
				$timezone);
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
	const FORMAT_DESCRIPTION_LABEL = 'label';

	/**
	 * Timestamp format token value range
	 *
	 * @var string
	 */
	const FORMAT_DESCRIPTION_RANGE = 'range';

	/**
	 * Strict equivalent format token for the strftime() function
	 *
	 * @var string
	 */
	const FORMAT_DESCRIPTION_STRFTIME = 'strftime';

	/**
	 * Timestamp format token additional informations
	 *
	 * @var string
	 */
	const FORMAT_DESCRIPTION_DETAILS = 'details';

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
					self::FORMAT_YEAR_LEAP => [
						self::FORMAT_DESCRIPTION_LABEL => 'Leap year indicator',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							1
						]
					],
					self::FORMAT_YEAR_ISO8601 => [
						self::FORMAT_DESCRIPTION_LABEL => 'ISO-8601 week-numbering year',
						self::FORMAT_DESCRIPTION_DETAILS => 'Same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead',
						self::FORMAT_DESCRIPTION_STRFTIME => '%G'
					],
					self::FORMAT_YEAR_NUMBER => [
						self::FORMAT_DESCRIPTION_LABEL => 'Year',
						self::FORMAT_DESCRIPTION_STRFTIME => '%Y'
					],
					self::FORMAT_YEAR_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => '2-letters Year',
						self::FORMAT_DESCRIPTION_STRFTIME => '%y'
					],

					// Week

					self::FORMAT_WEEK_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => 'ISO 8601 Week number of the year',
						self::FORMAT_DESCRIPTION_DETAILS => 'Starting on Monday',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							53
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%W'
					],

					// Month

					self::FORMAT_MONTH_NAME => [
						self::FORMAT_DESCRIPTION_LABEL => 'Month name',
						self::FORMAT_DESCRIPTION_STRFTIME => '%B'
					],
					self::FORMAT_MONTH_ALPHA_3 => [
						self::FORMAT_DESCRIPTION_LABEL => '3-letters month name',
						self::FORMAT_DESCRIPTION_STRFTIME => '%b'
					],
					self::FORMAT_MONTH_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => 'Month number of the year',
						self::FORMAT_DESCRIPTION_RANGE => [
							1,
							12
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%m'
					],
					self::FORMAT_MONTH_NUMBER => [
						self::FORMAT_DESCRIPTION_LABEL => 'Month number of the year',
						self::FORMAT_DESCRIPTION_DETAILS => 'Without leading zero',
						self::FORMAT_DESCRIPTION_RANGE => [
							1,
							12
						]
					],
					self::FORMAT_MONTH_DAY_COUNT => [
						self::FORMAT_DESCRIPTION_LABEL => 'Number of days in the month',
						self::FORMAT_DESCRIPTION_RANGE => [
							28,
							31
						]
					],

					// Day

					self::FORMAT_DAY_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => 'Day number of the month',
						self::FORMAT_DESCRIPTION_RANGE => [
							1,
							31
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%d'
					],
					'D' => [
						self::FORMAT_DESCRIPTION_LABEL => '3-letters day name'
					],
					self::FORMAT_DAY_NUMBER => [
						self::FORMAT_DESCRIPTION_LABEL => 'Day number of month',
						self::FORMAT_DESCRIPTION_DETAILS => 'Without leading zero',
						self::FORMAT_DESCRIPTION_RANGE => [
							1,
							31
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%e'
					],
					self::FORMAT_WEEK_DAY_EN_ALPHA_2 => [
						self::FORMAT_DESCRIPTION_LABEL => 'English ordinal suffix for the day of the month, 2 character'
					],
					self::FORMAT_DAY_NAME => [
						self::FORMAT_DESCRIPTION_LABEL => 'Day name',
						self::FORMAT_DESCRIPTION_STRFTIME => '%A'
					],
					self::FORMAT_WEEK_DAY_ISO8601 => [
						self::FORMAT_DESCRIPTION_LABEL => 'ISO 8601 day number of the week',
						self::FORMAT_DESCRIPTION_DETAILS => 'From Monday to Sunday',
						self::FORMAT_DESCRIPTION_RANGE => [
							1,
							7
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%u'
					],
					self::FORMAT_WEEK_DAY_NUMBER => [
						self::FORMAT_DESCRIPTION_LABEL => '2-letters english day name'
					],
					self::FORMAT_WEEK_DAY_NUMBER => [
						self::FORMAT_DESCRIPTION_LABEL => 'Day number of the week',
						self::FORMAT_DESCRIPTION_DETAILS => 'From Sunday to Saturday',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							6
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%w'
					],
					self::FORMAT_YEAR_DAY_NUMBER => [
						self::FORMAT_DESCRIPTION_LABEL => 'Day number of the year',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							365
						]
					],

					// Hour

					self::FORMAT_HOUR_AM_LOWERCASE => [
						self::FORMAT_DESCRIPTION_LABEL => 'Ante meridiem and Post meridiem',
						self::FORMAT_DESCRIPTION_DETAILS => 'Lowercase',
						self::FORMAT_DESCRIPTION_STRFTIME => '%P'
					],
					self::FORMAT_HOUR_AM_UPPERCASE => [
						self::FORMAT_DESCRIPTION_LABEL => 'Ante meridiem and Post meridiem',
						self::FORMAT_DESCRIPTION_DETAILS => 'Uppercase',
						self::FORMAT_DESCRIPTION_STRFTIME => '%p'
					],

					self::FORMAT_HOUR_12_PADDED => [
						self::FORMAT_DESCRIPTION_LABEL => '12-hour day hour',
						self::FORMAT_DESCRIPTION_DETAILS => 'Without leading zero',
						self::FORMAT_DESCRIPTION_RANGE => [
							1,
							12
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%l'
					],
					self::FORMAT_HOUR_24_PADDED => [
						self::FORMAT_DESCRIPTION_LABEL => '24-hour day hour',
						self::FORMAT_DESCRIPTION_DETAILS => 'Without leading zero',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							23
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%k'
					],
					self::FORMAT_HOUR_12_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => '12-hour day hour',
						self::FORMAT_DESCRIPTION_RANGE => [
							1,
							12
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%I'
					],
					self::FORMAT_HOUR_24_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => '24-hour day hour',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							23
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%H'
					],

					// Minutes

					self::FORMAT_MINUTE_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => 'Minutes',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							56
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%M'
					],

					// Seconds

					self::FORMAT_SECOND_DIGIT_2 => [
						self::FORMAT_DESCRIPTION_LABEL => 'Seconds',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							56
						],
						self::FORMAT_DESCRIPTION_STRFTIME => '%S'
					],
					self::FORMAT_MILLISECOND => [
						self::FORMAT_DESCRIPTION_LABEL => 'Milliseconds',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							999
						]
					],
					self::FORMAT_MICROSECOND => [
						self::FORMAT_DESCRIPTION_LABEL => 'Microseconds',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							999999
						]
					],

					// Time zone

					self::FORMAT_TIMEZONE_DST => [
						self::FORMAT_DESCRIPTION_LABEL => 'Daylight saving time indicator',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							1
						]
					],

					self::FORMAT_TIMEZONE_NAME => [
						self::FORMAT_DESCRIPTION_LABEL => 'Timezone identifier'
					],

					self::FORMAT_TIMEZONE_GMT_OFFSET => [
						self::FORMAT_DESCRIPTION_LABEL => 'GMT offset',
						self::FORMAT_DESCRIPTION_DETAILS => 'Without colon separator'
					],
					self::FORMAT_TIMEZONE_GMT_OFFSET_COLON => [
						self::FORMAT_DESCRIPTION_LABEL => 'GMT offset',
						self::FORMAT_DESCRIPTION_DETAILS => 'With colon separator'
					],

					self::FORMAT_TIMEZONE_ALPHA_3 => [
						self::FORMAT_DESCRIPTION_LABEL => 'Timezone abbreviation'
					],

					self::FORMAT_TIMEZONE_OFFSET => [
						self::FORMAT_DESCRIPTION_LABEL => 'Timezone offset in seconds',
						self::FORMAT_DESCRIPTION_STRFTIME => '%z'
					],

					// Shorthands

					self::FORMAT_TIMESTAMP_ISO8601 => [
						self::FORMAT_DESCRIPTION_LABEL => 'ISO 8601 date'
					],
					self::FORMAT_TIMESTAMP_RFC2822 => [
						self::FORMAT_DESCRIPTION_LABEL => 'RFC 2822 date'
					],

					// Misc

					self::FORMAT_SWATCH_TIME => [
						self::FORMAT_DESCRIPTION_LABEL => 'Swatch internet time',
						self::FORMAT_DESCRIPTION_RANGE => [
							0,
							999
						]
					],

					self::FORMAT_EPOCH_OFFSET => [
						self::FORMAT_DESCRIPTION_LABEL => 'Number of seconds since UNIX epoch',
						self::FORMAT_DESCRIPTION_DETAILS => 'Since January 1 1970 00:00:00 GM',
						self::FORMAT_DESCRIPTION_STRFTIME => '%s'
					]
				]);

			if (version_compare(PHP_VERSION, '8.0.0') >= 0)
			{
				self::$formatTokenDescriptions[self::FORMAT_TIMEZONE_GMT_OFFSET_COLON_Z] = [
					self::FORMAT_DESCRIPTION_LABEL => 'GMT offset',
					self::FORMAT_DESCRIPTION_DETAILS => 'With colon separator or "Z" for UTC'
				];
			}
		}

		if (\is_string($token))
			return Container::keyValue(self::$formatTokenDescriptions,
				$token, false);

		return self::$formatTokenDescriptions;
	}

	/**
	 *
	 * @return \DateTimeZone
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