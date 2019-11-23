<?php
namespace NoreSources;

class MediaTypeException extends \Exception
{

	/**
	 *
	 * @var MediaType|MediaSubType|string
	 */
	public $type;

	/**
	 *
	 * @param MediaType|MediaSubType|string $type
	 * @param string $message
	 */
	public function __construct($type, $message)
	{
		parent::__construct($message);
	}
}

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

class MediaType implements StringRepresentation
{

	/**
	 * Media main type
	 *
	 * @var string
	 */
	public $name;

	public function __construct($type, MediaSubType $subType = null)
	{
		$this->name = $type;
		$this->mediaSubType = $subType;
	}

	/**
	 *
	 * @property-read MediaSubType $subType
	 * @property-read MediaSubType $subtype
	 *
	 * @param string $member
	 * @return \NoreSources\MediaSubType
	 */
	public function __get($member)
	{
		if (strtolower($member) == 'subtype')
			return $this->mediaSubType;

		throw new \InvalidArgumentException(
			$member . ' is nat a member of ' . TypeDescription::getName($this));
	}

	public function __toString()
	{
		return $this->name . '/' . strval($this->mediaSubType);
	}

	public static function fromString($mediaTypeString)
	{
		$matches = [];
		if (!\preg_match(chr(1) . self::PATTERN . chr(1) . 'i', $mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString, 'Not a valid media type string');

		$subType = null;
		if (Container::keyExists($matches, 2))
		{
			$facets = explode('.', $matches[2]);
			$syntax = Container::keyValue($matches, 3, null);
			$subType = new MediaSubType($facets, $syntax);
		}

		return new MediaType($matches[1], $subType);
	}

	/**
	 * Get the subtype structured syntax name if any.
	 *
	 * If the subtype does not specify a structured syntax name and if the media main type is "test",
	 * the sybtype name is returned.
	 *
	 * @return string|array|string|NULL
	 */
	public function getStructuredSyntax()
	{
		$s = $this->mediaSubType->getStructuredSyntax();
		if ($s)
			return $s;

		if (strtolower($this->name) == 'text' && $this->mediaSubType->getFacetCount() == 1)
			return strtolower($this->mediaSubType->getFacet(0));

		return null;
	}

	const PATTERN = '^([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))/((?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*$';

	/**
	 *
	 * @var MediaSubType
	 */
	private $mediaSubType;
}