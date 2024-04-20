<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Type;

use NoreSources\DateTime;
use NoreSources\Container\Container;
use NoreSources\Container\InvalidContainerException;

/**
 * Type conversion utility class
 */
class TypeConversion
{

	/**
	 * Conversion fallback value or function.
	 *
	 * if fallback is callable, it will be invoked with the
	 * following arguments
	 * 1. mixed $value - The target value
	 * 2. mixed $options - Conversion options
	 *
	 * @var string
	 */
	const OPTION_FALLBACK = 'fallback';

	/**
	 * Option flags
	 *
	 * @var string
	 */
	const OPTION_FLAGS = 'flags';

	/**
	 * Attempt to find and invoke the Class::createFrom<Type> factory function
	 *
	 * @var number
	 */
	const OPTION_FLAG_OBJECT_FACTORY = 0x01;

	/**
	 * Attempt to construct object by value
	 *
	 * @var number
	 */
	const OPTION_FLAG_OBJECT_CONSTRUCTOR = 0x02;

	/**
	 * Time zone option (for toDateTime)
	 *
	 * @var string
	 */
	const OPTION_TIMEZONE = 'timezone';

	/**
	 * Target type name.
	 * Automatically passed to fallback function
	 */
	const OPTION_TYPE = 'type';

	/**
	 *
	 * @param string $type
	 *        	Target type name
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion options
	 * @throws TypeConversionException
	 * @throws \BadMethodCallException
	 * @return $type Value of type $type
	 */
	public static function to($type, $value, $options = array())
	{
		$valueType = TypeDescription::getLocalName($value);
		if ($valueType == $type)
			return $value;

		$methodName = 'to' . $type;
		if (\method_exists(self::class, $methodName))
			return call_user_func([
				self::class,
				$methodName
			], $value,
				\array_merge($options, [
					self::OPTION_TYPE => $type
				]));

		if (\class_exists($type))
		{
			$flags = Container::keyValue($options, self::OPTION_FLAGS, 0);

			if (($flags & self::OPTION_FLAG_OBJECT_FACTORY) ==
				self::OPTION_FLAG_OBJECT_FACTORY)
			{
				$method = 'createFrom' . $valueType;
				if (\method_exists($type, $method))
				{
					try
					{
						return \call_user_func([
							$type,
							$method
						], $value);
					}
					catch (\Exception $e)
					{
						throw new TypeConversionException($value,
							$type . '::' . $method);
					}
				}
			}

			if (($flags & self::OPTION_FLAG_OBJECT_CONSTRUCTOR) ==
				self::OPTION_FLAG_OBJECT_CONSTRUCTOR)
			{
				try
				{
					return new $type($value);
				}
				catch (\Exception $e)
				{
					throw new TypeConversionException($value,
						$type . '::__construct()');
				}
			}
		}

		return self::fallbackOrThrowException($value,
			\array_merge($options, [
				self::OPTION_TYPE => $type
			]));
	}

	/**
	 * Convert value to array
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion options
	 * @throws TypeConversionException
	 * @return array
	 */
	public static function toArray($value, $options = array())
	{
		try
		{
			$v = Container::createArray($value, null);
		}
		catch (InvalidContainerException $e)
		{
			$v = null;
		}

		if (\is_array($v))
			return $v;

		return self::fallbackOrThrowException($value,
			\array_merge($options, [
				self::OPTION_TYPE => 'array'
			]));
	}

	/**
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion options
	 * @throws TypeConversionException
	 * @return \DateTimeInterface
	 */
	public static function toDateTime($value, $options = array())
	{
		$timezone = Container::keyValue($options, self::OPTION_TIMEZONE,
			null);
		if ($timezone && !($timezone instanceof \DateTimeZone))
			$timezone = new \DateTimeZone($timezone);

		$message = null;
		$d = null;
		if ($value instanceof \DateTimeInterface)
		{
			$d = $value;
			if ($timezone && ($timezone !== $value->getTimezone()))
				$d = clone $value;
		}
		elseif (\is_float($value) || \is_integer($value))
			$d = new DateTime($value, $timezone);
		elseif (\is_string($value))
		{
			try
			{
				$d = new DateTime($value, $timezone);
			}
			catch (\Exception $e)
			{
				$message = $e->getMessage();
			}
		}
		elseif (DateTime::isDateTimeStateArray($value))
			$d = DateTime::createFromArray($value);
		elseif (Container::isArray($value))
		{
			try
			{
				$d = DateTime::createFromArray($value);
			}
			catch (\Exception $e)
			{
				$message = 'Unsupported array content. ' .
					$e->getMessage();
			}
		}

		if ($d instanceof \DateTimeInterface)
		{
			if ($timezone)
				$d->setTimezone($timezone);
			return $d;
		}

		return self::fallbackOrThrowException($value,
			\array_merge($options,
				[
					self::OPTION_TYPE => \DateTime::class
				]));
	}

	/**
	 * Convert value to integer
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion options
	 * @throws TypeConversionException
	 * @return integer
	 */
	public static function toInteger($value, $options = array())
	{
		if ($value instanceof IntegerRepresentation)
			return $value->getIntegerValue();

		if ($value instanceof \DateTimeInterface)
			return $value->getTimestamp();
		elseif ($value instanceof \DateTimeZone)
			return $value->getOffset(
				new DateTime('now', DateTime::getUTCTimezone()));
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

		return self::fallbackOrThrowException($value,
			\array_merge($options, [
				self::OPTION_TYPE => 'integer'
			]));
	}

	/**
	 * Convert value to float
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion options
	 * @throws TypeConversionException
	 * @return float
	 */
	public static function toFloat($value, $options = array())
	{
		if ($value instanceof FloatRepresentation)
			return $value->getFloatValue();

		if ($value instanceof \DateTimeInterface)
			return DateTime::toJulianDay($value);
		elseif ($value instanceof \DateTimeZone)
			return \floatval(
				$value->getOffset(
					new DateTime('now', DateTime::getUTCTimezone())));
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

		return self::fallbackOrThrowException($value,
			\array_merge($options,
				[
					TypeConversion::OPTION_TYPE => 'float'
				]));
	}

	/**
	 * Convert value to string
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion options
	 * @throws TypeConversionException
	 * @return string
	 */
	public static function toString($value, $options = array())
	{
		if (\is_string($value))
			return $value;
		elseif (\is_numeric($value))
			return \strval($value);
		elseif (\is_object($value))
		{
			if (\method_exists($value, '__toString'))
				return \call_user_func([
					$value,
					'__toString'
				]);
			elseif ($value instanceof \DateTimeInterface)
				return $value->format(\DateTIme::ISO8601);
			elseif ($value instanceof \DateTimeZone)
				return $value->getName();
			elseif ($value instanceof \Serializable)
				return $value->serialize();
			elseif ($value instanceof \JsonSerializable &&
				\function_exists('\json_encode'))
				return \json_encode($value->jsonSerialize());

			return self::fallbackOrThrowException($value,
				\array_merge($options,
					[
						TypeConversion::OPTION_TYPE => 'string'
					]));
		}
		elseif (\is_array($value))
		{
			return self::fallbackOrThrowException($value,
				\array_merge($options,
					[
						TypeConversion::OPTION_TYPE => 'string'
					]));
		}

		$s = @\strval($value);
		if ($s !== false)
			return $s;

		return self::fallbackOrThrowException($value,
			\array_merge($options,
				[
					TypeConversion::OPTION_TYPE => 'string'
				]));
	}

	/**
	 * Convert value to boolean
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion options
	 * @throws TypeConversionException
	 * @return boolean
	 */
	public static function toBoolean($value, $options = array())
	{
		if ($value instanceof BooleanRepresentation)
			return $value->getBooleanValue();
		$v = @boolval($value);
		if (\is_bool($v))
			return $v;
		return self::fallbackOrThrowException($value,
			\array_merge($options,
				[
					TypeConversion::OPTION_TYPE => 'boolean'
				]));
	}

	/**
	 * Convert any value to NULL
	 *
	 * This method is defined for implementation details purpose.
	 *
	 * @param mixed $value
	 *        	Value to convert (ununsed)
	 * @param $options Conversion
	 *        	options.
	 * @return NULL, obviously...
	 */
	private static function toNull($value, $options = array())
	{
		return null;
	}

	/**
	 *
	 * @param mixed $value
	 *        	Value to convert
	 * @param array $options
	 *        	Conversion array
	 * @throws TypeConversionException
	 * @return mixed
	 */
	private static function fallbackOrThrowException($value, $options)
	{
		$type = $options[self::OPTION_TYPE];
		if (!Container::keyExists($options, self::OPTION_FALLBACK))
			throw new TypeConversionException($value, $type,
				'No fallback strategy defined.');

		$fallback = $options[self::OPTION_FALLBACK];
		unset($options[self::OPTION_FALLBACK]);

		if (\is_callable($fallback))
			return \call_user_func($fallback, $value, $options);
		return $fallback;
	}
}