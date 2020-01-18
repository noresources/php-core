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

use NoreSources\MediaType\MediaType;
use NoreSources\MediaType\MediaSubType;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaTypeFactory;

final class MediaTypeTest extends \PHPUnit\Framework\TestCase
{

	public function testParse()
	{
		$tests = [
			'text/html' => [
				'valid' => true,
				'class' => MediaType::class,
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
				'valid' => true,
				'class' => MediaRange::class,
				'type' => 'text',
				'subtype' => null,
				'syntax' => null
			],
			'*/*' => [
				'valid' => true,
				'class' => MediaRange::class,
				'type' => '*',
				'subtype' => null,
				'syntax' => null
			],
			'text/vnd.abc' => [
				'valid' => true,
				'class' => MediaType::class,
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
			'image/vnd.noresources.amazing.format' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'image',
				'subtype' => [
					'text' => 'vnd.noresources.amazing.format',
					'facets' => [
						'vnd',
						'noresources',
						"amazing",
						'format'
					],
					'syntax' => null
				],
				'syntax' => null
			],
			'text/vnd.noresources.incredibly.flexible+xml' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'text',
				'subtype' => [
					'text' => 'vnd.noresources.incredibly.flexible+xml',
					'facets' => [
						'vnd',
						'noresources',
						"incredibly",
						'flexible'
					],
					'syntax' => 'xml'
				],
				'syntax' => 'xml'
			],
			'application/alto-costmap+json' => [
				'valid' => true,
				'class' => MediaType::class,
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
			$mediaType = null;
			try
			{
				$mediaType = MediaTypeFactory::fromString($text,
					$parsed['class'] == MediaRange::class);
			}
			catch (MediaTypeException $e)
			{
				if ($parsed['valid'])
					throw $e;
				continue;
			}

			$this->assertInstanceOf($parsed['class'], $mediaType, $text);
			$this->assertEquals($parsed['type'], $mediaType->getMainType(), $text . ' name');

			if ($parsed['subtype'])
			{
				$this->assertInstanceOf(MediaSubType::class, $mediaType->getSubType(),
					$text . ' subtype');

				$subType = $mediaType->getSubType();

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
			else
				$this->assertEquals(MediaRange::ANY, $mediaType->getSubType(), 'Subtype is a range');

			$this->assertEquals($parsed['syntax'], $mediaType->getStructuredSyntax(),
				$text . ' syntax');

			$this->assertEquals($text, strval($mediaType), $text . ' to string');
		}
	}

	public function testFromMedia()
	{
		$this->assertEquals('application/json',
			strval(MediaTypeFactory::fromMedia(__DIR__ . '/data/a.json')));
	}
}
