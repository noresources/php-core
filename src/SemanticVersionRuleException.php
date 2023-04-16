<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources;

/**
 * Sematic version rule violation exception
 */
class SemanticVersionRuleException extends \ErrorException
{

	/**
	 *
	 * @param integer $rulePoint
	 *        	Unsatisfied semantic versioning rule point.
	 * @param string $message
	 *        	Rule
	 * @param mixed $value
	 *        	Invalid value
	 */
	public function __construct($rulePoint, $message, $value)
	{
		parent::__construct(
			$value . ' does not respect Semantic Versioning rule #' .
			$rulePoint . ': ' . $message . '.' . PHP_EOL .
			'See https://semver.org', $rulePoint);
	}
}
