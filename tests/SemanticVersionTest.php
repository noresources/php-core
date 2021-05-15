<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

use NoreSources\SemanticVersion;

final class SemanticVersionTest extends \PHPUnit\Framework\TestCase
{

	public function testCompare()
	{
		$versions = array(
			'1.0.0-alpha',
			'1.0.0-alpha.1',
			'1.0.0-alpha.1.1',
			'1.0.0-alpha.2',
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
			$this->assertInstanceOf(SemanticVersion::class, $v);

			if (!($v instanceof SemanticVersion))
				continue;

			if (is_string($version))
			{
				$this->assertEquals($version, strval($v),
					'String representation of Semantic version');
			}

			if ($previous instanceof SemanticVersion)
			{
				$this->assertLessThan(0, $previous->compare($v),
					'Version comparison');
				$this->assertLessThan(0,
					SemanticVersion::compare($previous, $v,
						'Version comparison (static method)'),
					'Version comparison (static)');
			}

			$previous = $v;
		}
	}

	public function testToNumber()
	{
		$tests = [
			[
				'version' => '1.0.0',
				'value' => 10000
			],
			[
				'version' => '1.0.0-alpha2',
				'value' => 10000
			],
			[
				'version' => '1.0',
				'value' => 10000
			],
			[
				'version' => '2.3.4',
				'value' => 20304
			],
			[
				'version' => 123,
				'value' => 123
			],
			[
				'version' => '0.1.23',
				'value' => 123
			]
		];

		foreach ($tests as $test)
		{

			$version = null;
			$version = new SemanticVersion($test['version']);

			$this->assertEquals($test['value'],
				$version->getIntegerValue(),
				'Integer conversion of ' . $version);
		}
	}

	public function testSlice()
	{
		$tests = [
			'Major and minor' => [
				'version' => '1.2.3',
				'args' => [
					0,
					1
				],
				'slice' => '1.2'
			],
			'Major and minor (names)' => [
				'version' => '1.2.3',
				'args' => [
					SemanticVersion::MAJOR,
					SemanticVersion::MINOR
				],
				'slice' => '1.2'
			],
			'Full' => [
				'version' => '1.2.3+meta',
				'args' => [
					SemanticVersion::MAJOR,
					SemanticVersion::METADATA
				],
				'slice' => '1.2.3+meta'
			],
			'Pre-release and meta' => [
				'version' => '7.2.12+meta',
				'args' => [
					SemanticVersion::PRE_RELEASE,
					SemanticVersion::METADATA
				],
				'slice' => 'meta'
			],
			'Pre-release and meta' => [
				'version' => '7.2.12-alpha+meta',
				'args' => [
					SemanticVersion::PRE_RELEASE,
					SemanticVersion::METADATA
				],
				'slice' => 'alpha+meta'
			]
		];

		foreach ($tests as $label => $test)
		{
			$test = (object) $test;
			$sv = new SemanticVersion($test->version);
			$slice = \call_user_func_array([
				$sv,
				'slice'
			], $test->args);

			$this->assertEquals($test->slice, $slice, $label);
		}
	}
}
