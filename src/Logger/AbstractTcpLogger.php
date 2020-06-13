<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
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

	abstract public function formatMessage($level, $message, array $context = array());

	public function __construct($host, $port)
	{
		$this->socket = @fsockopen($host, $port);
	}

	public function __destruct()
	{
		if (\is_resource($this->socket))
			@fclose($this->socket);
	}

	public function isConnected()
	{
		return \is_resource($this->socket);
	}

	public function log($level, $message, array $context = array())
	{
		if (!\is_resource($this->socket))
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