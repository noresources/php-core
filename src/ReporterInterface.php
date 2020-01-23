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

/**
 *
 * @deprecated This is a transitional interface for earlier framework version
 *
 */
interface ReporterInterface
{

	/**
	 * Add message to reporter
	 *
	 * @param unknown $level
	 *        	Message level
	 * @param unknown $object
	 *        	Object that call the reporter
	 * @param unknown $message
	 *        	Message
	 * @param unknown $file
	 *        	File
	 * @param unknown $line
	 *        	Line number in @param $file
	 */
	function addMessage($level, $object, $message, $file, $line);

	/**
	 * Task to do after a fatal error message
	 */
	function handleFatalError();
}