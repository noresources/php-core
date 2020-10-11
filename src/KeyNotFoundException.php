<?php

/**
 * Copyright © 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

use Psr\Container\NotFoundExceptionInterface;

/**
 * An implementation of the PSR-11 NotFoundExceptionInterface raised by ContainerInterface::get()
 */
class KeyNotFoundException extends \InvalidArgumentException implements
	NotFoundExceptionInterface
{

	/**
	 *
	 * @var mixed Missing key
	 */
	public $key;

	/**
	 *
	 * @param mixed $key
	 *        	Missing key
	 * @param string|null $message
	 *        	Exception message
	 * @param integer|null $code
	 *        	Error code
	 */
	public function __construct($key, $message = null, $code = null)
	{
		parent::__construct(
			($message === null) ? (\strval($key) . ' not found') : $message,
			(($code === null) ? 404 : $code));
		$this->key = $key;
	}
}
