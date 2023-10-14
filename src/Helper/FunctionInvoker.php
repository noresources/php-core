<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Helper;

/**
 * Call a function silently and throws an exception if the function result corresponds to the error
 * result.
 */
class FunctionInvoker
{

	const DEFAULT_ERROR_RESULT = false;

	const DEFAULT_EXCEPTION_CLASS = '\RuntimeException';

	public $errorResult = false;

	public $exceptionClass = '\RuntimeException';

	/**
	 *
	 * @param string $functionName
	 *        	Function to call
	 * @param array $arguments
	 *        	Function arguments
	 * @return mixed
	 * @throws \Exception if invoked function returns the error result.
	 */
	public static function __callstatic($functionName, $arguments)
	{
		try
		{
			$result = @\call_user_func_array($functionName, $arguments);
		}
		catch (\Throwable $e)
		{
			$exceptionClass = self::DEFAULT_EXCEPTION_CLASS;
			throw new $exceptionClass(
				$functionName . ': ' . $e->getMessage());
		}
		catch (\Exception $e) // Pre-PHP 7
		{
			$exceptionClass = self::DEFAULT_EXCEPTION_CLASS;
			throw new $exceptionClass(
				$functionName . ': ' . $e->getMessage());
		}

		if ($result !== self::DEFAULT_ERROR_RESULT)
			return $result;
		$error = \error_get_last();
		$exceptionClass = self::DEFAULT_EXCEPTION_CLASS;
		throw new $exceptionClass(
			$functionName . ': ' . $error['message']);
	}

	/**
	 *
	 * @param string $functionName
	 *        	Function to call
	 * @param array $arguments
	 *        	Function arguments
	 * @return mixed Function invocation result
	 * @throws \Exception if invoked function returns the error result.
	 */
	public function __call($functionName, $arguments)
	{
		try
		{
			$result = @\call_user_func_array($functionName, $arguments);
		}
		catch (\Throwable $e)
		{
			throw new $this->exceptionClass(
				$functionName . ': ' . $e->getMessage());
		}
		catch (\Exception $e) // Pre-PHP 7
		{
			throw new $this->exceptionClass(
				$functionName . ': ' . $e->getMessage());
		}

		if ($result !== $this->errorResult)
			return $result;
		$error = \error_get_last();
		throw new $this->exceptionClass(
			$functionName . ': ' . $error['message']);
	}
}
