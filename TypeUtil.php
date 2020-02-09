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
		if (\is_null($value))
			return (new \DateTime('now'));
		if (\is_int($value))
		{
			$d = new \DateTime('now');
			$d->setTimestamp($value);
			return $d;
		}
		elseif (\is_float($value))
		{
			$d = new \DateTime('now');
			$d->setTimestamp(jdtounix($value));
			return $d;
		}
		elseif (\is_string($value))
		{
			if ((strlen($value) == 0) || (strtolower($value) == 'now'))
				return new \DateTime('now');

			// Always expec ISO format
			$d = \DateTime::createFromFormat(\DateTime::ISO8601, $value);
			if ($d instanceof \DateTime)
				return $d;
			
			return (new \DateTime ($value));
		}
		elseif (\is_array($value))
		{
			if (\array_key_exists('timezone_type', $value) && \array_key_exists('timezone', $value) &&
					\array_key_exists('date', $value))
			{
				$value = \DateTime::__set_state($value);
				return $value;
			}
			elseif (\array_key_exists('format', $value) && \array_key_exists('time', $value))
			{
				$d = \DateTime::createFromFormat($value['format'], $value['time']);
				
				if (!($d instanceof \DateTime))
					throw new \Exception(__METHOD__ . ' Failed to convert array ' . var_export($value, true) . ' to DateTime');

				return $d;
			}
			else
			{
				throw new \Exception(__METHOD__ . ' Invalid array format ' . var_export($value, true));
			}
		}

		if (!($value instanceof \DateTime))
		{
			throw new \Exception(
					__METHOD__ . ' Failed to convert ' . gettype($value) . ' ' . var_export($value, true) . ' to DateTime');
		}

		return $value;
	}
}
