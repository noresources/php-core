<?php
namespace NoreSources;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Type conversion exception
 */
class TypeConversionException extends \Exception
{

	/**
	 *
	 * @var mixed Value that cannot be converted
	 */
	public $value;

	public function __construct($value, $method, $message = null)
	{
		parent::__construct(
			'Failed to convert ' . TypeDescription::getName($value) . ' to ' .
			preg_replace(',.*::to(.*),', '\1', $method) . ($message ? (' : ' . $message) : ''));

		$this->value = $value;
	}
}

/**
 * Object have a integer representation
 */
interface IntegerRepresentation
{

	/**
	 *
	 * @return integer Integer representation of the class instance
	 */
	function getIntegerValue();
}

/**
 * Object have a float representation
 */
interface FloatRepresentation
{

	/**
	 *
	 * @return float Float representation of the class instance
	 */
	function getFloatValue();
}

/**
 * Object have a boolean representation
 */
interface BooleanRepresentation
{

	/**
	 *
	 * @return boolean Boolean representation of the class instance
	 */
	function getBooleanValue();
}

/**
 * Object can be converted to array
 */
interface ArrayRepresentation
{

	/**
	 *
	 * @return array Array representation of the class instance
	 */
	function getArrayCopy();
}

/**
 * Object have a string representation.
 * This interface is a syntaxic sugar to indicates the object redefines the __toString() magic method
 */
interface StringRepresentation
{

	/**
	 *
	 * @return string The string representation of the class instance
	 */
	function __toString();
}

class TypeConversion
{

	/**
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param string $type
	 *        	Target type.
	 * @param callable $fallback
	 *        	A cacallback to invoke if the method is nuable to convert the value e
	 * @throws \BadMethodCallException
	 */
	public static function to($value, $type, $fallback)
	{
		$methodName = 'to' . $type;
		if (\method_exists(self::class, $methodName))
			return call_user_func ([self::class, $methodName], $value, $fallback);

		throw new \BadMethodCallException(
			'Mo method to convert to ' . $type . ' (' . $methodName . ' not found)');
	}

	/**
	 * Convert value to POD array
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param callable $fallback
	 *        	A cacallback to invoke if the method is nuable to convert the value e
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
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param callable $fallback
	 *        	A cacallback to invoke if the method is nuable to convert the value e
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
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param callable $fallback
	 *        	A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return Integer
	 */
	public static function toInteger($value, $fallback = null)
	{
		if ($value instanceof IntegerRepresentation)
			return $value->getIntegerValue();

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
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param callable $fallback
	 *        	A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return float
	 */
	public static function toFloat($value, $fallback = null)
	{
		if ($value instanceof FloatRepresentation)
			return $value->getFloatValue();

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
	 * @param mixed $value
	 *        	Value to convert
	 * @param callable $fallback
	 *        	A cacallback to invoke if the method is nuable to convert the value e
	 * @throws TypeConversionException
	 * @return string
	 */
	public static function toString($value, $fallback = null)
	{
		if ($value instanceof \DateTime)
			return $value->format(\DateTIme::ISO8601);

		if ((\is_object($value) && !\method_exists($value, '__toString')) || \is_array($value))
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
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @return boolean
	 */
	public static function toBoolean($value)
	{
		if ($value instanceof BooleanRepresentation)
			return $value->getBooleanValue();
		return @boolval($value);
	}

	/**
	 * Convert any value to @c NULL
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @return NULL, obviously...
	 */
	public static function toNull($value)
	{
		return null;
	}
}