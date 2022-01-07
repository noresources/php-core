<?php
namespace NoreSources;

use NoreSources\Container\Container;

class DateTimeZone extends \DateTimeZone
{

	/**
	 * Get all time zone with the same UTC offset
	 *
	 * @param \DateTimeZone $timezone
	 * @param integer $timezoneGroup
	 *        	Time zone group.
	 * @param string $countryCode
	 *        	ISO 3166-1 compatible country code.
	 * @return \DateTimeZone[]
	 *
	 * @see https://www.php.net/manual/en/datetimezone.listidentifiers.php
	 */
	public static function listMatchingOffsetTimezones(
		\DateTimeZone $timezone, $timezoneGroup = \DateTimeZone::ALL,
		$countryCode = null)
	{
		$identifiers = self::listIdentifiers();
		$now = new \DateTime('now');
		$now->setTimezone(DateTime::getUTCTimezone());
		$offset = $timezone->getOffset($now);
		$matches = [];

		foreach ($identifiers as $identifier)
		{
			$subject = new \DateTimeZone($identifier);
			if ($subject->getOffset($now) == $offset)
				$matches[] = $subject;
		}

		return $matches;
	}

	const DESCRIPTION_OFFSET = 'offset';

	const DESCRIPTION_OFFSET_FORMAT = 'format';

	const DESCRIPTION_NAME = 'name';

	/**
	 * Create a DateTimeZone from a various kind of time zone description.
	 *
	 * @param string|number|array $description
	 *        	Time zone description. Could be
	 *        	<ul>
	 *        	<li>A numeric UTC offset</li>
	 *        	<li>A time zone name</li>
	 *        	<li>A time zone offset string (ex. +02:00)</li>
	 *        	<li>An array describing the time zone</li>
	 *        	</ul>
	 * @throws \InvalidArgumentException
	 * @return \DateTimeZone
	 */
	public static function createFromDescription($description)
	{
		if (\is_float($description) || \is_integer($description))
		{
			if ($description == 0)
				return clone DateTime::getUTCTimezone();
			$description = [
				self::DESCRIPTION_OFFSET => sprintf('%s%02d:%02d',
					(($description > 0) ? '+' : ''),
					\floor($description / 3600),
					(\floor($description) % 60)),
				self::DESCRIPTION_OFFSET_FORMAT => 'P'
			];
		}
		elseif (\is_string($description))
		{
			if (\preg_match(
				chr(1) . self::UTC_OFFSET_STRING_PATTERN . chr(1),
				$description))
				return clone DateTime::getUTCTimezone();

			try
			{
				$description = [
					self::DESCRIPTION_OFFSET => $description,
					self::DESCRIPTION_OFFSET_FORMAT => self::getOffsetFormat(
						$description)
				];
			}
			catch (\InvalidArgumentException $e)
			{
				return new \DateTimeZone($description);
			}
		}

		if (!Container::isArray($description))
			throw new \InvalidArgumentException(
				'string, number or description array expected.');

		if (($name = Container::keyValue($description,
			self::DESCRIPTION_NAME)))
			return new \DateTimeZone($name);

		return self::createFromOffset(
			Container::keyValue($description, self::DESCRIPTION_OFFSET),
			Container::keyValue($description,
				self::DESCRIPTION_OFFSET_FORMAT));
	}

	/**
	 *
	 * @param string|integer $value
	 *        	Time zone offset representation
	 * @param string|NULL $format
	 *        	Time zone offset string format
	 * @return \DateTimeZone
	 */
	public static function createFromOffset($value, $format = null)
	{
		if (\is_float($value) || \is_integer($value))
		{
			if ($value == 0)
				return clone DateTime::getUTCTimezone();
			$value = sprintf("%s%02d:%02d", (($value > 0) ? '+' : ''),
				\floor($value / 3600), ($value % 60));
		}

		if (\preg_match(
			chr(1) . self::UTC_OFFSET_STRING_PATTERN . chr(1), $value))
			return clone DateTime::getUTCTimezone();

		if (!$format)
			$format = self::getOffsetFormat($value);

		$d = DateTime::createFromFormat($format, $value);
		if (!($d instanceof \DateTimeInterface))
		{
			throw new \ErrorException('Failed to parse time zone');
		}
		return $d->getTimezone();
	}

	/**
	 *
	 * @param string $value
	 * @throws \InvalidArgumentException
	 * @return string PHP date() format string
	 */
	public static function getOffsetFormat($value)
	{
		if (\preg_match('/[+-][0-9]{2}:[0-9]{2}/', $value))
			return 'P';
		elseif (\preg_match('/[+-][0-9]{2}[0-9]{2}/', $value))
			return 'O';
		throw new \InvalidArgumentException(
			'Unrecognized time zone offset format');
	}

	const UTC_OFFSET_STRING_PATTERN = '[+-]?00:?00';
}
