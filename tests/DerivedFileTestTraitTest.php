<?php
use NoreSources\Test\DerivedFileTestTrait;

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
class DerivedFileTestTraitTest extends \PHPUnit\Framework\TestCase
{
	use DerivedFileTestTrait;

	public function __construct($name = null, array $data = [],
		$dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->initializeDerivedFileTest(__DIR__);
	}

	public function testAssertAnyEqualsReferenceFile()
	{
		$content = "Foo\nBar\nHello world!";
		$this->assertDataEqualsReferenceFile($content, __METHOD__, null,
			'txt', 'assertDataEqualsReferenceFile');

		$stream = \fopen('php://memory', "rw");
		\fwrite($stream, $content);
		$this->assertStreamEqualsReferenceFile($stream, __METHOD__, null,
			'txt');
	}

	public function __destruct()
	{
		$this->cleanupDerivedFileTest();
	}
}
