<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

class ChainValue extends ChainElement implements StringRepresentation
{

	public $value;

	public function __construct($v)
	{
		$this->value = $v;
	}

	public function __toString()
	{
		return \strval($this->value);
	}
}

final class ChainTest extends \PHPUnit\Framework\TestCase
{

	final function testInit()
	{
		$a = new ChainValue(1);
		$this->assertEquals(null, $a->getPreviousElement(), 'getPrevious : null');
		$this->assertEquals(null, $a->getNextElement(), 'getNext: null');
	}

	final function testInsertAfter()
	{
		$a = new ChainValue('a');
		$b = new ChainValue('b');
		$b->insertAfter($a);

		$this->assertEquals('a, b', self::stringifyForward($a), 'append :: forward');
		$this->assertEquals('b, a', self::stringifyBackward($b), 'append :: backward');

		$c = new ChainValue('c');
		$c->insertAfter($b);
		$this->assertEquals('a, b, c', self::stringifyForward($a), 'append again :: forward');
		$this->assertEquals('c, b, a', self::stringifyBackward($c), 'append again :: backward');

		$b2 = new ChainValue('b2');
		$b2->insertAfter($a);
		$this->assertEquals('a, b2, b, c', self::stringifyForward($a), 'insert :: forward');
		$this->assertEquals('c, b, b2, a', self::stringifyBackward($c), 'insert :: backward');

		$head = new ChainValue('head');
		$head->insertBefor($a);
		$this->assertEquals('head, a, b2, b, c', self::stringifyForward($head), 'prepend :: forward');
	}

	public static function stringifyForward(ChainValue $e)
	{
		$s = '';
		$i = 0;

		if (rand() % 2)
		{
			while ($e instanceof ChainElementInterface)
			{
				if ($i++)
					$s .= ', ';
				$s .= \strval($e);
				$e = $e->getNextElement();
			}
		}
		else
		{
			foreach ($e as $element)
			{
				if ($i++)
					$s .= ', ';
				$s .= \strval($element);
			}
		}

		return $s;
	}

	public static function stringifyBackward(ChainValue $e)
	{
		$s = '';
		$i = 0;
		while ($e instanceof ChainElementInterface)
		{
			if ($i++)
				$s .= ', ';
			$s .= \strval($e);
			$e = $e->getPreviousElement();
		}

		return $s;
	}
}
