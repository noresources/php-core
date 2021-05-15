<?php

/**
 * Copyright © 2020 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

/**
 * An object that can be compared to another.
 */
interface ComparableInterface
{

	/**
	 * Compare instance with another Comparable.
	 *
	 *
	 * @throws NotComparableException:: This method MUST throw NotComparableException if $value
	 *         cannot be compared to instance.
	 * @param mixed $value
	 *        	Value to compare to instance.
	 *
	 * @return integer One of
	 *         <ul>
	 *         <li>&lt; 0 If instance value is less than $value</li>
	 *         <li>0 if instance and $value are equal</li>
	 *         <li>&gt; 0 if instance value is greater than $value</li>
	 *         </ul>
	 *         The meaning of "less", "equal" and "greater" depends on class value type.
	 */
	public function compare($value);
}
