<?php
namespace NoreSources;

use PHPUnit\Framework\TestCase;

final class SourceFileTest extends TestCase
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
