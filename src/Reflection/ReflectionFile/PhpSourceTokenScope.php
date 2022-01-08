<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection\ReflectionFile;

/**
 * Represents a range of PHP source tokens that compose an element scope or code block
 */
class PhpSourceTokenScope
{

	/**
	 * Scope / block depth
	 *
	 * @var integer
	 */
	public $level;

	/**
	 *
	 * @var PhpSourceToken
	 */
	public $parentEntityToken;

	/**
	 *
	 * @var PhpSourceToken
	 *
	 */
	public $entityToken;

	/**
	 * First token of the scope
	 *
	 * @var integer
	 */
	public $startTokenIndex;

	/**
	 * Last token of the scope
	 *
	 * @var integer
	 */
	public $endTokenIndex;

	public function __construct()
	{
		$this->level = 0;
		$this->entityToken = null;
		$this->startTokenIndex = -1;
		$this->endTokenIndex = -1;
	}
}
