<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use PHPUnit\Framework\TestCase;

/**
 *
 * @deprecated Backward compatibility. Use DerivedFileTestTrait directly
 *
 */
class DerivedFileManager extends TestCase
{
	use DerivedFileTestTrait;

	const DIRECTORY_REFERENCE = 'reference';

	const DIRECTORY_DERIVED = 'derived';

	public function __construct($basePath = null)
	{
		$this->initializeDerivedFileTest($basePath,
			self::DIRECTORY_REFERENCE, self::DIRECTORY_DERIVED);
	}

	public function __destruct()
	{
		$this->cleanupDerivedFileTest();
	}
}