<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Logger;

use NoreSources\SingletonTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * A logger that does nothing
 */
class SilentLogger implements LoggerInterface
{
	use LoggerTrait;
	use SingletonTrait;

	public function __construct()
	{}

	public function log($level, $message, array $context = array())
	{}
}