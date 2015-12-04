<?php

/**
 * Copyright © 2012-2015 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

/**
 * Move to the next token kind
 * @param array $tokenArray A token arary given by token_get_all()
 * @param int $tokenIndex Index of the current token
 * @param mixed $nextElementType Token to search
 * 
 * @return The 
 */
function token_move_next(&$tokenArray, &$tokenIndex, $nextElementType)
{
	$c = count($tokenArray);
	$tokenIndex++;
	while ($tokenIndex < $c)
	{
		$token = $tokenArray [$tokenIndex];
		if (\is_array($token) && \is_int($nextElementType) && ($token [0] == $nextElementType))
		{
			return $token;
		}
		elseif (is_string($token) && is_string($nextElementType) && ($token == $nextElementType))
		{
			return $token;
		}
		
		$tokenIndex++;
	}
	
	return null;
}