<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Container;

/**
 * A class implementing the ChainElementInterface can be attached
 * to a single chain of ChainElementInterface
 */
interface ChainElementInterface
{

	/**
	 * Insert the instance before the given chain link
	 *
	 * @param ChainElementInterface $after
	 */
	function insertBefor(ChainElementInterface $after);

	/**
	 * Insert the instance after the given chain link.
	 *
	 * @param ChainElementInterface $before
	 */
	function insertAfter(ChainElementInterface $before);

	/**
	 * Unlink the instance from its currently attached chain.
	 *
	 * Elements before and after the instance are linked together.
	 */
	function detachElement();

	/**
	 * Change the link to the element before the instance.
	 *
	 * The previously attached link "next" element is set to NULL.
	 *
	 * @param ChainElementInterface $previousElement
	 *        	The new chain link which will be before the instance.
	 */
	function setPreviousElement(
		ChainElementInterface $previousElement = null);

	/**
	 * Change the link to the element after the instance.
	 *
	 * The previously attached link "previous" element is set to NULL.
	 *
	 * @param ChainElementInterface $nextElement
	 *        	The new chain link which will be after the instance.
	 */
	function setNextElement(ChainElementInterface $nextElement = null);

	/**
	 *
	 * @return ChainElementInterface|NULL
	 */
	function getPreviousElement();

	/**
	 *
	 * @return ChainElementInterface|NULL
	 */
	function getNextElement();
}