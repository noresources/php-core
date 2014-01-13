<?php

/**
 * Copyright © 2012 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

interface ReporterInterface
{

	function addMessage($level, $object, $message, $file, $line);

	function handleFatalError();
}

class Reporter
{
	const DEBUGMSG = 0x1;
	const NOTICE = 0x2;
	const WARNING = 0x4;
	const ERROR = 0x8;
	const FATAL_ERROR = 0xA;
	const ALL_MESSAGES = 0xFF;

	public static function setImplementation(ReporterInterface &$impl)
	{
		self::$m_implementation = $impl;
	}

	public static function debug($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::DEBUGMSG, $object, $message);
	}

	public static function notice($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::NOTICE, $object, $message);
	}

	public static function warning($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::WARNING, $object, $message);
	}

	public static function error($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::ERROR, $object, $message);
	}

	public static function fatalError($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::FATAL_ERROR, $object, $message);
		self::$m_implementation->handleFatalError();
		die();
	}

	private static function addMessage($level, $object, $message, $file = null, $line = null)
	{
		if (self::$m_implementation)
		{
			self::$m_implementation->addMessage($level, $object, $message, $file, $line);
		}
	}

	/**
	 *
	 * @var ReporterInterface
	 */
	private static $m_implementation;
}

class DummyReporterInterface implements ReporterInterface
{

	function addMessage($level, $object, $message, $file, $line)
	{
	}

	function handleFatalError()
	{
	}
}

Reporter::setImplementation(new DummyReporterInterface());

?>