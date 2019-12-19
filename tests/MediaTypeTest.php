<?php
namespace NoreSources;

use PHPUnit\Framework\TestCase;

final class MediaTypeTest extends TestCase
{

	public function testParse()
	{
		$tests = [
			'text/html' => [
				'strict' => true,
				'type' => 'text',
				'subtype' => [
					'text' => 'html',
					'facets' => [
						'html'
					],
					'syntax' => null
				],
				'syntax' => 'html'
			],
			'text/*' => [
				'strict' => false,
				'type' => 'text',
				'subtype' => null,
				'syntax' => null
			],
			'*/*' => [
				'strict' => false,
				'type' => '*',
				'subtype' => null,
				'syntax' => null
			],
			'text/vnd.abc' => [
				'strict' => true,
				'type' => 'text',
				'subtype' => [
					'text' => 'vnd.abc',
					'facets' => [
						'vnd',
						'abc'
					],
					'syntax' => null
				],
				'syntax' => null
			],
			'application/alto-costmap+json' => [
				'strict' => true,
				'type' => 'application',
				'subtype' => [
					'text' => 'alto-costmap+json',
					'facets' => [
						'alto-costmap'
					],
					'syntax' => 'json'
				],
				'syntax' => 'json'
			]
		];

		foreach ($tests as $text => $parsed)
		{
			$mediaType = MediaType::fromString($text, !$parsed['strict']);
			$this->assertInstanceOf(MediaType::class, $mediaType, $text);
			$this->assertEquals($parsed['type'], $mediaType->name, $text . ' name');

			if ($parsed['subtype'])
			{
			$this->assertInstanceOf(MediaSubType::class, $mediaType->subType, $text . ' subtype');
				/**
				 *
				 * @var MediaSubType $subType
				 */
				$subType = $mediaType->subType;

				$this->assertCount(count($parsed['subtype']['facets']), $subType->getFacets(),
					$text . ' subtype facets');

				$this->assertEquals($parsed['subtype']['syntax'], $subType->getStructuredSyntax(),
					$text . ' subtype syntax');

				foreach ($parsed['subtype']['facets'] as $index => $facet)
				{
					$this->assertEquals($facet, $subType->getFacet($index),
						$text . ' subtype facet ' . $index);
				}
			}

			$this->assertEquals($parsed['syntax'], $mediaType->getStructuredSyntax(),
				$text . ' syntax');

			$this->assertEquals($text, strval($mediaType), $text . ' to string');
		}
	}

	public function testFromMedia()
	{
		$this->assertEquals('application/json',
			strval(MediaType::fromMedia(__DIR__ . '/data/a.json')));
	}
}
