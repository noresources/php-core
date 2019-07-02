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

final class ArrayUtilIsArrayTest extends TestCase
{

	public function testPODArrayIsArray()
	{
		$this->assertEquals(true, ArrayUtil::isArray(array ()));
	}

	public function testArrayObjectIsArray()
	{
		$o = new \ArrayObject();
		$this->assertEquals(true, ArrayUtil::isArray($o));
	}

	public function testArrayAccessIsArray()
	{
		$o = new ArrayAccessImpl();
		$this->assertEquals(true, ArrayUtil::isArray($o));
	}

	public function testClassInstanceIsArray()
	{
		$o = new SimpleClass();
		$this->assertEquals(false, ArrayUtil::isArray($o));
	}
}

final class ArrayUtilCountTest extends TestCase
{

	public function testCountArray()
	{
		global $indexedReference;
		$this->assertEquals(4, ArrayUtil::count($indexedReference));
	}

	public function testCountAssociativeArray()
	{
		global $sparseIndexedReference;
		$this->assertEquals(3, ArrayUtil::count($sparseIndexedReference));
	}

	public function testCountHashTable()
	{
		global $hashReference;
		$this->assertEquals(3, ArrayUtil::count($hashReference));
	}

	public function testCountArrayObject()
	{
		global $indexedReferenceObject;
		$this->assertEquals(4, ArrayUtil::count($indexedReferenceObject));
	}

	public function testCountAssociativeArrayObject()
	{
		global $sparseIndexedReferenceObject;
		$this->assertEquals(3, ArrayUtil::count($sparseIndexedReferenceObject));
	}

	public function testCountHashTableObject()
	{
		global $hashReferenceObject;
		$this->assertEquals(3, ArrayUtil::count($hashReferenceObject));
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
			ArrayUtil::count($hashReferenceImpl);
		}
		else 
		{
			$result = null;
			try
			{
				$result = ArrayUtil::count($hashReferenceImpl);
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

final class ArrayUtilRemoveKeyTest extends TestCase
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
		$this->assertEquals($target, ArrayUtil::removeKey($source, 'three', ArrayUtil::REMOVE_COPY));
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
		ArrayUtil::removeKey($source, 'three', ArrayUtil::REMOVE_INPLACE);
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
		ArrayUtil::removeKey($source, 2, ArrayUtil::REMOVE_INPLACE);
		$this->assertEquals($target, $source);
	}
}
