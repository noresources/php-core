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

final class SourceFileTest extends \PHPUnit\Framework\TestCase
{

	public function testNamespace()
	{
		$source = new SourceFile(__DIR__ . '/../src/SourceFile.php');

		$namespaces = $source->getNamespaces();
		$this->assertCount(1, $namespaces);
		$this->assertArrayHasKey('name', $namespaces[0]);
		$this->assertEquals(__NAMESPACE__, $namespaces[0]['name']);
	}
}
