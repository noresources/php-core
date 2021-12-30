<?php
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
