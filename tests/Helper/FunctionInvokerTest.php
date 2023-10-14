<?php
use NoreSources\Helper\FunctionInvoker;

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class FunctionInvokerTest extends \PHPUnit\Framework\TestCase
{

	function testStatic()
	{
		$this->assertTrue(\function_exists('\mkdir'), 'strlen() exists');
		$this->expectException(FunctionInvoker::DEFAULT_EXCEPTION_CLASS,
			'mkdir without arguments');
		FunctionInvoker::mkdir();
	}

	function testNonStatic()
	{
		$className = \ErrorException::class;
		$invoker = new FunctionInvoker();
		$invoker->exceptionClass = $className;
		$this->assertTrue(\function_exists('\mkdir'), 'strlen() exists');
		$this->expectException($className, 'mkdir without arguments');
		$invoker->mkdir();
	}
}
