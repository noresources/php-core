<?php
namespace NoreSources;

use PHPUnit\Framework\TestCase;

final class DataTreeTest extends TestCase
{

	public function testFromArray()
	{
		$tree = new DataTree ([
			'key' => 'value',
			'subTree' => ['subKey' => 'subValue']
		]);

		$this->assertEquals('value', $tree->key, 'Top level existing key');
		$this->assertEquals('subValue', $tree->subTree->subKey, 'Second level existing key');
		$this->assertEquals('undefined', $tree->getElement('non-existing-key', 'undefined'),
			'Non-existing key');
	}
}