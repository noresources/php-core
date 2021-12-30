<?php
namespace NoreSources\Test\Data;

class TypeDescriptionSerializableSampleClass implements \Serializable
{

	public function __construct()
	{
		$this->text = 'Some text';
	}

	public function unserialize($serialized)
	{
		$this->text = $serialized;
	}

	public function serialize()
	{
		return $this->text;
	}

	private $text;
}
