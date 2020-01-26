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

trait MediaTypeStructuredTextTrait
{

	public function getStructuredSyntax()
	{
		if (!($this->getSubType() instanceof MediaSubType))
			return null;

		$s = $this->getSubType()->getStructuredSyntax();
		if ($s)
			return $s;

		if ($this->getSubType()->getFacetCount() == 1)
		{
			$facet = $this->getSubType()->getFacet(0);
			if ((strtolower($this->getMainType()) == 'text') ||
				StructuredSyntaxSuffixRegistry::isRegistered($facet))
				return $facet;
		}

		return null;
	}
}