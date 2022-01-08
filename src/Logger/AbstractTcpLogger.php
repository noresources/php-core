<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package COre
 */
namespace NoreSources\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Send message to TCP server
 */
abstract class AbstractTcpLogger implements LoggerInterface
{
	use LoggerTrait;

	/**
	 *
	 * @param string $level
	 *        	Log level identifier
	 * @param unknown $message
	 *        	Log message
	 * @param array $context
	 *        	Context
	 *
	 * @return string Message to send through the TCP socket
	 */
	abstract public function formatMessage($level, $message,
		array $context = array());

	/**
	 *
	 * @param string $host
	 *        	Hostname or IP address
	 * @param integer $port
	 *        	Port number
	 */
	public function __construct($host, $port)
	{
		$this->socket = @fsockopen($host, $port);
	}

	public function __destruct()
	{
		if (\is_resource($this->socket))
			@fclose($this->socket);
	}

	/**
	 * Indicates if the TCP socket is valid
	 *
	 * @return boolean
	 */
	public function isConnected()
	{
		return \is_resource($this->socket);
	}

	public function log($level, $message, array $context = array())
	{
		if (!$this->isConnected())
			return;

		$data = $this->formatMessage($level, $message, $context);
		@fwrite($this->socket, $data);
	}

	/**
	 *
	 * @var resource
	 */
	private $socket;
}