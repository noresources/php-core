<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace Space;

const HELLO = 'hello world';

function freeHello()
{
	return HELLO;
}

class PSR4Class implements PSR4Interface
{
	use PSR4Trait;

	const HELLO = 'hello class';

	public function hello()
	{
		return self::HELLO;
	}
}
