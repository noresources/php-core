<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 *
 * @deprecated This is a transitional class for earlier framework version
 *
 */
class Reporter
{

	/**
	 * Set Reporter interface implementation
	 *
	 * @param ReporterInterface $impl
	 */
	public static function setImplementation(&$impl)
	{
		if ($impl instanceof LoggerInterface || $impl instanceof ReporterInterface)
			self::$loggers = [
				$impl
			];
		else
			throw new \InvalidArgumentException(
				LoggerInterface::class . ' or ' . ReporterInterface::class . ' expected. Got ' .
				TypeDescription::getName($impl));
	}

	/**
	 * Add debug message
	 *
	 * @param mixed $context
	 *        	Object that call the reporter
	 * @param string $message
	 *        	Message
	 * @param string $file
	 *        	File
	 * @param integer $line
	 *        	Line number in @param $file
	 */
	public static function debug($context, $message, $file = null, $line = null)
	{
		self::addMessage(LogLevel::DEBUG, $context, $message, $file, $line);
		return true;
	}

	/**
	 * Add notice
	 *
	 * @param mixed $context
	 *        	Object that call the reporter
	 * @param string $message
	 *        	Message
	 * @param string $file
	 *        	File
	 * @param integer $line
	 *        	Line number in @param $file
	 */
	public static function notice($context, $message, $file = null, $line = null)
	{
		self::addMessage(LogLevel::NOTICE, $context, $message, $file, $line);
		return true;
	}

	/**
	 * Add warning
	 *
	 * @param mixed $context
	 *        	Object that call the reporter
	 * @param string $message
	 *        	Message
	 * @param string $file
	 *        	File
	 * @param integer $line
	 *        	Line number in @param $file
	 */
	public static function warning($context, $message, $file = null, $line = null)
	{
		self::addMessage(LogLevel::WARNING, $context, $message, $file, $line);
		return true;
	}

	/**
	 * Add error
	 *
	 * @param mixed $context
	 *        	Object that call the reporter
	 * @param string $message
	 *        	Message
	 * @param string $file
	 *        	File
	 * @param integer $line
	 *        	Line number in @param $file
	 */
	public static function error($context, $message, $file = null, $line = null)
	{
		self::addMessage(LogLevel::ERROR, $context, $message, $file, $line);
		return false;
	}

	/**
	 * Raise a fatal error
	 *
	 * @param mixed $context
	 *        	Object that call the reporter
	 * @param string $message
	 *        	Message
	 * @param string $file
	 *        	File
	 * @param integer $line
	 *        	Line number in @param $file
	 */
	public static function fatalError($context, $message, $file = null, $line = null)
	{
		self::addMessage(LogLevel::EMERGENCY, $context, $message, $file, $line);
		foreach (self::$loggers as $ogger)
		{
			if ($logger instanceof ReporterInterface)
				$logger->handleFatalError();
		}
		die('');
	}

	const kMessageDebug = 0x1;

	const kMessageNotice = 0x2;

	const kMessageWarning = 0x4;

	const kMessageError = 0x8;

	const kMessageFatalError = 0x10;

	const kMessageAll = 0xFF;

	private static function addMessage($level, $object, $message, $file = null, $line = null)
	{
		foreach (self::$loggers as $ogger)
		{
			if ($logger instanceof LoggerInterface)
				$logger->log($level, $message);
			elseif ($logger instanceof ReporterInterface)
				$logger->addMessage(self::$log2ReporterLevel[$level], $object, $message, $file,
					$line);
		}
	}

	public static function initialize()
	{
		if (!\is_array(self::$loggers))
			self::$loggers = [];

		if (!\is_array(self::log2ReporterLevel))
			self::log2ReporterLevel
		[
				LogLevel::ALERT => self::kMessageError,
				LogLevel::CRITICAL => self::kMessageError,
				LogLevel::DEBUG => self::kMessageDebug,
				LogLevel::EMERGENCY => self::kMessageFatalError,
				LogLevel::ERROR => self::kMessageError,
				LogLevel::INFO => self::kMessageDebug,
				LogLevel::NOTICE => self::kMessageNotice,
				LogLevel::WARNING => self::kMessageWarning
			]
	}

	/**
	 *
	 * @var LoggerInterface[]
	 */
	private static $loggers;

	private static $log2ReporterLevel;
}

Reporter::initialize();