<?php

namespace NoreSources;

use phpDocumentor\Reflection\Types\Integer;

class TypeConversionException extends \Exception
{

	public $value;

	public function __construct($value, $method, $message = null)
	{
		parent::__construct('Failed to convert ' . TypeDescription::getName($value) .
			' to ' . preg_replace(',.*::to(.*),', '\1', $method) .
			($message ? (' : ' . $message) : ''));

		$this->value = $value;
	}
}

class TypeConversion
{

	public static function to($value, $type, $fallback)
	{
		$methodName = 'to' . $type;
		if (\method_exists(self::class, $methodName))
			return call_user_func ([self::class, $methodName], $value, $fallback);

		throw new \BadMethodCallException('Mo method to convert to ' . $type . ' (' . $methodName . ' not found)');
	}

	/**
	 * Convert value to POD array
	 * @param mixed $value Value to convert
	 * @param callable $fallback A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return array
	 */
	public function toArray($value, $fallback = null)
	{
		$v = Container::createArray($value, null);
		if (!\is_array($v))
		{
			if (\is_callable($fallback))
				return call_user_func($fallback, $value);
			else
				throw new TypeConversionException($value, __METHOD__);
		}

		return $v;
	}

	/**
	 * Convert value to DateTime
	 * @param mixed $value Value to convert
	 * @param callable $fallback A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return \DateTime
	 */
	public static function toDateTime($value, $fallback = null)
	{
		if ($value instanceof \DateTime)
			return $value;

		$message = null;
		if (\is_float($value))
		{
			$d = new \DateTime();
			$d->setTimestamp(jdtounix($value));
			return $d;
		}
		elseif (\is_int($value))
		{
			$d = new \DateTime();
			$d->setTimestamp($value);
			return $d;
		}
		elseif (\is_string($value))
		{
			try
			{
				$d = new \DateTime($value);
				return $d;
			}
			catch (\Exception $e)
			{
				$message = $e->getMessage();
			}
		}
		elseif (Container::isArray($value))
		{
			try
			{
				$d = DateTime::createFromArray($value);
				return $d;
			}
			catch (\Exception $e)
			{
				$message = $e->getMessage();
			}
		}

		if (\is_callable($fallback))
			return call_user_func($fallback, $value);

		throw new TypeConversionException($value, __METHOD__, $message);
	}

	/**
	 * Convert value to integer
	 * @param mixed $value Value to convert
	 * @param callable $fallback A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return Integer
	 */
	public static function toInteger($value, $fallback = null)
	{
		if ($value instanceof \DateTime)
			return $value->getTimestamp();
		elseif (\is_bool($value))
			return ($value ? 1 : 0);
		elseif (\is_null($value))
			return 0;

		if (\is_numeric($value))
		{
			$i = @intval($value);
			if (\is_integer($i))
				return $i;
		}

		if (\is_callable($fallback))
			return call_user_func($fallback, $value);

		throw new TypeConversionException($value, __METHOD__);
	}

	/**
	 * Convert value to float
	 * @param mixed $value Value to convert
	 * @param callable $fallback A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return float
	 */
	public static function toFloat($value, $fallback = null)
	{
		if ($value instanceof \DateTime)
			return unixtojd($value->getTimestamp());
		elseif (\is_bool($value))
			return ($value ? 1. : 0.);
		elseif (\is_null($value))
			return 0.;

		if (\is_numeric($value))
		{
			$f = @floatval($value);
			if (\is_float($f))
				return $f;
		}

		if (\is_callable($fallback))
			return call_user_func($fallback, $value);

		throw new TypeConversionException($value, __METHOD__);
	}

	/**
	 * Convert value to string
	 *
	 * @param mixed $value Value to convert
	 * @param callable $fallback A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return string
	 */
	public static function toString($value, $fallback = null)
	{
		if ($value instanceof \DateTime)
			return $value->format(\DateTIme::ISO8601);

		if ((\is_object($value) && !\method_exists($value, '__toString')) ||
			\is_array($value))
		{
			if (\is_callable($fallback))
				return call_user_func($fallback, $value);

			throw new TypeConversionException($value, __METHOD__);
		}

		$s = @strval($value);
		if (\is_string($s))
			return $s;

		if (\is_callable($fallback))
			return call_user_func($fallback, $value);

		throw new TypeConversionException($value, __METHOD__);
	}

	/**
	 * Convert value to boolean
	 * @param mixed $value Value to convert
	 * @return boolean
	 */
	public static function toBoolean($value)
	{
		return @boolval($value);
	}
}