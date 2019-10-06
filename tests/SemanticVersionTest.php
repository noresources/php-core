<?php
namespace NoreSources;

use PHPUnit\Framework\TestCase;

final class SemanticVersionTest extends TestCase
{

	public function testCompare()
	{
		$versions = array(
			'1.0.0-alpha',
			'1.0.0-alpha.1',
			'1.0.0-alpha.beta',
			'1.0.0-beta',
			'1.0.0-beta.2',
			array(
				SemanticVersion::MAJOR => 1,
				SemanticVersion::MINOR => 0,
				SemanticVersion::PATCH => 0,
				SemanticVersion::PRE_RELEASE => 'beta.10'
			),
			'1.0.0-beta.11',
			'1.0.0-rc.1',
			'1.0.0',
			'1.0.1',
			'1.1.0',
			10101,
			'2.0.0'
		);

		$previous = null;
		foreach ($versions as $version)
		{
			$v = new SemanticVersion($version);
			$this->assertTrue($v instanceof SemanticVersion);

			if (!($v instanceof SemanticVersion))
				continue;

			if (is_string($version))
			{
				$this->assertEquals($version, strval($v));
			}

			if ($previous instanceof SemanticVersion)
			{
				$this->assertLessThan(0, $previous->compare($v), 'Version comparison');
				$this->assertLessThan(0, SemanticVersion::compare($previous, $v),
					'Version comparison (static)');
			}

			$previous = $v;
		}
	}
}