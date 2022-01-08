<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace Food\Fruit
{

	interface Fallable
	{

		function fall();
	}

	class Apple implements Fallable
	{

		function fall()
		{
			return 'Aaaaaaaaah';
		}
	}

	class Pear
	{
	}
}
namespace Food\Fish
{

	interface AggressiveInterface
	{

		function bite();
	}

	trait AggressiveTrait
	{

		function bite()
		{
			return 'Gnack !';
		}
	}

	class Shark implements AggressiveInterface
	{

		function bite()
		{
			return 'Crounch !';
		}
	}

	class Cat implements AggressiveInterface
	{
		use AggressiveTrait;
	}

	class Babel
	{
	}
}