<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\MediaType;

use NoreSources\StringRepresentation;
use NoreSources\Container;

class MediaSubType implements StringRepresentation
{

	/**
	 *
	 * @param array|string $facets
	 * @param string|null $structuredSyntax
	 */
	public function __construct($facets, $structuredSyntax = null)
	{
		$this->facets = $facets;
		$this->structuredSyntax = $structuredSyntax;

		if (\is_string($facets))
			$this->facets = explode('.', $facets);
	}

	public function __toString()
	{
		$s = \implode('.', $this->facets);
		if (\is_string($this->structuredSyntax) && \strlen($this->structuredSyntax))
			$s .= '+' . $this->structuredSyntax;

		return $s;
	}

	/**
	 *
	 * @return array Subtype facets
	 */
	public function getFacets()
	{
		return $this->facets;
	}

	/**
	 *
	 * @param integer $index
	 * @return string|NULL Subtype facet at the given index or @c null if the index does not exists
	 */
	public function getFacet($index)
	{
		return Container::keyValue($this->facets, $index, null);
	}

	public function getFacetCount()
	{
		return count($this->facets);
	}

	/**
	 * Get the sub type structured syntax name
	 *
	 * @see https://tools.ietf.org/html/rfc6838#section-4.2.8
	 * @return string If any, the lower-case structured syntax name
	 */
	public function getStructuredSyntax()
	{
		if (\is_string($this->structuredSyntax) && \strlen($this->structuredSyntax))
			return strtolower($this->structuredSyntax);
		return null;
	}

	public function compare(MediaSubType $b)
	{
		$fca = $this->getFacetCount();
		$fcb = $b->getFacetCount();
		$fcm = min($fca, $fcb);
		$fcM = max($fca, $fcb);
		$i = 0;
		for (; $i < $fcm; $i++)
		{
			$fa = $this->getFacet($i);
			$fb = $b->getFacet($i);

			if ($fb != $fb)
			{
				if ($i == ($fcM - 1))
					return \strcmp($fa, $fb);

				return 0;
			}
		}

		return ($fca > $fcb) ? 1 : (($fca < $fcb) ? -1 : 0);
	}

	/**
	 *
	 * @var array
	 */
	private $facets;

	/**
	 *
	 * @var string
	 */
	private $structuredSyntax;
}

