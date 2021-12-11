<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Type;

use NoreSources\ComparableInterface;
use NoreSources\ComparisonException;
use NoreSources\DateTime;
use NoreSources\Text\Text;

/**
 * Type comparison utility
 */
class TypeComparison
{

	/**
	 *
	 * @param mixed $a
	 *        	Left operand
	 * @param mixed $b
	 *        	Right operand
	 * @throws ComparisonException
	 * @return number - 0 if $a and $b are equal.
	 *         - < 0 If $a is lesser than $b
	 *         - > 0 if $a is greater than $b
	 *         This method uses the following rules
	 *         - NULL is lower than everything except itself.
	 *         - FALSE is lower than anything that evaluates to TRUE and equals anything that
	 *         evaluates to FALSE (except NULL).
	 *         - TRUE is greater than anything that evaluates to FALSE.
	 *         - Two numbers are cmopared using the minus operator
	 *         - Two strings are compared using the \strcmp() function
	 *         - Numbers are compared to strings by casting the string to number if possible
	 *
	 */
	public static function compare($a, $b)
	{
		$ta = TypeDescription::getName($a);
		$tb = TypeDescription::getName($b);

		try
		{
			if ($a instanceof ComparableInterface)
				return $a->compare($b);
			elseif ($b instanceof ComparableInterface)
				return -$b->compare($a);
			elseif (\is_null($a))
				return \is_null($b) ? 0 : -1;
			elseif (\is_null($b))
				return 1;
			elseif (\is_bool($a))
				return self::compareBoolean($a, $b);
			elseif (\is_bool($b))
				return -self::compareBoolean($b, $a);
			elseif (\is_numeric($a))
				return self::compareNumber($a, $b);
			elseif ($a instanceof \DateTimeInterface)
				return self::compareDateTime($a, $b);
			elseif (\is_string($a))
				return self::compareString($a, $b);

			elseif (\is_numeric($b))
				return -self::compareNumber($b, $a);
			elseif ($b instanceof \DateTimeInterface)
				return -self::compareDateTime($b, $a);
			elseif (\is_string($b))
				return -self::compareString($b, $a);
		}
		catch (ComparisonException $e)
		{
			throw $e;
		}
		catch (\Exception $e)
		{}

		throw new ComparisonException(
			$ta . ' <-> ' . $tb . ' Not comparable');
	}

	protected static function compareBoolean($a, $b)
	{
		if ($a)
			return ($b ? 0 : 1);
		return ($b ? -1 : 0);
	}

	protected static function compareNumber($a, $b)
	{
		if (\is_integer($a) && $b instanceof \DateTimeInterface)
			return $a - $b->getTimestamp();

		return $a - TypeConversion::toFloat($b);
	}

	protected static function compareString($a, $b)
	{
		if (\is_numeric($b))
		{
			if (Text::isFloat($a))
				return \floatval($a) - $b;
			throw new ComparisonException(
				'Cannot compare arbitraty string with ' .
				TypeDescription::getName($b));
		}

		return \strcmp($a, TypeConversion::toString($b));
	}

	protected static function compareDateTime(\DateTimeInterface $a, $b)
	{
		if (\is_string($b))
			$b = new DateTime($b);

		if ($b instanceof \DateTimeInterface)
		{
			/** @var \DateTimeInterface */
			$interval = $a->diff($b);
			if ($interval->invert)
				return -1;

			foreach ([
				'y',
				'm',
				'd',
				'h',
				'i',
				's',
				'f'
			] as $property)
			{
				if ($interval->$property > 0)
					return 1;
			}

			return 0;
		}
		elseif (\is_float($b))
			return DateTime::toJulianDay($a) - $b;
		elseif (\is_integer($b))
			return $a->getTimestamp() - $b;

		throw new \Exception();
	}
}
