<?php
namespace NoreSources;

use PHPUnit\Framework\TestCase;
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

$hashReferenceImpl = new ArrayAccessImpl($hashReference);

final class ContainerIsArrayTest extends TestCase
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
}

final class ContainerValueExistsTest extends TestCase
{

	public function testCountArray()
	{
		global $indexedReference;
		$this->assertEquals(true, Container::valueExists($indexedReference, 'two'));
		$this->assertEquals(false, Container::valueExists($indexedReference, 'deux'));
	}
}

final class ContainerValueSetTest extends TestCase
{

	public function testArray()
	{
		$a = [ 'hello' => 'everyone', 'good' => 'bye' ];
		Container::setValue($a, 'hello', 'world');
		$this->assertEquals('world', $a['hello']);
	}

	public function testArrayAccess()
	{
		$a = new \ArrayObject([ 'hello' => 'everyone', 'good' => 'bye' ]);
		Container::setValue($a, 'hello', 'world');
		$this->assertEquals('world', $a['hello']);
	}

	public function testArbitraryClass()
	{
		$a = new \stdClass();
		$a->hello = 'world';
		$a->good = 'bye';

		Container::setValue($a, 'hello', 'world');
		$this->assertEquals('world', $a->hello);
	}

	public function testArbitraryClassInvalidMember()
	{
		$a = new \stdClass();
		$a->hello = 'world';
		$a->good = 'bye';
		
		$this->expectException(\InvalidArgumentException::class);
		Container::setValue($a, 'undefied', 42);
	}

	public function testArbitraryClassInvalidKey()
	{
		$a = new \stdClass();
		$a->hello = 'world';
		$a->good = 'bye';
		
		$this->expectException(InvalidContainerException::class);
		Container::setValue($a, 2, 42);
	}
}

final class ContainerCountTest extends TestCase
{

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
			} finally {
				$this->assertEquals(InvalidContainerException::class, $result);
			}
		}
	}
}

final class ContainerRemoveKeyTest extends TestCase
{

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
}
