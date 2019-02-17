<?php

namespace NoreSources;

const kSemanticVersionMajor = 'major';
const kSemanticVersionMinor = 'minor';
const kSemanticVersionPatch = 'patch';
const kSemanticVersionPreRelease = 'prerelease';
const kSemanticVersionMetadata = 'metadata';

class SemanticPostfixedData extends \ArrayObject
{

	public function __construct($data)
	{
		parent::__construct(array ());
		$this->set($data);
	}

	public function __toString()
	{
		return \implode('.', $this->getArrayCopy());
	}

	public function __offsetSet($offset, $value)
	{
		if (!\is_numeric($offset))
		{
			throw new \InvalidArgumentException('Non-numeric key');
		}
		if (!self::validate($value))
		{
			throw new \InvalidArgumentException($value . ' is not a valid build metadata string');
		}
		
		parent::__offsetSet($offset, $value);
	}

	public function set($data)
	{
		$this->exchangeArray(array ());
		
		if (\is_string($data))
		{
			if (strlen($data) == 0)
				return;
			$data = explode('.', $data);
		}
		
		if (ArrayUtil::isArray($data))
		{
			foreach ($data as $value)
			{
				if (!self::validate($value))
				{
					throw new \InvalidArgumentException($value . ' is not a valid build metadata string');
				}
				
				$this->append($value);
			}
		}
	}

	public function append($value)
	{
		if (!self::validate($value))
		{
			throw new \InvalidArgumentException($value . ' is not a valid build metadata string');
		}
		
		parent::append($value);
	}

	/**
	 *
	 * @param SemanticPostfixedData $data
	 * @return number An integer value
	 *         <ul>
	 *         <li>&lt; 0 if Object have lower precedence than @c $data</li>
	 *         <li>0 if Object have the same precedence than @c $data</li>
	 *         <li>&gt;0 0 if Object have higher precedence than @c $data</li>
	 *         </ul>
	 */
	public function compare(SemanticPostfixedData $data)
	{
		$ia = $this->getIterator();
		$ib = $data->getIterator();
		$ca = $this->count();
		$cb = $data->count();
		
		if ($ca == 0)
			return ($cb == 0) ? 1 : -1;
		elseif ($cb == 0)
			return -1;
		
		$numericRegex = chr(1) . '^[0-9]+$' . chr(1);
		
		while ($ia->valid() && $ib->valid())
		{
			$va = $ia->current();
			$vb = $ib->current();
			
			if (preg_match($numericRegex, $va))
			{
				if (preg_match($numericRegex, $vb))
				{
					// identifiers consisting of only digits are compared numerically
					$va = intval($va);
					$vb = intval($vb);
					if ($va < $vb)
						return -1;
					elseif ($va > $vb)
						return 1;
				}
				
				// Numeric identifiers always have lower precedence than non-numeric identifiers
				return -1;
			}
			elseif (preg_match($numericRegex, $vb))
			{
				// Numeric identifiers always have lower precedence than non-numeric identifiers
				return 1;
			}
			else
			{
				// identifiers with letters or hyphens are compared lexically in ASCII sort order.
				$v = self::compareString($va, $vb);
				if ($v != 0)
					return $v;
			}
			
			$ia->next();
			$ib->next();
		}
		
		if ($ia->valid())
		{
			return 1;
		}
		
		if ($ib->valid())
		{
			return -1;
		}
		
		return 0;
	}

	private static function validate($value)
	{
		if (preg_match(chr(1) . '^0+[0-9]*$' . chr(1), $value))
			return false; // Numeric identifiers MUST NOT include leading zeroes
		
		return preg_match(chr(1) . '^[A-Za-z0-9-]+$' . chr(1), $value);
	}

	private static function compareString($a, $b)
	{
		$sa = strlen($a);
		$sb = strlen($b);
		$m = min($sa, $sb);
		for ($i = 0; $i < $m; $i++)
		{
			$va = ord(substr($a, $i));
			$vb = ord(substr($b, $i));
			
			if ($va != $vb)
			{
				return ($va < $vb) ? -1 : 1;
			}
		}
		
		return (($sa < $sb) ? -1 : (($sa > $sb) ? 1 : 0));
	}
}

/**
 * Semantic version
 * @see https://semver.org/
 */
class SemanticVersion
{

	/**
	 *
	 * @param array|string|integer $version
	 * @param number $numberFormDigitCount
	 */
	public function __construct($version, $numberFormDigitCount = 2)
	{
		$this->set($version);
	}

	/**
	 *
	 * @param array|string|integer $version
	 * @param number $numberFormDigitCount
	 */
	public function set($version, $numberFormDigitCount = 2)
	{
		$this->major = 0;
		$this->minor = 0;
		$this->patch = 0;
		$this->prerelease = new SemanticPostfixedData('');
		$this->metadata = new SemanticPostfixedData('');
		
		if (is_int($version))
		{
			$p = pow(10, $numberFormDigitCount);
			$this->patch = $version % $p;
			$this->minor = intval($version / $p) % $p;
			$this->major = intval($version / ($p * $p));
		}
		elseif (is_string($version))
		{
			$matches = array ();
			if (preg_match(chr(1) . '^([0-9.]+)(-(.+?))?(\+(.+?))?$' . chr(1), $version, $matches))
			{
				$v = explode('.', $matches[1]);
				$this->major = ArrayUtil::keyValue($v, 0, 0);
				$this->minor = ArrayUtil::keyValue($v, 1, 0);
				$this->patch = ArrayUtil::keyValue($v, 2, 0);
				$this->prerelease->set(ArrayUtil::keyValue($matches, 3, ''));
				$this->metadata->set(ArrayUtil::keyValue($matches, 5, ''));
			}
			else
			{
				throw new \InvalidArgumentException('Invalid version format "' . $version . '"');
			}
		}
		elseif ($version instanceof SemanticVersion)
		{
			$this->major = $version->major;
			$this->minor = $version->minor;
			$this->patch = $version->patch;
			$this->prerelease = clone $version->prerelease;
			$this->metadata = clone $version->metadata;
		}
		elseif (ArrayUtil::isArray($version))
		{
			$this->major = ArrayUtil::keyValue($version, kSemanticVersionMajor, 0);
			$this->minor = ArrayUtil::keyValue($version, kSemanticVersionMinor, 0);
			$this->patch = ArrayUtil::keyValue($version, kSemanticVersionPatch, 0);
			$this->prerelease->set(ArrayUtil::keyValue($version, kSemanticVersionPreRelease, ''));
			$this->metadata->set(ArrayUtil::keyValue($version, kSemanticVersionMetadata, ''));
		}
		else
		{
			$t = is_object($version) ? get_class($version) : gettype($version);
			throw new \InvalidArgumentException($t . ' is not a valid argument');
		}
	}

	public function __toString()
	{
		$s = $this->major . '.' . $this->minor . '.' . $this->patch;
		$p = strval($this->prerelease);
		$m = strval($this->metadata);
		if (strlen($p))
			$s .= '-' . $p;
		if (strlen($m))
			$s .= '+' . $m;
		return $s;
	}

	public function __get($member)
	{
		switch ($member){
			case kSemanticVersionMajor: return $this->major;
			case kSemanticVersionMetadata: return strval($this->metadata);
			case kSemanticVersionMinor: return $this->minor;
			case kSemanticVersionPatch: return $this->patch;
			case kSemanticVersionPreRelease: return strval($this->prerelease);
		}
		
		throw new \InvalidArgumentException($member);
	}
	
	public function __set($member, $value)
	{
		switch ($member){
			case kSemanticVersionMajor: {
				if (is_int($value) && $value >= 0) {
					$this->major = $value;
				} else throw new \InvalidArgumentException($value);
			} break;
			case kSemanticVersionMetadata: {
				$this->metadata->set($value);
			} break;
			case kSemanticVersionMinor: {
				if (is_int($value) && $value >= 0) {
					$this->minor = $value;
				} else throw new \InvalidArgumentException($value);
			} break;
			case kSemanticVersionPatch: {
				if (is_int($value) && $value >= 0) {
					$this->patch = $value;
				} else throw new \InvalidArgumentException($value);
			} break;
			case kSemanticVersionPreRelease: {
				$this->prerelease->set ($value);
			} break;
		}
		
		throw new \InvalidArgumentException($member);
	}
	
	/**
	 * 
	 * @param mixed $version Version to compare with instance
	 * @return number
	 *         <ul>
	 *         <li>&lt; 0 if Object have lower precedence than @c $version</li>
	 *         <li>0 if Object have the same precedence than @c $version</li>
	 *         <li>&gt;0 0 if Object have higher precedence than @c $version</li>
	 *         </ul>
	 */
	public function compare($version)
	{
		if (!($version instanceof SemanticVersion))
		{
			$version = new SemanticVersion($version);
		}
		
		if ($this->major != $version->major)
		{
			return ($this->major < $version->major) ? -1 : 1;
		}
		if ($this->minor != $version->minor)
		{
			return ($this->minor < $version->minor) ? -1 : 1;
		}
		if ($this->patch != $version->patch)
		{
			return ($this->patch < $version->patch) ? -1 : 1;
		}
		
		return $this->prerelease->compare($version->prerelease);
	}

	/**
	 * @var number
	 */
	private $major;

	/**
	 * @var number
	 */
	private $minor;

	/**
	 * @var number
	 */
	private $patch;

	/**
	 * @var SemanticPostfixedData
	 */
	private $prerelease;

	/**
	 * @var SemanticPostfixedData
	 */
	private $metadata;
}