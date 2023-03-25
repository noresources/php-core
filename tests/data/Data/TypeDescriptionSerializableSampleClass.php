<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
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
