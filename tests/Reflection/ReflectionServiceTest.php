<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
use NoreSources\Bitset;
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionService;
use NoreSources\Reflection\ReflectionServiceInterface;
use NoreSources\Type\TypeDescription;

class BasicClass
{

	public $key = 'value';

	public $undefined;
}

class OpaqueClass
{

	private $secret = "I'm a teapot";
}

class ReadOnlyClass
{

	public function getSecret()
	{
		return $this->secret;
	}

	private $secret = "I'm a teapot";
}

class PublicPropertyWithReadMethodClass
{

	public $enabled;

	public $undefined;

	public function isEnabled()
	{
		return ($this->enabled ? true : false);
	}
}

class Ancestor
{

	public $ancestorName = 'Dumbo';

	private $familySecret = "I'm a teapot";
}

class DirectParent extends Ancestor
{

	public $parentName = 'Anakin';

	public function getFamilySecret()
	{
		return 'The force is strong in the family';
	}

	private $parentSecret = "I am your father";
}

class Child extends DirectParent
{

	public $name;

	public function setGender($g)
	{
		$this->gender = $g;
	}

	private $gender;
}

class ReflectionServiceTest extends \PHPUnit\Framework\TestCase
{

	public function testFlags()
	{
		$flags = [
			'readable' => ReflectionServiceInterface::READABLE,
			'writable' => ReflectionServiceInterface::WRITABLE,
			'hidden' => ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY,
			'allow read method' => ReflectionServiceInterface::ALLOW_READ_METHOD,
			'allow write method' => ReflectionServiceInterface::ALLOW_WRITE_METHOD,
			'inherit property' => ReflectionServiceInterface::EXPOSE_INHERITED_PROPERTY,
			'hidden' => ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY
			//'force read method' => ReflectionServiceInterface::FORCE_READ_METHOD,
			//'force write method' => ReflectionServiceInterface::FORCE_WRITE_METHOD
		];

		foreach ($flags as $name => $value)
		{
			foreach ($flags as $otherName => $otherValue)
			{
				if ($name == $otherName)
					continue;
				$this->assertEquals(0, ($value & $otherValue),
					$name . ' does not conflicts with ' . $otherName);
			}
		}
	}

	public function testGet()
	{
		$reflectionService = ReflectionService::getInstance();
		$public = new BasicClass();
		$opaque = new OpaqueClass();
		$readOnly = new ReadOnlyClass();
		$publicReadMethod = new PublicPropertyWithReadMethodClass();

		$properties = $reflectionService->getPropertyValues($opaque);
		$this->assertIsArray($properties, 'Get all properties');

		$tests = [
			'public property' => [
				'object' => $public,
				'property' => 'key',
				'expected' => 'value'
			],
			'opaque property' => [
				'object' => $opaque,
				'property' => 'secret',
				'expected' => null
			],
			'force expose opaque property' => [
				'object' => $opaque,
				'flags' => ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY,
				'property' => 'secret',
				'expected' => "I'm a teapot"
			],
			'getter' => [
				'object' => $readOnly,
				'property' => 'secret',
				'flags' => ReflectionServiceInterface::ALLOW_READ_METHOD,
				'expected' => "I'm a teapot"
			],
			'getter of public property' => [
				'object' => $publicReadMethod,
				'property' => 'enabled',
				'flags' => ReflectionServiceInterface::ALLOW_READ_METHOD,
				'expected' => null
			],
			'force getter of public property' => [
				'object' => $publicReadMethod,
				'property' => 'enabled',
				'flags' => ReflectionServiceInterface::FORCE_READ_METHOD,
				'expected' => false
			]
		];
		foreach ($tests as $label => $test)
		{
			$object = $test['object'];
			$propertyName = $test['property'];
			$expected = $test['expected'];
			$flags = Container::keyValue($test, 'flags', 0);

			$description = TypeDescription::getLocalName($object) . '::' .
				$propertyName . ' with flags 0x' . dechex($flags);

			$actual = $reflectionService->getPropertyValue($object,
				$propertyName, $flags);
			$this->assertEquals($expected, $actual,
				$description . ' (property name)');

			$class = $reflectionService->getReflectionClass($object);
			$property = $class->getProperty($propertyName);

			$actual = $reflectionService->getPropertyValue($object,
				$property, $flags);
			$this->assertEquals($expected, $actual, $description);
		}
	}

	public function testSet()
	{
		$reflectionService = ReflectionService::getInstance();
		$object = new OpaqueClass();

		$expected = [
			'secret' => "I'm a teapot"
		];
		$flags = ReflectionServiceInterface::RW |
			ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY;

		$actual = $reflectionService->getPropertyValues($object, $flags);
		$this->assertEquals($expected, $actual, 'Opaque initial values');

		$values = [
			'secret' => 'No so secret'
		];

		$reflectionService->setPropertyValues($object, $values, $flags);
		$actual = $reflectionService->getPropertyValues($object, $flags);
		$this->assertEquals($values, $actual,
			'Set opaque object properties');
	}

	public function testInherited()
	{
		$ancestor = new Ancestor();
		$anakin = new DirectParent();
		$luke = new Child();
		$leia = new Child();
		$luke->name = 'Luke';
		$luke->setGender('M');

		$tests = [
			[
				'object' => $ancestor,
				'expected' => [
					'ancestorName' => 'Dumbo'
				]
			],
			[
				'object' => $ancestor,
				'flags' => ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY,
				'expected' => [
					'ancestorName' => 'Dumbo',
					'familySecret' => "I'm a teapot"
				]
			],
			[
				'object' => $anakin,
				'expected' => [
					'ancestorName' => 'Dumbo',
					'parentName' => 'Anakin'
				]
			],
			[
				'object' => $anakin,
				'flags' => ReflectionServiceInterface::EXPOSE_INHERITED_PROPERTY,
				'expected' => [
					'ancestorName' => 'Dumbo',
					'parentName' => 'Anakin',
					'familySecret' => "I'm a teapot"
				]
			],
			[
				'description' => 'family secret property will be find by inheritance, then DirectParent getter will be invoked',
				'object' => $anakin,
				'flags' => ReflectionServiceInterface::EXPOSE_INHERITED_PROPERTY |
				ReflectionServiceInterface::ALLOW_READ_METHOD,
				'expected' => [
					'ancestorName' => 'Dumbo',
					'parentName' => 'Anakin',
					'familySecret' => 'The force is strong in the family'
				]
			]
		];
		$reflectionService = ReflectionService::getInstance();
		foreach ($tests as $label => $test)
		{
			$object = $test['object'];
			$expected = $test['expected'];
			$flags = Container::keyValue($test, 'flags', 0);
			$actual = $reflectionService->getPropertyValues($object,
				$flags);

			\ksort($actual);
			\ksort($expected);

			$className = TypeDescription::getLocalName($object);
			$label = $className . ' - ' .
				$this->getReflectionServiceFlagsDescription($flags);
			if (($description = Container::keyValue($test, 'description')))
				$label .= PHP_EOL . $description;
			$this->assertEquals($expected, $actual, $label);
		}

		$exposeFlags = ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY |
			ReflectionServiceInterface::EXPOSE_INHERITED_PROPERTY;
		$tests = [
			[
				'object' => new Ancestor(),
				'flags' => $exposeFlags,
				'expected' => [
					'ancestorName' => 'Lilith',
					'familySecret' => 'I am a demon'
				]
			],
			[
				'object' => new DirectParent(),
				'flags' => $exposeFlags,
				'expected' => [
					'ancestorName' => 'Lilith',
					'familySecret' => 'I am a demon',
					'parentName' => 'Gloim',
					'parentSecret' => 'My wife has a beard'
				]
			],
			[
				'object' => new DirectParent(),
				'flags' => $exposeFlags |
				ReflectionServiceInterface::ALLOW_RW_METHODS,
				'set' => [
					'familySecret' => 'I am a demon'
				],
				'expected' => [
					'ancestorName' => 'Dumbo',
					'familySecret' => 'The force is strong in the family',
					'parentName' => 'Anakin',
					'parentSecret' => "I am your father"
				]
			],
			[
				'object' => clone $luke,
				'flags' => $exposeFlags |
				ReflectionServiceInterface::ALLOW_RW_METHODS,
				'set' => [
					'familySecret' => 'I am a demon'
				],
				'expected' => [
					'ancestorName' => 'Dumbo',
					'familySecret' => 'The force is strong in the family',
					'parentName' => 'Anakin',
					'parentSecret' => "I am your father",
					'name' => 'Luke',
					'gender' => 'M'
				]
			]
		];

		foreach ($tests as $test)
		{
			$object = $test['object'];
			$expected = $test['expected'];
			$set = Container::keyValue($test, 'set', $expected);
			$label = TypeDescription::getLocalName($object) . ' - ' .
				$this->getReflectionServiceFlagsDescription($flags);

			$flags = Container::keyValue($test, 'flags', $exposeFlags);

			$reflectionService->setPropertyValues($object, $set,
				$flags | ReflectionServiceInterface::WRITABLE);
			$actual = $reflectionService->getPropertyValues($object,
				$flags | ReflectionServiceInterface::READABLE);
			\ksort($expected);
			\ksort($actual);
			$this->assertEquals($expected, $actual, $label);
		}
	}

	public static function getReflectionServiceFlagsDescription($flags)
	{
		if ($flags == 0)
			return 'Default';

		$bits = new Bitset($flags);
		$text = [];
		if ($bits->match(ReflectionServiceInterface::RW, true))
			$text[] = "RW";
		elseif ($bits->match(ReflectionServiceInterface::READABLE))
			$text[] = 'readable';
		elseif ($bits->match(ReflectionServiceInterface::WRITABLE))
			$text[] = 'writable';

		if ($bits->match(
			ReflectionServiceInterface::EXPOSE_INHERITED_PROPERTY |
			ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY))
		{
			$e = [];
			$i = [];
			if ($bits->match(
				ReflectionServiceInterface::EXPOSE_HIDDEN_PROPERTY))
				$e[] = 'hidden properties';
			if ($bits->match(
				ReflectionServiceInterface::EXPOSE_INHERITED_PROPERTY))
				$e[] = 'inherited properties';

			$text[] = 'expose ' .
				Container::implodeValues($e,
					[
						Container::IMPLODE_BETWEEN => ', ',
						Container::IMPLODE_BETWEEN_LAST => ' and '
					]);
		}

		if ($bits->match(ReflectionServiceInterface::ALLOW_RW_METHODS))
		{
			$m = [];
			if ($bits->getMaxIntegerValue(
				ReflectionServiceInterface::ALLOW_READ_METHOD))
				$m[] = 'getter';
			if ($bits->match(
				ReflectionServiceInterface::ALLOW_WRITE_METHOD))
				$m[] = 'setter';
			$text[] = 'allow ' . Container::implodeValues($m, ' and ') .
				' methods';
		}

		return Container::implodeValues($text, ', ');
	}
}
