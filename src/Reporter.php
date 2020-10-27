<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

use Psr\Log\LoggerInterface;

/**
 * Multi-logger reporter
 */
class Reporter
{

	use SingletonTrait;

	/**
	 * Add a replace a logger.
	 *
	 * This method is also callable statically. In this case, the class singleton will be used.
	 *
	 * @param string $key
	 *        	Logger identifier
	 * @param LoggerInterface $logger
	 *
	 */
	public function registerLogger($key, LoggerInterface $logger)
	{
		$this->loggers[$key] = $logger;
	}

	/**
	 * Remove a logger by its key
	 *
	 * This method is also callable statically. In this case, the class singleton will be used.
	 *
	 * @param string $key
	 *        	Logger identifier
	 */
	public function unregisterLogger($key)
	{
		Container::removeKey($this->loggers, $key);
	}

	/**
	 * Invoke registered loggers corresponding method
	 *
	 * @method void emergency ($message, $context)
	 * @method void alert ($message, $context)
	 * @method void critical ($message, $context)
	 * @method void error ($message, $context)
	 * @method void warning ($message, $context)
	 * @method void notice ($message, $context)
	 * @method void info ($message, $context)
	 * @method void debug ($message, $context)
	 *
	 * @param string $method
	 *        	Loggers method ton invoke
	 * @param array $args
	 *        	Loggers method arguments
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $args)
	{
		if (!\in_array($method,
			[
				'emergency',
				'alert',
				'critical',
				'error',
				'warning',
				'notice',
				'info',
				'debug'
			]))
			throw new \BadMethodCallException(
				$method . ' is not a valid method of ' . static::class);

		foreach ($this->loggers as $logger)
		{
			\call_user_func_array([
				$logger,
				$method
			], $args);
		}
	}

	public static function __callStatic($method, $args)
	{
		return \call_user_func_array([
			self::getInstance(),
			$method
		], $args);
	}

	public function __construct()
	{
		if (!\is_array($this->loggers))
			$this->loggers = [];
	}

	/**
	 *
	 * @var LoggerInterface[]
	 */
	private $loggers;
}

