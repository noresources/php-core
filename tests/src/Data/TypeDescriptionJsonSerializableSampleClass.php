<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test\Data;

class TypeDescriptionJsonSerializableSampleClass implements
	\JsonSerializable
{

	public function jsonSerialize()
	{
		return [
			'key' => 'value',
			'foo' => [
				'bar',
				'baz'
			]
		];
	}
}
