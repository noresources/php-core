<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

use NoreSources\Container\CascadedValueTree;

final class CascadedValueTreeTest extends \PHPUnit\Framework\TestCase
{

	public function testCVT()
	{
		$c = new CascadedValueTree();
		$this->assertInstanceOf(CascadedValueTree::class, $c);

		$c->offsetSet('foo', 'foo value');
		$this->assertEquals('foo value', $c['foo'], 'Value of "foo"');

		$c->offsetSet('foo.bar', 'bar value in foo');
		$c->offsetSet('foo.baz', 'baz value in foo');
		$c['foo.bar.baz'] = 'baz';

		$this->assertArrayHasKey('foo.bar.baz', $c);
		$this->assertArrayHasKey('foo.baz', $c);
		$this->assertArrayNotHasKey('baz', $c);

		$this->assertEquals('baz', $c['foo.bar.baz'],
			'Value of "foo.bar.baz"');
		$this->assertEquals(null, $c['foo.undef.baz'],
			'Value of "foo.undef.baz"' . PHP_EOL . $this->toJson($c));

		$c->offsetUnset('foo.bar.baz');

		$this->assertFalse($c->offsetExists('foo.bar.baz'),
			'offset not exists after remove');
	}

	private function toJson(CascadedValueTree $c)
	{
		return \json_encode($c->getArrayCopy(), JSON_PRETTY_PRINT);
	}
}