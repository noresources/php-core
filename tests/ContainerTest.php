<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\Container\Container;
use NoreSources\Container\ContainerPropertyInterface;
use NoreSources\Container\InvalidContainerException;
use NoreSources\Type\TypeDescription;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements
	NotFoundExceptionInterface
{

	public function __construct()
	{}
}

/**
 * The class is, by default not traversable because it does not have any public prperty
 */
class OpaqueClass
{

	private $number;
}

/**
 * This class is traversable because it has a public property
 */
class PublicClass
{

	public $value = 'public';
}

class MetaVariable implements ContainerPropertyInterface
{

	public function getContainerProperties()
	{
		return (Container::TRAVERSABLE | Container::PROPERTY_ACCESS);
	}

	public $foo = 'bar';
}

class ArrayAccessImpl implements \ArrayAccess
{

	public function __construct($t = array())
	{
		$this->table = $t;
	}

	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return \array_key_exists($offset, $this->table);
	}

	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->table[$offset];
	}

	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		$this->table[$offset] = $value;
	}

	#[\ReturnTypeWillChange]
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

class TraversableImpl implements \IteratorAggregate
{

	public function __construct($array)
	{
		$this->array = new \ArrayObject($array);
	}

	#[\ReturnTypeWillChange]
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

class ArrayObjectWithGetMethod extends \ArrayObject
{

	public $hardCodedProperty = 'hard';

	public function __get($key)
	{
		switch ($key)
		{
			case 'toto':
				return 'titi';
			case 'dynamicProperty':
				return 'Dynamic';
		}

		throw new \InvalidArgumentException(
			$key . ' is not a dynamic property key of ' .
			TypeDescription::getLocalName($this));
	}
}

final class ContainerTest extends \PHPUnit\Framework\TestCase
{

	public function __construct($name = null, array $data = [],
		$dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->indexedReference = array(
			"zero",
			"one",
			"two",
			"three"
		);
		$this->sparseIndexedReference = array(
			0 => "zero",
			1 => "one",
			3 => "three"
		);
		$this->hashReference = array(
			"one" => 1,
			"two" => 2,
			"the great answer" => "Fourty two"
		);
		$this->hashReferenceObject = new \ArrayObject(
			$this->hashReference);
		$this->indexedReferenceObject = new \ArrayObject(
			$this->indexedReference);
		$this->sparseIndexedReferenceObject = new \ArrayObject(
			$this->sparseIndexedReference);

		$this->nullValue = null;

		$this->hashReferenceImpl = new ArrayAccessImpl(
			$this->hashReference);
	}

	public function testKeyValue()
	{
		$array = [
			'key' => 'value',
			'foo' => 'bar',
			'toto' => 'titi'
		];

		$ci = new ContainerImpl($array);
		$aa = new ArrayAccessImpl($array);
		$metavariable = new MetaVariable();

		$arrayWithGetMethod = new ArrayObjectWithGetMethod(
			[
				'foo' => 'bar',
				'key' => 'value'
			]);

		foreach ([
			$array,
			$ci,
			$aa,
			$metavariable
		] as $container)
		{
			$this->assertEquals('bar',
				Container::keyValue($container, 'foo'),
				'keyValue of ' .
				TypeDescription::getLocalName($container));
		}

		$this->assertEquals('default',
			Container::keyValue($arrayWithGetMethod, 'undefined',
				'default'),
			'Catch exception when attempting to get object property');

		$hardCodedProperty = Container::keyValue($arrayWithGetMethod,
			'hardCodedProperty');
		$this->assertEquals('hard', $hardCodedProperty,
			'Initial arrayWithGetMethod::$hardCodedProperty');

		$arrayWithGetMethod['hardCodedProperty'] = 'soft';
		$hardCodedProperty = Container::keyValue($arrayWithGetMethod,
			'hardCodedProperty');
		$this->assertEquals('soft', $hardCodedProperty,
			'After offsetSet arrayWithGetMethod::$hardCodedProperty');
	}

	public function testTreeValue()
	{
		$tests = [
			'Basic =ey value' => [
				'container' => [
					'key' => 'value'
				],
				'tree' => 'key',
				'expected' => 'value'
			],
			'Missing key' => [
				'container' => [
					'key' => 'value'
				],
				'tree' => 'not-a-key',
				'expected' => 'not-found'
			],
			'Valid key tree' => [
				'container' => [
					'foo' => [
						'bar' => [
							'baz' => 'meta'
						]
					]
				],
				'tree' => 'foo.bar.baz',
				'expected' => 'meta'
			],
			'Partial key tree' => [
				'container' => [
					'foo' => [
						'bar' => [
							'bouzz' => 'pun'
						]
					]
				],
				'tree' => 'foo.bar.baz',
				'expected' => 'not-found'
			],
			'Invalid key tree' => [
				'container' => [
					'foo' => [
						'bar' => 'baz'
					]
				],
				'tree' => 'foo.bar.baz',
				'expected' => InvalidContainerException::class
			]
		];

		foreach ($tests as $label => $test)
		{
			$container = $test['container'];
			$keyTree = $test['tree'];
			$expected = $test['expected'];

			$dflt = \array_key_exists('default', $test) ? $test['default'] : 'not-found';
			$keySeparator = \array_key_exists('separator', $test) ? $test['separator'] : '.';
			$actual = null;

			foreach ([
				$container,
				new \ArrayObject($container)
			] as $container)
			{

				try
				{
					$actual = Container::treeValue($container, $keyTree,
						$dflt, $keySeparator);
				}
				catch (\Exception $e)
				{
					$actual = TypeDescription::getName($e);
				}
				$this->assertEquals($expected, $actual,
					$label . '(' . TypeDescription::getName($container) .
					')');
			}
		}
	}

	public function testPropertines()
	{
		$properties = [
			Container::COUNTABLE => 'countable',
			Container::MODIFIABLE => 'modifiable',
			Container::EXTENDABLE => 'extendable',
			Container::SHRINKABLE => 'shrinkable',
			Container::RANDOM_ACCESS => 'random access',
			Container::TRAVERSABLE => 'traversable'
		];

		$tests = [
			'array' => [
				'container' => [
					'Hello'
				],
				'expected' => [
					Container::COUNTABLE,
					Container::EXTENDABLE,
					Container::SHRINKABLE,
					Container::OFFSET_ACCESS,
					Container::TRAVERSABLE
				]
			],
			'ArrayObject' => [
				'container' => new \ArrayObject([
					'Hello'
				]),
				'expected' => [
					Container::COUNTABLE,
					Container::EXTENDABLE,
					Container::SHRINKABLE,
					Container::OFFSET_ACCESS,
					Container::TRAVERSABLE
				]
			],
			'ContainerInterface' => [
				'container' => new ContainerImpl([
					'foo' => 'bar'
				]),
				'expected' => [
					Container::RANDOM_ACCESS
				]
			],
			'MetaVariable object' => [
				'container' => new MetaVariable(),
				'expected' => [
					Container::PROPERTY_ACCESS,
					Container::TRAVERSABLE
				]
			],
			'OpaqueClassobject' => [
				'container' => new OpaqueClass(),
				'expected' => []
			],
			'PublicClassobject' => [
				'container' => new PublicClass(),
				'expected' => [
					Container::TRAVERSABLE
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$container = $test['container'];
			$e = $test['expected'];
			$expected = 0;
			foreach ($e as $v)
				$expected |= $v;
			$actual = Container::properties($container);

			foreach ($properties as $property => $name)
			{
				$expectedValue = $expected & $property;
				$text = $label . ' is ' . ($expectedValue ? '' : 'not ') .
					$name;
				$this->assertEquals($expectedValue, $actual & $property,
					$text);
			}
		}
	}

	public function testPop()
	{
		$container = [
			'foo',
			'bar',
			'baz'
		];

		foreach ([
			$container,
			new \ArrayObject($container)
		] as $container)
		{
			foreach ([
				'baz',
				'bar',
				'foo'
			] as $pass => $expected)
			{
				$label = TypeDescription::getName($container) . ' pass ' .
					($pass + 1) . ' (' .
					Container::implodeValues($container, ', ') . ')';
				$actual = Container::pop($container);
				$this->assertEquals($expected, $actual, $label);
			}
		}
	}

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
		$o = new OpaqueClass();
		$this->assertEquals(false, Container::isArray($o));
	}

	public function testValueExists()
	{
		$this->assertEquals(true,
			Container::valueExists($this->indexedReference, 'two'));
		$this->assertEquals(false,
			Container::valueExists($this->indexedReference, 'deux'));
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
		$a = new \ArrayObject(
			[
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

		$this->assertInstanceOf(\InvalidArgumentException::class,
			$exception);
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

		$this->assertInstanceOf(InvalidContainerException::class,
			$exception);
	}

	public function testSetValueBothPropertyAndArrayAccess()
	{
		$o = new ArrayObjectWithGetMethod();
		Container::setValue($o, 'hardCodedProperty', 'foo');
		$this->assertEquals('foo', $o->hardCodedProperty,
			'Property value after Container::setValue');
		$this->assertArrayHasKey('hardCodedProperty', $o,
			'ArrayAccess key set after Container::setValue');
		$this->assertEquals('foo', $o['hardCodedProperty'],
			'ArrayAccess[hardCodedProperty]');
	}

	public function testAppendArray()
	{
		$a = [
			1,
			2,
			3
		];
		Container::appendValue($a, 4);
		$this->assertEquals([
			1,
			2,
			3,
			4
		], $a, 'Append to array');
	}

	public function testAppendArrayObject()
	{
		$a = new \ArrayObject([
			1,
			2,
			3
		]);
		Container::appendValue($a, 4);
		$this->assertEquals([
			1,
			2,
			3,
			4
		], $a->getArrayCopy(), 'Append to ArrayObject');
	}

	public function testAppendToDateTime()
	{
		$a = new \DateTime('now');
		$this->expectException(InvalidContainerException::class);
		Container::appendValue($a, 4);
	}

	public function testPrependArray()
	{
		$a = [
			2,
			3
		];
		Container::prependValue($a, 1);
		$this->assertEquals([
			1,
			2,
			3
		], $a, 'Prepend to array');
	}

	public function testPrependArrayObject()
	{
		$a = new \ArrayObject([
			2,
			3
		]);
		Container::prependValue($a, 1);
		$this->assertEquals([
			1,
			2,
			3
		], $a->getArrayCopy(), 'Prepend to ArrayObject');
	}

	public function testPrependArrayAccess()
	{
		$a = new ArrayAccessImpl([
			2,
			3
		]);
		$this->expectException(InvalidContainerException::class);
		Container::prependValue($a, 1);
	}

	public function testCountArray()
	{
		$this->assertEquals(4, Container::count($this->indexedReference));
	}

	public function testCountAssociativeArray()
	{
		$this->assertEquals(3,
			Container::count($this->sparseIndexedReference));
	}

	public function testCountHashTable()
	{
		$this->assertEquals(3, Container::count($this->hashReference));
	}

	public function testCountArrayObject()
	{
		$this->assertEquals(4,
			Container::count($this->indexedReferenceObject));
	}

	public function testCountAssociativeArrayObject()
	{
		$this->assertEquals(3,
			Container::count($this->sparseIndexedReferenceObject));
	}

	public function testCountHashTableObject()
	{
		$this->assertEquals(3,
			Container::count($this->hashReferenceObject));
	}

	public function testCountHashTableArrayAccess()
	{
		if (\is_callable(array(
			$this,
			'expectException'
		)))
		{
			$this->expectException(InvalidContainerException::class);
			Container::count($this->hashReferenceImpl);
		}
		else
		{
			$result = null;
			try
			{
				$result = Container::count($this->hashReferenceImpl);
			}
			catch (\Exception $e)
			{
				$result = get_class($e);
			}
			finally {
				$this->assertEquals(InvalidContainerException::class,
					$result);
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

		$this->assertEquals($expectedKeys, Container::keys($array),
			'Container::keys (array)');
		$this->assertEquals($expectedKeys, Container::keys($arrayObject),
			'Container::keys (ArrayObject)');
		$this->assertEquals($expectedKeys, Container::keys($traversable),
			'Container::keys (TraversableImpl)');

		$this->assertEquals($expectedValues, Container::values($array),
			'Container::getValue(array)');
		$this->assertEquals($expectedValues,
			Container::values($arrayObject),
			'Container::getValue(ArrayObject)');
		$this->assertEquals($expectedValues,
			Container::values($traversable),
			'Container::getValue(TraversableImpl)');

		$this->expectException(InvalidContainerException::class);
		Container::keys($container);
	}

	public function testImplodeValueBasic()
	{
		$builtin = \implode(', ', $this->indexedReference);
		$ns = Container::implodeValues($this->indexedReference, ', ');

		$this->assertEquals($builtin, $ns,
			'implodeValue basically mimics the implode function');
	}

	public function testFilter()
	{
		$series = [
			1,
			2,
			3,
			4,
			5,
			6
		];
		$seriesObject = new \ArrayObject($series);
		$evenValues = [
			1 => 2,
			3 => 4,
			5 => 6
		];
		$evenKeys = [
			0 => 1,
			2 => 3,
			4 => 5
		];

		$oddFilter = function ($v) {
			return ($v % 2) == 0;
		};

		$this->assertEquals($evenValues,
			Container::filterValues($series, $oddFilter),
			'Filter odd array values');
		$this->assertEquals($evenValues,
			Container::filterValues($seriesObject, $oddFilter),
			'Filter odd ArrayObject values');

		$this->assertEquals($evenKeys,
			Container::filterKeys($series, $oddFilter),
			'Filter odd array keys');
		$this->assertEquals($evenKeys,
			Container::filterKeys($seriesObject, $oddFilter),
			'Filter odd ArrayObject keys');

		$this->assertEquals([
			1 => 2,
			2 => 3
		],
			Container::filter($series,
				function ($k, $v) {
					return $k == 2 || $v == 2;
				}), 'Filter two only');
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

		$this->assertEquals($evenStrings, $evenStringsResult,
			'Filter odd keys and false values');

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

		$this->assertEquals($stringValues, $stringValuesResult,
			'Only string values');
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

		$this->assertFalse(Container::removeKey($source, 'Kowabunga'),
			'Remove non-existing');

		$this->assertTrue(Container::removeKey($source, 'three'),
			'Remove existing');

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
		$this->assertTrue(Container::removeKey($source, 2),
			'Remove existing');
		$this->assertEquals($target, $source);
	}

	public function testAssoc()
	{
		$this->assertTrue(Container::isIndexed([]),
			'Empty container is indexed by default');
		$this->assertTrue(Container::isAssociative([]),
			'Empty container is associative by default');
		$this->assertFalse(Container::isIndexed([], false, false),
			'Empty container is indexed if asked');
		$this->assertFalse(Container::isAssociative([], false, false),
			'Empty container is associative if asked');

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

		$this->assertTrue(Container::isIndexed($indexed),
			'Indexed is indexed');
		$this->assertTrue(Container::isIndexed($indexed, true),
			'Indexed is indexed (strict)');

		$this->assertTrue(Container::isIndexed($explicitIndexes),
			'Explicitely indexed is indexed');
		$this->assertTrue(Container::isIndexed($explicitIndexes, true),
			'Explicitely indexed is indexed (strict)');

		$this->assertTrue(Container::isIndexed($numericStringIndexes),
			'numeric string indexes is indexed');
		$this->assertTrue(
			Container::isIndexed($numericStringIndexes, true),
			'numeric string indexes (strict) is still indexed due to PHP auto conversion');

		$this->assertFalse(Container::isIndexed($unordered),
			'Unordered is not indexed');
		$this->assertFalse(Container::isIndexed($unordered, true),
			'Unordered is not indexed (strict)');

		$this->assertFalse(Container::isAssociative($indexed),
			'Indexed is not associative');
		$this->assertFalse(Container::isAssociative($indexed, true),
			'Indexed is not associative (strict)');

		$this->assertFalse(Container::isAssociative($explicitIndexes),
			'Explicitely indexed is not associative');
		$this->assertFalse(
			Container::isAssociative($explicitIndexes, true),
			'Explicitely indexed is not associative (strict)');

		$this->assertTrue(Container::isAssociative($unordered),
			'Unordered is associative');
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

		$this->assertFalse(Container::isIndexed($associative),
			'Associative is not indexed');
		$this->assertTrue(Container::isAssociative($associative));

		$this->assertFalse(Container::isIndexed($mixed),
			'mixed is not indexed');
		$this->assertTrue(Container::isAssociative($mixed),
			'mixed is associative');
	}

	public function testNthValue()
	{
		$this->assertEquals('Three',
			Container::nthValue([
				'One',
				"Two",
				'Three',
				'Four'
			], 2), '3rd value of array');

		$this->assertEquals('Two',
			Container::nthValue(
				[
					0 => 'One',
					5 => "Two",
					3 => 'Three',
					42 => 'Four'
				], 1),
			'2nd value of associative array with integer keys');

		$this->assertEquals(null,
			Container::nthValue([
				'One',
				'Four'
			], 15), '3rd value of array');
	}

	public function testFirstAndLast()
	{
		$tests = [
			'empty' => [
				'collection' => [],
				'key' => null,
				'value' => null,
				'lastKey' => null,
				'lastValue' => null
			],
			'empty with default' => [
				'collection' => [],
				'key' => 'not a key',
				'value' => 'not a value',
				'default' => [
					'not a key',
					'not a value'
				],
				'lastKey' => 'not a key',
				'lastValue' => 'not a value'
			],
			'indexed array' => [
				'collection' => [
					'first value',
					'second one',
					'third item'
				],
				'key' => 0,
				'value' => 'first value',
				'lastKey' => 2,
				'lastValue' => 'third item'
			],
			'associative array' => [
				'collection' => [
					'foo' => 'bar',
					'a' => 'b',
					0 => 'not the first'
				],
				'key' => 'foo',
				'value' => 'bar',
				'lastKey' => 0,
				'lastValue' => 'not the first'
			]
		];

		foreach ($tests as $label => $test)
		{
			$key = $test['key'];
			$value = $test['value'];
			$dflt = Container::keyValue($test, 'default', []);
			$lastKey = $test['lastKey'];
			$lastValue = $test['lastValue'];

			$array = $test['collection'];
			$object = new \ArrayObject($array);
			$iterator = $object->getIterator();
			$iterator->next();
			$current = $iterator->current();

			foreach ([
				'array' => $array,
				'ArrayObject' => $object,
				'Ietrator' => $iterator
			] as $type => $container)
			{
				$this->assertEquals([
					$key,
					$value
				], Container::first($container, $dflt),
					$label . ' ' . $type . ' first');

				$this->assertEquals($key,
					Container::firstKey($container,
						Container::keyValue($dflt, 0, null)),
					$label . ' ' . $type . ' firstKey');
				$this->assertEquals($value,
					Container::firstValue($container,
						Container::keyValue($dflt, 1, null)),
					$label . ' ' . $type . ' firstValue');

				$this->assertEquals([
					$lastKey,
					$lastValue
				], Container::last($container, $dflt),
					$label . ' ' . $type . ' last');
				$this->assertEquals($lastKey,
					Container::lastKey($container,
						Container::keyValue($dflt, 0, null)),
					$label . ' ' . $type . ' last key');
				$this->assertEquals($lastValue,
					Container::lastValue($container,
						Container::keyValue($dflt, 1, null)),
					$label . ' ' . $type . ' last value');
			}

			$this->assertEquals($current, $iterator->current(),
				'Unchanged iterator');
		}
	}

	public function testCreateArrayException()
	{
		$this->expectException(InvalidContainerException::class);
		$o = new OpaqueClass();
		$a = Container::createArray($o);
	}

	public function testCreateArrayException2()
	{
		$this->expectException(InvalidContainerException::class);
		$a = Container::createArray(5);
	}

	public function testCreateArray()
	{
		$value = 5;
		$this->assertEquals([
			'value' => $value
		], Container::createArray($value, 'value'), 'Literal value');

		$object = new MetaVariable();
		$this->assertEquals([
			'foo' => 'bar'
		], Container::createArray($object), 'Traversable object');

		$a = [
			'one' => 1,
			2 => 'two'
		];
		$this->assertEquals($a, Container::createArray($a), 'Array');
	}

	public function testMap()
	{
		$input = [
			'foo' => 'bar',
			'bar' => 'baz'
		];

		$expected = [
			'foo' => 'foo.BAR',
			'bar' => 'bar.BAZ'
		];

		$actual = Container::map($input,
			function ($k, $v) {
				return $k . '.' . \strtoupper($v);
			});

		$this->assertEquals($expected, $actual,
			'map without aadditional arguments');

		// mapValues
		$expected = [
			'foo' => 'BAR',
			'bar' => 'BAZ'
		];

		$actual = Container::mapValues($input, '\strtoupper');

		$this->assertEquals($expected, $actual,
			'mapValues without aadditional arguments');

		// map with args

		$expected = [
			'foo' => 'foo.BAR.suffix',
			'bar' => 'bar.BAZ.suffix'
		];

		$actual = Container::map($input,
			function ($k, $v, $suffix) {
				return $k . '.' . \strtoupper($v) . '.' . $suffix;
			}, 'suffix');

		$this->assertEquals($expected, $actual,
			'map with aadditional arguments');

		Container::walk($input,
			function ($k, $v, $suffix) {
				return $k . '.' . \strtoupper($v) . '.' . $suffix;
			}, 'suffix');

		$this->assertEquals($expected, $input, 'Walk');
	}

	public function testSorts()
	{
		$tests = [
			'asort' => [
				'input' => [
					5,
					2,
					4,
					3,
					1
				],
				'expected' => [
					4 => 1,
					1 => 2,
					3 => 3,
					2 => 4,
					0 => 5
				]
			],
			'ksort' => [
				'input' => [
					'c' => 3,
					'b' => 2,
					'a' => 1
				],
				'expected' => [
					'a' => 1,
					'b' => 2,
					'c' => 3
				]
			],
			'uksort' => [
				'input' => [
					'b' => 2,
					'a' => 1,
					'c' => 3
				],
				'expected' => [
					'c' => 3,
					'b' => 2,
					'a' => 1
				],
				'args' => [
					function ($a, $b) {
						return \strcmp($b, $a);
					}
				]
			]
		];

		foreach ($tests as $f => $test)
		{
			$arrayInput = $test['input'];
			$objectInput = new \ArrayObject($arrayInput);
			$arrayExpected = $test['expected'];
			$objectExpected = new \ArrayObject($arrayExpected);

			$arrayArgs = [
				&$arrayInput
			];
			$objectArgs = [
				&$objectInput
			];
			if (Container::keyExists($test, 'args'))
			{
				$arrayArgs = \array_merge($arrayArgs, $test['args']);
				$objectArgs = \array_merge($objectArgs, $test['args']);
			}

			\call_user_func_array([
				Container::class,
				$f
			], $arrayArgs);
			$this->assertEquals($arrayExpected, $arrayInput,
				$f . ' array');

			\call_user_func_array([
				Container::class,
				$f
			], $objectArgs);
			$this->assertEquals($objectExpected, $objectInput,
				$f . ' ArrayObject');
		}
	}

	public function testUniqueValues()
	{
		$tests = [
			'no changes' => [
				'input' => [
					'a',
					'b',
					'c',
					'd'
				]
			],
			'duplicate' => [
				'input' => [
					'a',
					'b',
					'a',
					'd'
				],
				'expected' => [
					'a',
					'b',
					'd'
				]
			],
			'even or ...' => [
				'input' => [
					1,
					2,
					2,
					3,
					3,
					4,
					4,
					5,
					5,
					6,
					6
				],
				'expected' => [
					1,
					2,
					2,
					3,
					3,
					4,
					4,
					5,
					6,
					6
				],
				'comparer' => function ($a, $b, $orValue) {
					$c = ($a == $b) && (($a % 2 == 1) && ($a != 3));
					return $c;
				},
				'arguments' => [
					3
				]
			]
		];
		foreach ($tests as $label => $test)
		{
			$container = $test['input'];
			$expected = Container::keyValue($test, 'expected',
				$container);
			$comparer = Container::keyValue($test, 'comparer');
			$arguments = Container::keyValue($test, 'arguments', []);

			$actual = \call_user_func_array(
				[
					Container::class,
					'uniqueValues'
				], \array_merge([
					$container,
					$comparer
				], $arguments));

			if ($comparer === null && \count($arguments) == 0)
			{
				$arrayUnique = \array_unique($container);
				$this->assertEquals($arrayUnique, $actual,
					$label .
					' should returns the same result as array_unique');
			}

			$expected = \implode(', ', $expected);
			$actual = \implode(', ', $actual);

			$this->assertEquals($expected, $actual, $label);
		}
	}

	public function testMerge()
	{
		$mapA = [
			'key' => 'value',
			'foo' => 'bar'
		];
		$mapB = [
			'bar' => 'baz'
		];
		$listA = [
			'a',
			'b',
			'c'
		];

		$listB = [
			'apple',
			'banana',
			'tomato'
		];

		$expected = \array_merge($mapA, $mapB, $listA, $listB);
		$expected = \json_encode($expected, JSON_PRETTY_PRINT);

		$actual = Container::merge($mapA, $mapB, $listA, $listB);
		$actual = \json_encode($actual, JSON_PRETTY_PRINT);

		$this->assertEquals($expected, $actual,
			'Default merge option mimics array_merge');

		$options = Container::MERGE_LIST_REPLACE;
		$expected = [
			'key' => 'value',
			'foo' => 'bar',
			'bar' => 'baz',
			'a',
			'b',
			'c',
			'apple',
			'banana',
			'tomato'
		];

		$actual = Container::merge($mapA, $mapB, $listA, $listB,
			$options);

		$this->assertEquals($expected, $actual, 'Merge / replace list');
		$expected = [
			'apple',
			'banana',
			'tomato',
			'key' => 'value',
			'foo' => 'bar',
			'bar' => 'baz'
		];
		$expected = \json_encode($expected, JSON_PRETTY_PRINT);

		$actual = Container::merge($listA, $listB, $mapA, $mapB,
			$options);
		$actual = \json_encode($actual, JSON_PRETTY_PRINT);

		$this->assertEquals($expected, $actual,
			'Merge / replace list (2)');
	}

	public function testMergeRecursive()
	{
		$a = [
			'key' => 'value',
			'a' => [
				'b' => [
					'C'
				],
				'd' => 'Dis'
			]
		];
		$b = [
			'a' => [
				'b' => [
					'Sea'
				],
				'g' => [
					'Dji'
				]
			]
		];
		$options = Container::MERGE_RECURSE;

		$expected = \array_merge_recursive($a, $b);
		$expected = \json_encode($expected, JSON_PRETTY_PRINT);

		$actual = Container::merge($a, $b, $options);
		$actual = \json_encode($actual, JSON_PRETTY_PRINT);

		$this->assertEquals($expected, $actual,
			'Default recursive merge mimics array_merge_recursive');
	}
}


