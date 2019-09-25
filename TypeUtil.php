<?php
namespace NoreSources;

class TypeUtil
{

	/**
	 * Convert anyting to a DateTime
	 *
	 * @param unknown $value
	 * @throws \Exception
	 * @return \DateTime
	 */
	public static function toDateTime ($value)
	{
		if (\is_int($value))
		{
			$d = new \DateTime('now');
			$d->setTimestamp($value);
			$value = $d;
		}
		elseif (\is_float($value))
		{
			$d = new \DateTime('now');
			$d->setTimestamp(jdtounix($value));
			$value = $d;
		}
		elseif (\is_string($value))
		{
			// Always expec ISO format
			$d = \DateTime::createFromFormat(\DateTime::ISO8601, $value);
			if (!($d instanceof \DateTime))
				throw new \Exception('Failed to convert string ' . $value . 'to DateTime');
		}
		elseif (\is_array($value))
		{
			if (\array_key_exists('timezone_type', $value) && \array_key_exists('timezone', $value) &&
					\array_key_exists('date', $value))
			{
				$value = \DateTime::__set_state($value);
			}
			elseif (\array_key_exists('format', $value) && \array_key_exists('time', $value))
			{
				$d = \DateTime::createFromFormat($value['format'], $value['time']);
				if (!($d instanceof \DateTime))
					throw new \Exception('Failed to convert array ' . var_export($value, true) . ' to DateTime');

				$value = $d;
			}
			else
			{
				throw new \Exception('Invalid array format ' . var_export($value, true));
			}
		}

		if (!($value instanceof \DateTime))
		{
			throw new \Exception('Failed to convert ' . gettype($value) . ' to DateTime');
		}

		return $value;
	}
}