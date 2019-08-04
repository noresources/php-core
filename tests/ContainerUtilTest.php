<?php

namespace NoreSources;

use PHPUnit\Framework\TestCase;

$indexedReference = array (
		"zero",
		"one",
		"two",
		"three"
);
$sparseIndexedReference = array (
		0 => "zero",
		1 => "one",
		3 => "three"
);
$hashReference = array (
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

$hashReferenceImpl = new ArrayAccessImpl($hashReference);

final class ContainerUtilIsArrayTest extends TestCase
{

	public function testPODArrayIsArray()
	{
		$this->assertEquals(true, ContainerUtil::isArray(array ()));
	}

	public function testArrayObjectIsArray()
	{
		$o = new \ArrayObject();
		$this->assertEquals(true, ContainerUtil::isArray($o));
	}

	public function testArrayAccessIsArray()
	{
		$o = new ArrayAccessImpl();
		$this->assertEquals(true, ContainerUtil::isArray($o));
	}

	public function testClassInstanceIsArray()
	{
		$o = new SimpleClass();
		$this->assertEquals(false, ContainerUtil::isArray($o));
	}
}

final class ContainerUtilValueExistsTest extends TestCase
{

	public function testCountArray()
	{
		global $indexedReference;
		$this->assertEquals(true, ContainerUtil::valueExists($indexedReference, 'two'));
		$this->assertEquals(false, ContainerUtil::valueExists($indexedReference, 'deux'));
	}
}

final class ContainerUtilCountTest extends TestCase
{

	public function testCountArray()
	{
		global $indexedReference;
		$this->assertEquals(4, ContainerUtil::count($indexedReference));
	}

	public function testCountAssociativeArray()
	{
		global $sparseIndexedReference;
		$this->assertEquals(3, ContainerUtil::count($sparseIndexedReference));
	}

	public function testCountHashTable()
	{
		global $hashReference;
		$this->assertEquals(3, ContainerUtil::count($hashReference));
	}

	public function testCountArrayObject()
	{
		global $indexedReferenceObject;
		$this->assertEquals(4, ContainerUtil::count($indexedReferenceObject));
	}

	public function testCountAssociativeArrayObject()
	{
		global $sparseIndexedReferenceObject;
		$this->assertEquals(3, ContainerUtil::count($sparseIndexedReferenceObject));
	}

	public function testCountHashTableObject()
	{
		global $hashReferenceObject;
		$this->assertEquals(3, ContainerUtil::count($hashReferenceObject));
	}

	public function testCountHashTableArrayAccess()
	{
		global $hashReferenceImpl;
		if (\is_callable(array (
				$this,
				'expectException'
		)))
		{
			$this->expectException(InvalidContainerException::class);
			ContainerUtil::count($hashReferenceImpl);
		}
		else 
		{
			$result = null;
			try
			{
				$result = ContainerUtil::count($hashReferenceImpl);
			}
			catch (\Exception $e)
			{
				$result = get_class($e);
			} finally {
				$this->assertEquals(InvalidContainerException::class, $result);
			}
		}
	}
}

final class ContainerUtilRemoveKeyTest extends TestCase
{

	public function testremoveKeyArrayCopy()
	{
		$source = array (
				'one' => 1,
				'two' => 2,
				'three' => 3,
				'four' => 4
		);
		$target = array (
				'one' => 1,
				'two' => 2,
				'four' => 4
		);
		$this->assertEquals($target, ContainerUtil::removeKey($source, 'three', ContainerUtil::REMOVE_COPY));
	}

	public function testremoveKeyArrayInplace()
	{
		$source = array (
				'one' => 1,
				'two' => 2,
				'three' => 3,
				'four' => 4
		);
		$target = array (
				'one' => 1,
				'two' => 2,
				'four' => 4
		);
		ContainerUtil::removeKey($source, 'three', ContainerUtil::REMOVE_INPLACE);
		$this->assertEquals($target, $source);
	}

	public function testremoveKeyArrayInplace2()
	{
		$source = array (
				'one',
				'two',
				'three',
				'four'
		);
		$target = array (
				0 => 'one',
				1 => 'two',
				3 => 'four'
		);
		ContainerUtil::removeKey($source, 2, ContainerUtil::REMOVE_INPLACE);
		$this->assertEquals($target, $source);
	}
}
