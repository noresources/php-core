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
	 * @param integer|string|array $time
	 * @param \DateTimeZone $timezone
	 */
	public function __construct($time = null, DateTimeZone $timezone = null)
	{
		parent::__construct(\is_string($time) ? $time : null, $timezone);

		if (self::isDateTimeStateArray($time)) {
			$d = self::__set_state(Container::createArray($time));

			$this->setTimezone($d->getTimezone());
			$this->setTimestamp($d->getTimestamp());
		}
	}

	/**
	 * Create a DateTime from a DateTime description array
	 * @param array $array
	 * @throws \InvalidArgumentException
	 * @return DateTime
	 *
	 * @ignore Fractional seconds are not supported
	 */
	public static function createFromArray($array)
	{
		if (self::isDateTimeStateArray($array))
			return new DateTime($array);

		throw new \InvalidArgumentException(var_export($array, true) . ' is not a valid DateTime array');
	}

	/**
	 * Indicate if the given array can be used to create a DateTime using DateTime::__set_state() magic method.
	 * @param array $array
	 * @return boolean
	 */
	public static function isDateTimeStateArray($array)
	{
		if (Container::isArray($array))
		{
			$stateKeys = array (
					'date',
					'timezone',
					'timezone_type'
			);

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