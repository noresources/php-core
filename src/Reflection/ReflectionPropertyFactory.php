<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use NoreSources\Container\Container;

/**
 * Default ReflectionPropertyFactory implementation
 * taht use basic method name prefix strategy to find getters and setters
 * for a given property.
 */
class ReflectionPropertyFactory implements
	ReflectionPropertyFactoryInterface
{

	/**
	 * Set the method name prefixes recognized as getter methods.
	 *
	 * Common values are "get" and "is"
	 *
	 * @param string[] $prefixes
	 */
	public function setGetterMethodNamePrefixes($prefixes, $access = 0)
	{
		if (!isset($this->methodNamePrefixes))
			$this->methodNamePrefixes = [];
		$this->methodNamePrefixes['get'] = $prefixes;
	}

	/**
	 * Set the method name prefixes recognied as a setter method.
	 *
	 * Common values are "set" and "is".
	 *
	 * @param string $prefixes
	 *        	List of prefixes.
	 */
	public function setSetterMethodNamePrefixes($prefixes)
	{
		if (!isset($this->methodNamePrefixes))
			$this->methodNamePrefixes = [];
		$this->methodNamePrefixes['set'] = $prefixes;
	}

	public function createReflectionProperty($class, $name,
		$mode = self::MODE_AUTO)
	{
		$cls = null;
		if (\is_string($class))
			$cls = new \ReflectionClass($class);
		elseif ($class instanceof \ReflectionClass)
			$cls = $class;
		elseif (\is_object($class))
			$cls = new \ReflectionClass(\get_class($class));
		else
			throw new \InvalidArgumentException(
				'Class name, RflectionClass or object expected');

		$methods = [];
		$property = $cls->getProperty($name);
		if ($property->isPublic())
			return $property;

		if (isset($this->methodNamePrefixes))
		{
			foreach ([
				'get' => 0,
				'set' => 1
			] as $type => $count)
			{
				foreach (Container::keyValue($this->methodNamePrefixes,
					$type, []) as $prefix)
				{
					$n = $prefix . $name;

					if ($cls->hasMethod($n) && ($m = $cls->getMethod($n)) &&
						($property->isStatic() == $m->isStatic()) &&
						(\count($m->getParameters()) == $count))
					{
						$methods[$type] = $m;
						break;
					}
				}
			}
		}

		$get = Container::keyValue($methods, 'get', null);
		$set = Container::keyValue($methods, 'set', null);

		if (!($mode || $get || $set))
			return $property;

		$alwaysAccessible = (version_compare(PHP_VERSION, '8.1.0') >= 0);
		if ($alwaysAccessible)
			return new ReflectionPropertyMethod($class, $name, $get,
				$set);

		$mandatoryReadable = ($mode & self::MODE_READ) == self::MODE_READ;
		$mandatoryWritable = ($mode & self::MODE_WRITE) ==
			self::MODE_WRITE;
		$setter = $set;
		$getter = $get;
		$accessible = false;

		if ($mandatoryReadable)
		{
			if (!$get)
			{
				$accessible = true;
				if (!$mandatoryWritable && !$set)
					$setter = new ReflectionProtectedProperty($class,
						$name);
			}
		}

		if ($mandatoryWritable)
		{
			if (!$set)
			{
				$accessible = true;
				if (!$mandatoryReadable && !$get)
					$getter = new ReflectionProtectedProperty($class,
						$name);
			}
		}

		$property = new ReflectionPropertyMethod($class, $name, $getter,
			$setter);
		$property->setAccessible($accessible);
		return $property;
	}

	/**
	 *
	 * @var string[]
	 */
	private $methodNamePrefixes;
}

/**
 *
 * @internal
 *
 *
 */
class ReflectionProtectedProperty
{

	private $name;

	private $class;

	public function __construct($class, $name)
	{
		$this->class = $class;
		$this->name = $name;
	}

	public function invoke()
	{
		$member = $this->class . '::$' . $this->name;
		if (func_num_args() < 2)
			throw new \ReflectionException(
				'Cannot get value of non-public member ' . $member);
		throw new \ReflectionException(
			'Cannot set value of non-public member ' . $member);
	}
}
