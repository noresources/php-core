<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources;

/**
 * Stack container implementation
 */
class Stack implements \Countable, \IteratorAggregate, ArrayRepresentation
{

	public function __construct()
	{
		$this->stackElements = [];
	}

	/**
	 *
	 * @return integer Number of elements in the stack
	 */
	public function count()
	{
		return \count($this->stackElements);
	}

	/**
	 *
	 * @return array Stack elements (First in, last out)
	 */
	public function getArrayCopy()
	{
		return \array_reverse($this->stackElements);
	}

	/**
	 * Get an iterator on stack elements sorted first in last out.
	 *
	 * @return StackIterator
	 */
	public function getIterator()
	{
		return new StackIterator($this->stackElements);
	}

	/**
	 * Indicates if the stack is empty
	 *
	 * @return boolean true if the stack does not contain any element
	 */
	public function isEmpty()
	{
		return (\count($this->stackElements) == 0);
	}

	/**
	 * Get the top-most element of the stack
	 *
	 * @throws \UnderflowException
	 * @return mixed The top-most element of the stack
	 */
	public function top()
	{
		if ($this->count() == 0)
			throw new \UnderflowException('Stack is empty');

		$index = $this->count() - 1;
		return $this->stackElements[$index];
	}

	/**
	 * Push an element on top of the stack
	 *
	 * @param mixed $element
	 *        	Element to add
	 * @return Stack the stack object
	 */
	public function push($element)
	{
		array_push($this->stackElements, $element);
		return $this;
	}

	/**
	 * Remove the top-most element of the stack
	 *
	 * @throws \UnderflowException
	 * @return mixed the removed element
	 */
	public function pop()
	{
		if ($this->isEmpty())
			throw new \UnderflowException('Stack is empty');

		$index = $this->count() - 1;
		return array_pop($this->stackElements);
	}

	/**
	 * Invoke the top-most element of the stack
	 *
	 * @throws \UnexpectedValueException
	 * @return mixed
	 */
	public function __invoke()
	{
		$e = $this->top();

		if (!\is_callable($e))
			throw new \UnexpectedValueException(
				'Unable to invoke a non-callable type (' . TypeDescription::getName($e) . ')');

		return call_user_func_array($e, func_get_args());
	}

	/**
	 * Attempt to call the given method on the top-most object of the stack
	 *
	 * @param string $name
	 * @param array $arguments
	 * @throws \UnexpectedValueException
	 * @throws \BadMethodCallException
	 */
	public function __call($name, $arguments)
	{
		$e = $this->top();

		if (!\is_object($e))
			throw new \UnexpectedValueException(
				'Unable to call method ' . $name . '() on a non-object (' .
				TypeDescription::getName($e) . ')');

		if (!\method_exists($e, $name))
			throw new \BadMethodCallException(
				$name . ' is not a method of ' . TypeDescription::getName($e));
		;

		return \call_user_func_array([
			$e,
			$name
		], $arguments);
	}

	/**
	 * Attempt to get the given property of the top-most element of the stack
	 *
	 * @param string $member
	 *        	Array key or object property
	 * @throws \InvalidArgumentException
	 * @return mixed|array|\ArrayAccess|\Traversable
	 */
	public function __get($member)
	{
		$e = $this->top();

		if (Container::keyExists($e, $member))
			return Container::keyValue($e, $member);

		throw new \InvalidArgumentException(
			$member . ' is not a member of ' . TypeDescription::getName($e));
	}

	/**
	 * Attempt to set the value of the given property of hte top most element of the stack.
	 *
	 * @param string $member
	 *        	Array key or object property
	 * @param mixed $value
	 * @throws \InvalidArgumentException
	 * @return mixed
	 */
	public function __set($member, $value)
	{
		$e = $this->top();

		if (Container::keyExists($e, $member))
		{
			Container::setValue($e, $member, $value);
			return;
		}

		throw new \InvalidArgumentException(
			$member . ' is not a member of ' . TypeDescription::getName($e));
	}

	/**
	 *
	 * @var array
	 */
	private $stackElements;
}