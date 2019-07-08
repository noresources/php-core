<?php

namespace NoreSources;

use PHPUnit\Framework\TestCase;

final class PathUtilTest extends TestCase
{

	public function testIsAbsoluteUnixStyle()
	{
		$this->assertEquals(true, PathUtil::isAbsolute('/home/god/rules'));
	}

	public function testIsAbsoluteWindowsDriveSlash()
	{
		$this->assertEquals(true, PathUtil::isAbsolute('c:/mydrive'));
	}

	public function testIsAbsoluteWindowsDriveBackslash()
	{
		$this->assertEquals(true, PathUtil::isAbsolute('c:\mydrive'));
	}

	public function testIsAbsoluteWrapperFilePrefix()
	{
		$this->assertEquals(true, PathUtil::isAbsolute('file:///home/god/rules'));
	}

	public function testIsAbsoluteWrapperHttpPrefix()
	{
		$this->assertEquals(true, PathUtil::isAbsolute('http://server/resource'));
	}

	public function testIsAbsoluteWrapperRelativeDot()
	{
		$this->assertEquals(false, PathUtil::isAbsolute('./boom.exe'));
	}
}
