<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Container;

/**
 * Reference implementation of the ChainElementInterface
 */
trait ChainElementTrait
{

	/**
	 * Insert the chain element just before another one
	 *
	 * @param ChainElementInterface $after
	 *        	Element to insert before it
	 */
	public function insertBefor(ChainElementInterface $after)
	{
		$before = $after->getPreviousElement();
		$this->attachBetween($before, $after);
	}

	/**
	 * Insert the instance element just after a given one
	 *
	 * @param ChainElementInterface $before
	 *        	Element to insert after it
	 */
	public function insertAfter(ChainElementInterface $before)
	{
		$after = $before->getNextElement();
		$this->attachBetween($before, $after);
	}

	/**
	 * Set the previous element in chain
	 *
	 * This method shoud be considered as non-public
	 *
	 * @param ChainElementInterface $previousElement
	 */
	public function setPreviousElement(
		?ChainElementInterface $previousElement)
	{
		if ($this->previousChainElement == $previousElement)
			return;

		$p = $this->previousChainElement;
		$this->previousChainElement = null;

		if ($p instanceof ChainElementInterface)
			$p->setNextElement(null);

		$this->previousChainElement = $previousElement;
	}

	/**
	 * Set next element in chain
	 *
	 * This method should be considered as non-public.
	 *
	 * @param ChainElementInterface $nextElement
	 */
	public function setNextElement(
		?ChainElementInterface $nextElement)
	{
		if ($this->nextChainElement == $nextElement)
			return;

		$n = $this->nextChainElement;
		$this->nextChainElement = null;

		if ($n instanceof ChainElementInterface)
			$n->setPreviousElement(null);

		$this->nextChainElement = $nextElement;
	}

	/**
	 * Detach element from owning list
	 */
	public function detachElement()
	{
		$p = $this->previousChainElement;
		$n = $this->nextChainElement;

		$this->previousChainElement = null;
		$this->nextChainElement = null;

		if ($n instanceof ChainElementInterface)
			$n->setPreviousElement($p);

		if ($p instanceof ChainElementInterface)
			$p->setNextElement($n);
	}

	/**
	 *
	 * @return ChainElementInterface|NULL
	 */
	public function getPreviousElement()
	{
		return $this->previousChainElement;
	}

	/**
	 *
	 * @return ChainElementInterface|NULL
	 */
	public function getNextElement()
	{
		return $this->nextChainElement;
	}

	private function attachBetween($before, $after)
	{
		$this->detachElement();

		if ($after instanceof ChainElementInterface)
			$after->setPreviousElement($this);

		if ($before instanceof ChainElementInterface)
			$before->setNextElement($this);

		$this->previousChainElement = $before;
		$this->nextChainElement = $after;
	}

	/**
	 *
	 * @var ChainElementInterface
	 */
	private $previousChainElement;

	/**
	 *
	 * @var ChainElementInterface
	 */
	private $nextChainElement;
}