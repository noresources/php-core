<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Logger;

use NoreSources\SingletonTrait;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * A Logger that use the built-in PHP trigger_error function
 */
class ErrorReporterLogger implements LoggerInterface
{
	use LoggerTrait;
	use SingletonTrait;

	public function __construct()
	{}

	public function log($level, $message, array $context = array())
	{
		if (!isset($this))
			return self::getInstance()->log($level, $message, $context);

		$errorType = 0;
		switch ($level)
		{
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
			case LogLevel::CRITICAL:
			case LogLevel::ERROR:
				$errorType = E_USER_ERROR;
			break;
			case LogLevel::WARNING:
				$errorType = E_USER_WARNING;
			break;
			default:
				$errorType = E_USER_NOTICE;
			break;
		}

		\trigger_error($message, $errorType);
	}
}