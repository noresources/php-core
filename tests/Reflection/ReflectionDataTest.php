<?php
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionData;
use NoreSources\Reflection\ReflectionService;
use NoreSources\Reflection\ReflectionServiceInterface;

class GuineaPig
{

	/**
	 * Private property with read method
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Get guinea pig ID
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	public $publicProperty = 'public';

	/**
	 * A simple RW public property
	 *
	 * @var string
	 */
	public $uninitializedPublicProperty;

	/**
	 * A private property without setter nor getter method
	 *
	 * @var string
	 */
	private $privateProperty = 'private';

	/**
	 * A private property with setter and getter.
	 *
	 * @var mixed
	 */
	private $uninitializedPrivateProperty;

	public function getUninitializedPrivateProperty()
	{
		return $this->uninitializedPrivateProperty;
	}

	public function setUninitializedPrivateProperty($value)
	{
		$this->uninitializedPrivateProperty = $value;
	}

	public $publicPropertyWithWriteMethod = 'initial';

	public function setPublicPropertyWithWriteMethod($value)
	{
		$this->publicPropertyWithWriteMethod = $value .
			' set with write method';
	}

	/**
	 * A pseudo read-only property
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Guinea pig';
	}
}

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class ReflectionDataTest extends \PHPUnit\Framework\TestCase
{

	public function testGuineaPig()
	{
		$service = new ReflectionService();

		$rw = ReflectionService::RW;
		$allowMethods = ReflectionService::ALLOW_RW_METHODS;

		$tests = [
			[
				'property' => 'publicProperty',
				'flags' => $rw | $allowMethods,
				'initial' => 'public',
				'set' => 'modified'
			],
			[
				'property' => 'privateProperty',
				'class' => ReflectionProperty::class,
				'initial' => 'private',
				'set' => 'foo',
				'get' => 'foo'
			],
			'$id without using methods' => [
				'property' => 'id',
				'class' => ReflectionProperty::class,
				'flags' => $rw,
				'initial' => null,
				'set' => 'gupy',
				'get' => 'gupy'
			],
			'$id with read method' => [
				'property' => 'id',
				'class' => ReflectionData::class,
				'initial' => null,
				'set' => 'gupy',
				'get' => 'gupy'
			],
			[
				'property' => 'name',
				'flags' => ReflectionService::READABLE |
				ReflectionService::ALLOW_READ_METHOD,
				'class' => ReflectionData::class,
				'set' => ReflectionException::class,
				'get' => 'Guinea pig'
			],
			'read-only $name property' => [
				'property' => 'name',
				// default flags attempt to get a RW reflection data
				'class' => \ReflectionException::class
			],
			[
				'property' => 'publicPropertyWithWriteMethod',
				'class' => \ReflectionProperty::class,
				'initial' => 'initial',
				'set' => 'changed',
				'get' => 'changed'
			],
			'$ublicPropertyWithWriteMethod force use methods' => [
				'property' => 'publicPropertyWithWriteMethod',
				'flags' => 0x666,
				'initial' => 'initial',
				'set' => 'value',
				'get' => 'value set with write method'
			]
		];

		foreach ($tests as $label => $test)
		{
			$propertyName = $test['property'];
			$object = new GuineaPig();
			$label = \is_integer($label) ? ('$' . $propertyName .
				' property') : $label;
			$flags = Container::keyValue($test, 'flags',
				$rw | $allowMethods);
			$className = Container::keyValue($test, 'class',
				Reflector::class);
			$reflectionProperty = null;
			try
			{
				$reflectionProperty = $service->getReflectionProperty(
					GuineaPig::class, $propertyName, $flags);
			}
			catch (\Exception $e)
			{
				$reflectionProperty = $e;
			}

			$moreInfos = '';
			if ($reflectionProperty instanceof \Exception)
				$moreInfos .= PHP_EOL . \basename($e->getFile()) . ':' .
					$e->getLine() . ' ' . $e->getMessage();
			$this->assertInstanceOf($className, $reflectionProperty,
				$label . ' reflection class' . $moreInfos);

			if (Container::keyExists($test, 'initial'))
			{
				$actual = $reflectionProperty->getValue($object);
				$expected = $test['initial'];
				$this->assertEquals($expected, $actual,
					$label . ' initial value');
			}

			if (Container::keyExists($test, 'set'))
			{
				try
				{
					$reflectionProperty->setValue($object, $test['set']);
				}
				catch (\Exception $e)
				{
					$this->assertInstanceOf($test['set'], $e,
						$label .
						' was expected to throw a given exception class');
				}
			}

			if (Container::keyExists($test, 'get'))
			{
				$actual = $reflectionProperty->getValue($object);
				$expected = $test['get'];
				$this->assertEquals($expected, $actual,
					$label . ' value');
			}
		}
	}

	public function testImplementation()
	{
		$reflectionProperty = new ReflectionClass(
			ReflectionProperty::class);
		$reflectionPropertyMethods = $reflectionProperty->getMethods();

		$service = new ReflectionService();
		$propertyNames = [
			'publicProperty',
			'privateProperty'
		];

		$data = [];

		foreach ($propertyNames as $propertyName)
		{
			$flags = (ReflectionServiceInterface::RW |
				ReflectionServiceInterface::ALLOW_RW_METHODS);
			$reflectionData = $service->getReflectionProperty($this,
				$propertyName, $flags);
			$this->assertInstanceOf(Reflector::class, $reflectionData,
				'ReflectionProperty for $' . $propertyName);
			foreach ($reflectionPropertyMethods as $method)
			{
				/**
				 *
				 * @var ReflectionMethod $method
				 */
				$name = $method->getName();

				if (\substr($name, 0, 2) == '__')
					continue;

				$parameters = $method->getParameters();
				$args = [];
				$invoke = false;
				if (\count($parameters) == 1 &&
					($parameter = $parameters[0]) &&
					$parameter->getName() == 'object')
				{
					$invoke = true;
					$args[] = $this;
				}
				elseif (\count($parameters) == 0)
					$invoke = true;

				if (!$invoke)
					continue;

				$errorMessage = '';
				try
				{
					\call_user_func_array([
						$reflectionData,
						$name
					], $args);
					$errorMessage = 'OK';
				}
				catch (\Exception $e)
				{
					$errorMessage = $e->getMessage();
				}
				$this->assertEquals('OK', $errorMessage,
					'Invoking ' . $name . ' on $' . $propertyName .
					' with ' . \count($args) . ' arguments');
			}
		}
	}

	public $publicProperty = 'public';

	public function getPrivateProperty()
	{
		return $this->privateProperty;
	}

	private $privateProperty = 'private';
}
