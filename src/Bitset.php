<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources;

use NoreSources\Container\Container;
use NoreSources\Type\ArrayRepresentation;
use NoreSources\Type\IntegerRepresentation;
use NoreSources\Type\StringRepresentation;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

/**
 * Bit storage and manipulation
 */
class Bitset implements IntegerRepresentation, StringRepresentation,
	ArrayRepresentation, \ArrayAccess
{

	/**
	 * Get the maximum integer value that can be stored with the given number of bits.
	 *
	 * @param integer $bitCount
	 * @param boolean $signed
	 * @return number
	 */
	public static function getMaxIntegerValue($bitCount, $signed = false)
	{
		$bitCount = ($signed ? $bitCount - 1 : $bitCount);
		return \pow(2, $bitCount) - 1;
	}

	/**
	 *
	 * @param integer|string|array $value
	 * @throws \InvalidArgumentException
	 */
	public function __construct($value = 0)
	{
		if (\is_integer($value))
			$this->value = $value;
		elseif ($value instanceof IntegerRepresentation)
			$this->value = $value->getIntegerValue();
		elseif (\is_string($value))
			$this->value = \bindec($value);
		elseif (TypeDescription::hasStringRepresentation($value))
			$this->value = \bindec(TypeConversion::toString($value));
		elseif (Container::isTraversable($value))
		{
			$this->value = 0;
			foreach ($value as $i => $value)
			{
				if (!(\is_integer($i) && \is_integer($value)))
					throw new \InvalidArgumentException();
				$this->value |= (($value ? 1 : 0) << $i);
			}
		}
		else
			throw new \InvalidArgumentException(
				'integer, binary string or bit array expected, got ' .
				TypeDescription::getName($value));
	}

	/**
	 * Indicates if the bitset contains at least one or all bit flags of another bit set.
	 *
	 * @param integer|IntegerRepresentation $bits
	 *        	Bit flags to test against Bitset instance value.
	 * @param boolean $strict
	 *        	Whenever or not the instance value must match at least all of the bit flags of
	 *        	$bits.
	 * @return boolean.
	 */
	public function match($bits, $strict = false)
	{
		if ($bits instanceof IntegerRepresentation)
			$bits = $bits->getIntegerValue();
		$m = $this->value & $bits;
		return ($strict ? ($m == $bits) : ($m > 0));
	}

	/**
	 * Binary OR
	 *
	 * @param integer|IntegerRepresentation $value
	 * @return \NoreSources\Bitset
	 */
	public function add($value)
	{
		if ($value instanceof IntegerRepresentation)
			$value = $value->getIntegerValue();
		$this->value |= $value;
		return $this;
	}

	/**
	 * Remove bit flags
	 *
	 * @param integer|IntegerRepresentation $value
	 * @return \NoreSources\Bitset
	 */
	public function remove($value)
	{
		if ($value instanceof IntegerRepresentation)
			$value = $value->getIntegerValue();
		$this->value &= ~$value;
		return $this;
	}

	/**
	 * Get integer value of the bitset flags
	 *
	 * @return integer Integer value of the bitset
	 */
	public function getIntegerValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @return string Binary representation of the bitset
	 */
	public function __toString()
	{
		return $this->getBinaryString();
	}

	/**
	 *
	 * @return integer[] Value of each bit
	 */
	public function getArrayCopy()
	{
		return (\array_map(function ($v) {
			return \intval($v);
		}, \array_reverse(\str_split(\decbin($this->value)))));
	}

	/**
	 *
	 * @param string $pad
	 *        	Padding character
	 * @param number $padLength
	 *        	Minimum number of characters of the output string.
	 * @param integer $padDirection
	 *        	<ul>
	 *        	<li>&le; 0: Left padded to reach $padLength characters</li>
	 *        	<li>&gt; 0: Right padded to reach $padLength characters</li>
	 *        	</ul>
	 * @return string
	 */
	public function getBinaryString($pad = '', $padLength = 0,
		$padDirection = -1)
	{
		$s = \decbin($this->value);
		$length = \strlen($s);

		if ($padLength > $length && \strlen($pad))
			if ($padDirection <= 0)
				$s = \str_repeat($pad, ($padLength - $length)) . $s;
			else
				$s .= \str_repeat($pad, ($padLength - $length));
		return $s;
	}

	/**
	 * Indicates if the given offset can be stored
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return (\is_integer($offset) && $offset >= 0 &&
			($offset < (PHP_INT_SIZE * 8)));
	}

	/**
	 * Get Value of bit at the given offset
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset))
			return 0;
		return (($this->value >> $offset) & 0x1);
	}

	/**
	 * Set value of bit at the given offset
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		if (!$this->offsetExists($offset))
			throw new \OutOfRangeException();
		if (TypeConversion::toBoolean($value))
			$this->add(1 << $offset);
		else
			$this->remove(1 << $offset);
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		if (!$this->offsetExists($offset))
			return;
		$this->remove(1 << $offset);
	}

	const BIT_NONE = 0x00;

	const BIT_01 = 0x01;

	const BIT_02 = 0x02;

	const BIT_03 = 0x04;

	const BIT_04 = 0x08;

	const BIT_05 = 0x10;

	const BIT_06 = 0x20;

	const BIT_07 = 0x40;

	const BIT_08 = 0x80;

	const BYTE_01 = 0xFF;

	const BIT_09 = 0x100;

	const BIT_10 = 0x200;

	const BIT_11 = 0x400;

	const BIT_12 = 0x800;

	const BIT_13 = 0x1000;

	const BIT_14 = 0x2000;

	const BIT_15 = 0x4000;

	const BIT_16 = 0x8000;

	const BYTE_02 = 0xFF00;

	const BIT_17 = 0x10000;

	const BIT_18 = 0x20000;

	const BIT_19 = 0x40000;

	const BIT_20 = 0x80000;

	const BIT_21 = 0x100000;

	const BIT_22 = 0x200000;

	const BIT_23 = 0x400000;

	const BIT_24 = 0x800000;

	const BYTE_03 = 0xFF0000;

	const BIT_25 = 0x1000000;

	const BIT_26 = 0x2000000;

	const BIT_27 = 0x4000000;

	const BIT_28 = 0x8000000;

	const BIT_29 = 0x10000000;

	const BIT_30 = 0x20000000;

	const BIT_31 = 0x40000000;

	const BIT_32 = 0x80000000;

	const BYTE_04 = 0xFF000000;

	const BIT_ALL = 0xFFFFFFFF;

	/**
	 *
	 * @var integer
	 */
	private $value;
}
