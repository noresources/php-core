<?php
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