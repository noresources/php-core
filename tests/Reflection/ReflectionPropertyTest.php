<?php
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionPropertyFactory;

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class ReflectionPropertyTest extends \PHPUnit\Framework\TestCase
{

	public $publicValue;

	/**
	 * Will be ignored because $publicValue is public
	 *
	 * @param unknown $value
	 */
	public function setPublicValue($value)
	{
		$this->publicValue = 'set.' . $value;
	}

	private $privateValue;

	/**
	 * ReadMethod will be ignored because method has a parameter
	 *
	 * @param unknown $option
	 * @return string
	 */
	public function getPrivateValue($option)
	{
		return 'get.' . $this->privateValue . '.' . $option;
	}

	private $privateProperty;

	public function getPrivateProperty()
	{
		if (isset($this->privateProperty))
			return 'get.' . $this->privateProperty;
		return 'undefined';
	}

	public function setPrivateProperty($value)
	{
		$this->privateProperty = 'set.' . $value;
	}

	public function testPublic()
	{
		$factory = new ReflectionPropertyFactory();

		$value = $expected = 'foo';
		$this->runReflectionFactoryTest($factory, 'publicValue',
			ReflectionPropertyFactory::MODE_RW, $value, $expected,
			'No refix set');

		$factory->setWriteMethodNamePrefixes([
			'set'
		]);
		$factory->setReadMethodNamePrefixes([
			'get',
			'is'
		]);

		$this->runReflectionFactoryTest($factory, 'publicValue',
			ReflectionPropertyFactory::MODE_RW, $value, $expected,
			'Public property ignores setters and getters');
	}

	public function testPrivateWithInvalidMethods()
	{
		$factory = new ReflectionPropertyFactory();
		$factory->setWriteMethodNamePrefixes([
			'set'
		]);
		$factory->setReadMethodNamePrefixes([
			'get',
			'is'
		]);

		$name = 'privateValue';
		foreach ([
			'Invalid getter prototype' => [
				ReflectionPropertyFactory::MODE_AUTO,
				'foo',
				$this->isReflectionPropertyAlwaysAccessible('foo',
					'Cannot access non-public member ReflectionPropertyTest::privateValue')
			],
			'Force RW' => [
				ReflectionPropertyFactory::MODE_RW,
				'foo',
				'foo'
			],
			'Force readable will NOT make it writable' => [
				ReflectionPropertyFactory::MODE_READ,
				'foo',
				$this->isReflectionPropertyAlwaysAccessible('foo', null)
			],
			'Force wriable will NOT make it readable (PHP < 8.1)' => [
				ReflectionPropertyFactory::MODE_WRITE,
				'foo',
				$this->isReflectionPropertyAlwaysAccessible('foo',
					'Cannot get value of non-public member ' . __CLASS__ .
					'::$' . $name)
			]
		] as $label => $arguments)
		{
			$args = \array_merge([
				$factory,
				$name
			], $arguments, [
				$label
			]);
			\call_user_func_array([
				$this,
				'runReflectionFactoryTest'
			], $args);
		}
	}

	public function testPropertySetGet()
	{
		$factory = new ReflectionPropertyFactory();
		$factory->setWriteMethodNamePrefixes([
			'set'
		]);
		$factory->setReadMethodNamePrefixes([
			'get',
			'is'
		]);

		$name = 'privateProperty';
		foreach ([
			'Property with get/get' => [
				ReflectionPropertyFactory::MODE_AUTO,
				'foo',
				'get.set.foo'
			]
		] as $label => $arguments)
		{
			$args = \array_merge([
				$factory,
				$name
			], $arguments, [
				$label
			]);
			\call_user_func_array([
				$this,
				'runReflectionFactoryTest'
			], $args);
		}
	}

	public function isReflectionPropertyAlwaysAccessible($yes, $no)
	{
		return (version_compare(PHP_VERSION, '8.1.0') >= 0) ? $yes : $no;
	}

	public function runReflectionFactoryTest(
		ReflectionPropertyFactory $factory, $name, $mode, $value,
		$expected, $label)
	{
		$this->publicValue = $this->privateValue = $this->privateProperty = null;
		$modeName = [];
		if ($mode)
			foreach ([
				ReflectionPropertyFactory::MODE_READ => 'readable',
				ReflectionPropertyFactory::MODE_WRITE => 'writable'
			] as $m => $n)
			{
				if (($mode & $m) == $m)
					$modeName[] = $n;
			}
		else
			$modeName = [
				'Auto'
			];

		$modeName = Container::implodeValues($modeName,
			[
				Container::IMPLODE_BETWEEN => ', ',
				Container::IMPLODE_BETWEEN_LAST => ' and '
			]);

		$label = 'Property ' . $name . ' with ' . $modeName . ' mode | ' .
			$label;

		$property = $factory->createReflectionProperty(self::class,
			$name, $mode);

		$this->assertInstanceOf(ReflectionProperty::class, $property);

		try
		{
			$property->setValue($this, $value);
		}
		catch (\Exception $e)
		{
			$this->assertTrue(
				($mode & ReflectionPropertyFactory::MODE_WRITE) == 0,
				$label . ' ~ Should be writable');
		}

		try
		{
			$actual = $property->getValue($this);
		}
		catch (\Exception $e)
		{
			$actual = $e->getMessage();
			$this->assertTrue(
				($mode & ReflectionPropertyFactory::MODE_READ) == 0,
				$label . ' ~ Should be readable');
		}

		$this->assertEquals($expected, $actual, $label . ' ~ value');
	}
}
