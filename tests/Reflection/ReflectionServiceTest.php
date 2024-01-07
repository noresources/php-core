<?php
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionService;
use NoreSources\Reflection\ReflectionServiceInterface;
use NoreSources\Type\TypeDescription;

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
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

class ReflectionServiceTest extends \PHPUnit\Framework\TestCase
{

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
}
