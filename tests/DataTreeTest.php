<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

use NoreSources\Test\DerivedFileManager;

final class DataTreeTest extends \PHPUnit\Framework\TestCase
{

	public function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->derived = new DerivedFileManager(__DIR__);
	}

	public function testFromArray()
	{
		$tree = new DataTree([
			'key' => 'value',
			'subTree' => [
				'subKey' => 'subValue'
			]
		]);

		$this->assertEquals('value', $tree->key, 'Top level existing key');
		$this->assertEquals('subValue', $tree->subTree->subKey, 'Second level existing key');
		$this->assertEquals('undefined', $tree->getElement('non-existing-key', 'undefined'),
			'Non-existing key');
	}

	public function testClone()
	{
		$a = new DataTree([
			'key' => 'value',
			'tree' => [
				'one' => 1,
				'two' => 2
			]
		]);

		$reference = $a;
		$cloned = clone $a;

		$this->assertEquals($a->getArrayCopy(), $reference->getArrayCopy(),
			'Reference has identical data');
		$this->assertEquals($a->getArrayCopy(), $cloned->getArrayCopy(), 'Clone has identical data');

		$a->key = 'new value';

		$this->assertEquals('new value', $reference->key, 'Reference has changed');
		$this->assertEquals('value', $cloned->key, 'Cloned tree did not change');

		$a->tree->three = 3;

		$this->assertEquals(3, $reference->tree->three, 'Reference has changed (subtree)');
		$this->assertEquals(null, $cloned->tree->three, 'Cloned tree did not change (subtree)');
	}

	public function testLoadQueryString()
	{
		$expected = [
			'key' => 'value'
		];

		$input = \http_build_query($expected);
		$tree = new DataTree();
		$tree->loadData($input, StructuredText::FORMAT_URL_ENCODED);

		$this->assertEquals($expected, $tree->getArrayCopy(), '');
	}

	public function testLoadJson()
	{
		$tree = new DataTree();
		$tree->loadFile(__DIR__ . '/data/a.json');
		$data = json_encode($tree, JSON_PRETTY_PRINT);
		$this->derived->assertDerivedFile($data, __METHOD__, 'file', 'json', 'json from file');

		$tree = new DataTree();
		$input = \file_get_contents(__DIR__ . '/data/a.json');
		$tree->loadData($input, StructuredText::FORMAT_JSON);
		$data = json_encode($tree, JSON_PRETTY_PRINT);
		$this->derived->assertDerivedFile($data, __METHOD__, 'string', 'json', 'json from string');
	}

	public function testLoadPhp()
	{
		$tree = new DataTree();
		$tree->loadFile(__DIR__ . '/data/a.php');
		$data = json_encode($tree, JSON_PRETTY_PRINT);
		$this->derived->assertDerivedFile($data, __METHOD__, null, 'json');
	}

	public function testLoadYaml()
	{
		if (!\extension_loaded('yaml'))
		{
			$this->assertTrue(true, 'YAML extension not available');
			return;
		}

		$tree = new DataTree();
		$tree->loadFile(__DIR__ . '/data/a.yaml');
		$data = json_encode($tree, JSON_PRETTY_PRINT);
		$this->derived->assertDerivedFile($data, __METHOD__, null, 'json');
	}

	public function testLoadInvalidJson()
	{
		$tree = new DataTree();
		$exceptionInstance = null;
		try
		{
			$tree->loadFile(__DIR__ . '/data/syntax-error.json');
		}
		catch (\Exception $e)
		{
			$exceptionInstance = $e;
		}

		$this->assertInstanceOf(\ErrorException::class, $exceptionInstance);
	}

	public function testFusion()
	{
		foreach ([
			DataTree::REPLACE => 'replace',
			DataTree::MERGE => 'merge',
			DataTree::MERGE_OVERWRITE => 'mergeoverwrite'
		] as $mode => $modeName)
		{

			foreach ([
				'json',
				'php'
			] as $extension)
			{
				$tree = new DataTree();
				$tree->loadFile(__DIR__ . '/data/a.' . $extension);
				$tree->loadFile(__DIR__ . '/data/b.' . $extension, $mode);
				$data = json_encode($tree, JSON_PRETTY_PRINT);
				$this->derived->assertDerivedFile($data, __METHOD__, $modeName, 'json',
					'Fusion (' . $modeName . ') ' . $extension);
			}
		}
	}

	private $derived;
}