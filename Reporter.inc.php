<?php

/**
 * Copyright © 2012-2018 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

interface ReporterInterface
{

	/**
	 * Add message to reporter
	 *
	 * @param unknown $level
	 *			Message level
	 * @param unknown $object
	 *			Object that call the reporter
	 * @param unknown $message
	 *			Message
	 * @param unknown $file
	 *			File
	 * @param unknown $line
	 *			Line number in @param $file
	 */
	function addMessage($level, $object, $message, $file, $line);

	/**
	 * Task to do after a fatal error message
	 */
	function handleFatalError();
}

class Reporter
{

	const kMessageDebug = 0x1;

	const kMessageNotice = 0x2;

	const kMessageWarning = 0x4;

	const kMessageError = 0x8;

	const kMessageFatalError = 0x10;

	const kMessageAll = 0xFF;

	/**
	 * Set Reporter interface implementation
	 *
	 * @param ReporterInterface $impl
	 */
	public static function setImplementation(ReporterInterface &$impl)
	{
		self::$m_implementation = $impl;
	}

	public static function getImplementation()
	{
		return self::$m_implementation;
	}

	/**
	 * Add debug message
	 *
	 * @param unknown $object
	 *			Object that call the reporter
	 * @param unknown $message
	 *			Message
	 * @param unknown $file
	 *			File
	 * @param unknown $line
	 *			Line number in @param $file
	 */
	public static function debug($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::kMessageDebug, $object, $message, $file, $line);
		return true;
	}

	/**
	 * Add notice
	 *
	 * @param unknown $object
	 *			Object that call the reporter
	 * @param unknown $message
	 *			Message
	 * @param unknown $file
	 *			File
	 * @param unknown $line
	 *			Line number in @param $file
	 */
	public static function notice($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::kMessageNotice, $object, $message, $file, $line);
		return true;
	}

	/**
	 * Add warning
	 *
	 * @param unknown $object
	 *			Object that call the reporter
	 * @param unknown $message
	 *			Message
	 * @param unknown $file
	 *			File
	 * @param unknown $line
	 *			Line number in @param $file
	 */
	public static function warning($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::kMessageWarning, $object, $message, $file, $line);
		return true;
	}

	/**
	 * Add error
	 *
	 * @param unknown $object
	 *			Object that call the reporter
	 * @param unknown $message
	 *			Message
	 * @param unknown $file
	 *			File
	 * @param unknown $line
	 *			Line number in @param $file
	 */
	public static function error($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::kMessageError, $object, $message, $file, $line);
		return false;
	}

	/**
	 * Raise a fatal error
	 *
	 * @param unknown $object
	 *			Object that call the reporter
	 * @param unknown $message
	 *			Message
	 * @param unknown $file
	 *			File
	 * @param unknown $line
	 *			Line number in @param $file
	 */
	public static function fatalError($object, $message, $file = null, $line = null)
	{
		self::addMessage(self::kMessageFatalError, $object, $message, $file, $line);
		self::$m_implementation->handleFatalError();
		die('');
	}

	private static function addMessage($level, $object, $message, $file = null, $line = null)
	{
		if (self::$m_implementation) {
			self::$m_implementation->addMessage($level, $object, $message, $file, $line);
		}
	}

	/**
	 *
	 * @var ReporterInterface
	 */
	private static $m_implementation;
}

/**
 * A default implementation of ReporterInterface which does nothing
 */
class DummyReporterInterface implements ReporterInterface
{

	function addMessage($level, $object, $message, $file, $line)
	{}

	function handleFatalError()
	{}
}

// Set a default reporter
if (! Reporter::getImplementation()) {
	$d = new DummyReporterInterface();
	Reporter::setImplementation($d);
}
