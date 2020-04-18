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

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException implements NotFoundExceptionInterface
{

	public function __construct()
	{}
}

$indexedReference = array(
	"zero",
	"one",
	"two",
	"three"
);
$sparseIndexedReference = array(
	0 => "zero",
	1 => "one",
	3 => "three"
);
$hashReference = array(
	"one" => 1,
	"two" => 2,
	"the great answer" => "Fourty two"
);
$hashReferenceObject = new \ArrayObject($hashReference);
$indexedReferenceObject = new \ArrayObject($indexedReference);
$sparseIndexedReferenceObject = new \ArrayObject($sparseIndexedReference);

$nullValue = null;

class SimpleClass
{

	public $number;
}

class ArrayAccessImpl implements \ArrayAccess
{

	public function __construct($t = array())
	{
		$this->table = $t;
	}

	public function offsetExists($offset)
	{
		return \array_key_exists($offset, $this->table);
	}

	public function offsetGet($offset)
	{
		return $this->table[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->table[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->table[$offset]);
	}

	private $table;
}

class ContainerImpl implements ContainerInterface
{

	public function __construct($a)
	{
		$this->table = $a;
	}

	public function has($id)
	{
		return \array_key_exists($id, $this->table);
	}

	public function get($id)
	{
		if (\array_key_exists($id, $this->table))
			return $this->table[$id];

		throw new NotFoundException();
	}

	private $table;
}

$hashReferenceImpl = new ArrayAccessImpl($hashReference);

class TraversableImpl implements \IteratorAggregate
{

	public function __construct($array)
	{
		$this->array = new \ArrayObject($array);
	}

	public function getIterator()
	{
		return $this->array->getIterator();
	}

	/**
	 *
	 * @var \ArrayObject
	 */
	private $array;
}

final class ContainerTest extends \PHPUnit\Framework\TestCase
{

	public function testPODArrayIsArray()
	{
		$this->assertEquals(true, Container::isArray(array()));
	}

	public function testArrayObjectIsArray()
	{
		$o = new \ArrayObject();
		$this->assertEquals(true, Container::isArray($o));
	}

	public function testArrayAccessIsArray()
	{
		$o = new ArrayAccessImpl();
		$this->assertEquals(true, Container::isArray($o));
	}

	public function testClassInstanceIsArray()
	{
		$o = new SimpleClass();
		$this->assertEquals(false, Container::isArray($o));
	}

	public function testValueExists()
	{
		global $indexedReference;
		$this->assertEquals(true, Container::valueExists($indexedReference, 'two'));
		$this->assertEquals(false, Container::valueExists($indexedReference, 'deux'));
	}

	public function testSetValueArray()
	{
		$a = [
			'hello' => 'everyone',
			'good' => 'bye'
		];
		Container::setValue($a, 'hello', 'world');
		$this->assertEquals('world', $a['hello']);
	}

	public function testSetValueArrayAccess()
	{
		$a = new \ArrayObject([
			'hello' => 'everyone',
			'good' => 'bye'
		]);
		Container::setValue($a, 'hello', 'world');
		$this->assertEquals('world', $a['hello']);
	}

	public function testSetValueArbitraryClass()
	{
		$a = new \stdClass();
		$a->hello = 'world';
		$a->good = 'bye';

		Container::setValue($a, 'hello', 'world');
		$this->assertEquals('world', $a->hello);
	}

	public function testSetValueArbitraryClassInvalidMember()
	{
		$a = new \stdClass();
		$a->hello = 'world';
		$a->good = 'bye';

		$exception = null;
		try
		{
			Container::setValue($a, 'undefied', 42);
		}
		catch (\Exception $e)
		{
			$exception = $e;
		}

		$this->assertInstanceOf(\InvalidArgumentException::class, $exception);
	}

	public function testSetValueArbitraryClassInvalidKey()
	{
		$a = new \stdClass();
		$a->hello = 'world';
		$a->good = 'bye';

		$exception = null;
		try
		{
			Container::setValue($a, 2, 42);
		}
		catch (\Exception $e)
		{
			$exception = $e;
		}

		$this->assertInstanceOf(InvalidContainerException::class, $exception);
	}

	public function testCountArray()
	{
		global $indexedReference;
		$this->assertEquals(4, Container::count($indexedReference));
	}

	public function testCountAssociativeArray()
	{
		global $sparseIndexedReference;
		$this->assertEquals(3, Container::count($sparseIndexedReference));
	}

	public function testCountHashTable()
	{
		global $hashReference;
		$this->assertEquals(3, Container::count($hashReference));
	}

	public function testCountArrayObject()
	{
		global $indexedReferenceObject;
		$this->assertEquals(4, Container::count($indexedReferenceObject));
	}

	public function testCountAssociativeArrayObject()
	{
		global $sparseIndexedReferenceObject;
		$this->assertEquals(3, Container::count($sparseIndexedReferenceObject));
	}

	public function testCountHashTableObject()
	{
		global $hashReferenceObject;
		$this->assertEquals(3, Container::count($hashReferenceObject));
	}

	public function testCountHashTableArrayAccess()
	{
		global $hashReferenceImpl;
		if (\is_callable(array(
			$this,
			'expectException'
		)))
		{
			$this->expectException(InvalidContainerException::class);
			Container::count($hashReferenceImpl);
		}
		else
		{
			$result = null;
			try
			{
				$result = Container::count($hashReferenceImpl);
			}
			catch (\Exception $e)
			{
				$result = get_class($e);
			}
			finally {
				$this->assertEquals(InvalidContainerException::class, $result);
			}
		}
	}

	public function testGetKeysAndValues()
	{
		$expectedKeys = [
			'one',
			2,
			'three',
			4,
			'five',
			6,
			'un'
		];
		$expectedValues = [
			1,
			'two',
			3,
			'four',
			5,
			'six',
			1
		];

		$array = [];
		for ($i = 0; $i < \count($expectedKeys); $i++)
		{
			$array[$expectedKeys[$i]] = $expectedValues[$i];
		}

		$arrayObject = new \ArrayObject($array);
		$traversable = new TraversableImpl($array);
		$container = new ContainerImpl($array);

		$this->assertEquals($expectedKeys, Container::getKeys($array), 'Container::getKeys (array)');
		$this->assertEquals($expectedKeys, Container::getKeys($arrayObject),
			'Container::getKeys (ArrayObject)');
		$this->assertEquals($expectedKeys, Container::getKeys($traversable),
			'Container::getKeys (TraversableImpl)');

		$this->assertEquals($expectedValues, Container::getValues($array),
			'Container::getValue(array)');
		$this->assertEquals($expectedValues, Container::getValues($arrayObject),
			'Container::getValue(ArrayObject)');
		$this->assertEquals($expectedValues, Container::getValues($traversable),
			'Container::getValue(TraversableImpl)');

		$this->expectException(InvalidContainerException::class);
		Container::getKeys($container);
	}

	public function testImplodeValueBasic()
	{
		global $indexedReference;
		$builtin = \implode(', ', $indexedReference);
		$ns = Container::implodeValues($indexedReference, ', ');

		$this->assertEquals($builtin, $ns, 'implodeValue basically mimics the implode function');
	}

	/**
	 * Test ability to skip elements
	 */
	public function testImplodeValueSparse()
	{
		$source = [
			0 => 'zero',
			1 => 'one',
			2 => false,
			3 => 'three',
			4 => false,
			5 => false,
			7 => 'seven'
		];

		$glue = [
			'before' => '[',
			'after' => ']',
			'between' => ', ',
			'last' => ' and '
		];

		$even = '[zero], [] and []';
		$evenStrings = '[zero]';
		$odd = '[one], [three], [] and [seven]';
		$stringValues = '[zero], [one], [three] and [seven]';

		$evenResult = Container::implode($source, $glue,
			function ($k, $v) {
				if ($k % 2 == 0)
					return strval($v);
				return false;
			});

		$this->assertEquals($even, $evenResult, 'Only even indexes');

		$evenStringsResult = Container::implode($source, $glue,
			function ($k, $v) {
				if ($k % 2 == 1)
					return false;
				if ($v === false)
					return false;
				return strval($v);
			});

		$this->assertEquals($evenStrings, $evenStringsResult, 'Filter odd keys and false values');

		$oddResult = Container::implode($source, $glue,
			function ($k, $v) {
				if ($k % 2 == 0)
					return false;
				return strval($v);
			});

		$this->assertEquals($odd, $oddResult);

		$stringValuesResult = Container::implodeValues($source, $glue,
			function ($v) {
				if ($v === false)
					return false;
				return strval($v);
			});

		$this->assertEquals($stringValues, $stringValuesResult, 'Only string values');
	}

	public function testremoveKeyArrayCopy()
	{
		$source = array(
			'one' => 1,
			'two' => 2,
			'three' => 3,
			'four' => 4
		);
		$target = array(
			'one' => 1,
			'two' => 2,
			'four' => 4
		);
		$this->assertEquals($target, Container::removeKey($source, 'three', Container::REMOVE_COPY));
	}

	public function testremoveKeyArrayInplace()
	{
		$source = array(
			'one' => 1,
			'two' => 2,
			'three' => 3,
			'four' => 4
		);
		$target = array(
			'one' => 1,
			'two' => 2,
			'four' => 4
		);
		Container::removeKey($source, 'three', Container::REMOVE_INPLACE);
		$this->assertEquals($target, $source);
	}

	public function testremoveKeyArrayInplace2()
	{
		$source = array(
			'one',
			'two',
			'three',
			'four'
		);
		$target = array(
			0 => 'one',
			1 => 'two',
			3 => 'four'
		);
		Container::removeKey($source, 2, Container::REMOVE_INPLACE);
		$this->assertEquals($target, $source);
	}

	public function testAssoc()
	{
		$this->assertTrue(Container::isIndexed([]), 'Empty container is indexed');
		$this->assertTrue(Container::isAssociative([]), 'Empty container is associative');

		$indexed = [
			'zero',
			'one',
			'two'
		];
		$explicitIndexes = [
			0 => 'zero',
			1 => 'one',
			2 => 'two'
		];
		$numericStringIndexes = [
			"0" => 'zero',
			"1" => 'one',
			"2" => 'two'
		];

		$unordered = [
			1 => 'one',
			0 => 'zero',
			2 => 'two'
		];

		$this->assertTrue(Container::isIndexed($indexed), 'Indexed is indexed');
		$this->assertTrue(Container::isIndexed($indexed, true), 'Indexed is indexed (strict)');

		$this->assertTrue(Container::isIndexed($explicitIndexes), 'Explicitely indexed is indexed');
		$this->assertTrue(Container::isIndexed($explicitIndexes, true),
			'Explicitely indexed is indexed (strict)');

		$this->assertTrue(Container::isIndexed($numericStringIndexes),
			'numeric string indexes is indexed');
		$this->assertTrue(Container::isIndexed($numericStringIndexes, true),
			'numeric string indexes (strict) is still indexed due to PHP auto conversion');

		$this->assertFalse(Container::isIndexed($unordered), 'Unordered is not indexed');
		$this->assertFalse(Container::isIndexed($unordered, true),
			'Unordered is not indexed (strict)');

		$this->assertFalse(Container::isAssociative($indexed), 'Indexed is not associative');
		$this->assertFalse(Container::isAssociative($indexed, true),
			'Indexed is not associative (strict)');

		$this->assertFalse(Container::isAssociative($explicitIndexes),
			'Explicitely indexed is not associative');
		$this->assertFalse(Container::isAssociative($explicitIndexes, true),
			'Explicitely indexed is not associative (strict)');

		$this->assertTrue(Container::isAssociative($unordered), 'Unordered is associative');
		$this->assertTrue(Container::isAssociative($unordered, true),
			'Unordered is associative (strict)');

		$associative = [
			'one' => 'Un',
			'two' => 'deux',
			'three' => 'trois'
		];

		$mixed = [
			'zero' => 'zero',
			1 => 'Un',
			'two' => 'deux',
			3 => 'trois'
		];

		$this->assertFalse(Container::isIndexed($associative), 'Associative is not indexed');
		$this->assertTrue(Container::isAssociative($associative));

		$this->assertFalse(Container::isIndexed($mixed), 'mixed is not indexed');
		$this->assertTrue(Container::isAssociative($mixed), 'mixed is associative');
	}
}
