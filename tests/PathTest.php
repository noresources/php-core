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

final class PathTest extends \PHPUnit\Framework\TestCase
{

	public function testIsAbsolute()
	{
		$paths = array(
			'/home/god/rules.csv' => true,
			'/etc' => true,
			'hello' => false,
			'.' => false,
			'c:\windows' => true,
			'c:/windows-too' => true,
			'c:not-a-drive' => false,
			'\\\\samba-on-windows-not-supported\\file.txt' => false,
			'http://server/path' => true,
			'http://./invalid/url.html' => true,
			'file://../this-is-not-a-relative/path' => true,
			'file:///unix/path/using/file.wrapper' => true,
			'simple-file.txt' => false
		);

		foreach ($paths as $path => $expected)
		{
			$this->assertEquals($expected, Path::isAbsolute($path), 'Path: "' . $path . '"');
		}
	}
}
