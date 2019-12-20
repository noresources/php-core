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

/**
 *
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 *
 */
class MediaType implements StringRepresentation
{

	const ANY = '*';

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
		if (\is_string($this->name) && strlen($this->name) && ($this->name != self::ANY))
		{
			$s = $this->name . '/';
			if ($this->mediaSubType instanceof MediaSubType)
				$s .= strval($this->mediaSubType);
			else
				$s .= '*';
			return $s;
		}

		return '*/*';
	}

	/**
	 * Get media type of a file or stream
	 *
	 * @param string|resource $media
	 *        	File path or stream
	 * @return \NoreSources\MediaType
	 */
	public static function fromMedia($media)
	{
		$type = @mime_content_type($media);

		if ($type === false)
			throw \Exception('Unable to recognize media type');
		elseif ($type == 'text/plain')
		{
			if (is_file($media))
			{
				$x = self::fromFileExtension(pathinfo($media, PATHINFO_EXTENSION));
				if ($x !== false)
					$type = $x;
			}
		}

		return self::fromString($type);
	}

	/**
	 * Get Media type from file extension
	 *
	 * @param string $extension
	 *        	File extension
	 * @return \NoreSources\MediaType|false
	 */
	public static function fromFileExtension($extension)
	{
		if (!\is_array(self::$extensions))
			self::$extensions = [
				'css' => 'text/css',
				'html' => 'text/html',
				'htm' => 'text/html',
				'js' => 'text/javascript',
				'json' => 'application/json',
				'xml' => 'text/xml',
				'yaml' => 'text/yaml'
			];

		$mediaTypeString = Container::keyValue(self::$extensions, strtolower($extension), false);
		if (\is_string($mediaTypeString))
			return self::fromString($mediaTypeString);
		return $mediaTypeString;
	}

	/**
	 * Parse a media type string
	 *
	 * @param string $mediaTypeString
	 *        	Mediga type strin
	 * @param boolean $acceptStar
	 *        	Accept the character '*' indicating "any type or subtype" in HTTP Accept header
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType
	 */
	public static function fromString($mediaTypeString, $acceptStar = false)
	{
		$matches = [];
		$pattern = ($acceptStar ? self::PATTERN_OPTIONAL : self::PATTERN_STRICT);
		if (!\preg_match(chr(1) . $pattern . chr(1) . 'i', $mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString, 'Not a valid media type string');

		$subType = null;
		if (Container::keyExists($matches, 2))
		{
			$facets = explode('.', $matches[2]);
			$syntax = Container::keyValue($matches, 3, null);
			$subType = new MediaSubType($facets, $syntax);
		}

		return new MediaType(Container::keyValue($matches, 1, self::ANY), $subType);
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
		if (!($this->mediaSubType instanceof MediaSubType))
			return null;

		$s = $this->mediaSubType->getStructuredSyntax();
		if ($s)
			return $s;

		if ($this->mediaSubType->getFacetCount() == 1)
		{
			$facet = $this->mediaSubType->getFacet(0);
			if ((strtolower($this->name) == 'text') || \in_array($facet, [
				'json',
				'xml'
			]))
				return $facet;
		}

		return null;
	}

	const PATTERN_STRICT = '^([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))/((?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*$';

	const PATTERN_OPTIONAL = '^(?:\*/\*)|(?:([a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))/(?:(?:\*)|((?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^ -]{0,126})))*))$';

	/**
	 *
	 * @var MediaSubType
	 */
	private $mediaSubType;

	private static $extensions;
}