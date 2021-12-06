<?php
namespace NoreSources\Test;

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
