<?php

/**
 * Copyright © 2012-2018 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

class DateTime extends \DateTime
{

	/**
	 *
	 * @param string $time
	 * @param \DateTimeZone $timezone
	 */
	public function __construct($time = null, \DateTimeZone $timezone = null)
	{
		parent::__construct($time, $timezone);
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
	 *        	<li>Timezone identifier (@see https://www.php.net/manual/en/class.datetimezone.php)</li>
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
	 *        	Return a built-in \DateTime instance. Otherwise, return a \NoreSources\DateTime (with no
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
	 * Indicate if the given array can be used to create a DateTime using DateTime::__set_state() magic method.
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
}