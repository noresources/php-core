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
use NoreSources\NotComparableException;
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
			elseif ($b instanceof \DateTimeInterface)
				return -self::compareDateTime($b, $a);
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

		$ta = TypeDescription::getName($a);
		$tb = TypeDescription::getName($b);
		throw new ComparisonException(
			$ta . ' <-> ' . $tb . ' Not comparable');
	}

	/**
	 *
	 * @param \DateTimeInterface $a
	 *        	First operand
	 * @param \DateTimeInterface|string|integer|float $b
	 *        	Second operand. DateTime, string representation of a DateTime, UNIX timestamp or
	 *        	julian day
	 * @param number $fractionEpsilon
	 *        	Threshold to use in \DateTimeInterface fraction field comparison.
	 *        	If $fractionEpsilon > 1 A UNIX timestamp comparison is made instead.
	 * @throws NotComparableException
	 * @return number
	 */
	public static function compareDateTime(\DateTimeInterface $a, $b,
		$fractionEpsilon = 1)
	{
		if (\is_string($b))
			$b = new DateTime($b);

		if ($b instanceof \DateTimeInterface)
		{
			if ($fractionEpsilon >= 1)
				return $a->getTimestamp() - $b->getTimestamp();

			/** @var \DateTimeInterface */
			$interval = $a->diff($b);
			$m = -1;
			if ($interval->invert)
				$m = 1;

			foreach ([
				'y',
				'm',
				'd',
				'h',
				'i',
				's'
			] as $property)
			{
				$v = $interval->$property;
				if ($v > 0)
				{
					return $m * $v;
				}
			}

			if ($interval->f > $fractionEpsilon)
				return $m * $interval->f;

			return 0;
		}
		elseif (\is_float($b))
			return DateTime::toJulianDay($a) - $b;
		elseif (\is_integer($b))
			return $a->getTimestamp() - $b;

		$ta = TypeDescription::getName($a);
		$tb = TypeDescription::getName($b);
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
}
