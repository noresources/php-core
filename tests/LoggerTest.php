<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\Container\Container;
use NoreSources\Logger\AbstractTcpLogger;
use Psr\Log\LoggerInterface;

class TcpTestLogger extends AbstractTcpLogger
{

	public function formatMessage($level, $message, array $context = [])
	{
		return $level . ':' . $message . PHP_EOL .
			Container::implodeValues($context, PHP_EOL);
	}
}

final class LoggerTest extends \PHPUnit\Framework\TestCase
{

	public function testTcpLogger()
	{
		$logger = new TcpTestLogger('localhost', 5555);

		$this->assertInstanceOf(LoggerInterface::class, $logger);

		if (!$logger->isConnected())
			return;

		$logger->warning('You shoud see this on your TCP server');
	}
}