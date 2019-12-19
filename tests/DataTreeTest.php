<?php
namespace NoreSources;

use PHPUnit\Framework\TestCase;

final class DataTreeTest extends TestCase
{

	public function __construct()
	{
		$this->derived = new \DerivedFileManager();
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

	public function testLoadJson()
	{
		$tree = new DataTree();
		$tree->load(__DIR__ . '/data/a.json');
		$data = json_encode($tree, JSON_PRETTY_PRINT);
		$this->derived->assertDerivedFile($data, __METHOD__, null, 'json');
	}

	public function testMerge()
	{
		$tree = new DataTree();
		$tree->load(__DIR__ . '/data/a.json');
		$tree->load(__DIR__ . '/data/b.json', null, DataTree::MODE_MERGE_OVERWRITE);
		$data = json_encode($tree, JSON_PRETTY_PRINT);
		$this->derived->assertDerivedFile($data, __METHOD__, null, 'json');
	}

	private $derived;
}