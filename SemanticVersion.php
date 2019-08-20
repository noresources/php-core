<?php

namespace NoreSources;

final class SemanticPostfixedData extends \ArrayObject
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

	public function offsetSet($offset, $value)
	{
		if (!(\is_numeric($offset) || is_null($offset)))
		{
			throw new \InvalidArgumentException('Non-numeric key "' . strval($offset) . '"');
		}
		if (!self::validate($value))
		{
			throw new \InvalidArgumentException($value . ' is not a valid build metadata string');
		}

		parent::offsetSet($offset, $value);
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

		if (ContainerUtil::isArray($data))
		{
			foreach ($data as $value)
			{
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

		return parent::append($value);
	}

	/**
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
	const MAJOR = 'major';
	const MINOR = 'minor';
	const PATCH = 'patch';
	const PRE_RELEASE = 'prerelease';
	const METADATA = 'metadata';

	/**
	 * @param array|string|integer $version
	 * @param number $numberFormDigitCount
	 */
	public function __construct($version, $numberFormDigitCount = 2)
	{
		$this->set($version, $numberFormDigitCount);
	}

	/**
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
				$this->major = ContainerUtil::keyValue($v, 0, 0);
				$this->minor = ContainerUtil::keyValue($v, 1, 0);
				$this->patch = ContainerUtil::keyValue($v, 2, 0);
				$this->prerelease->set(ContainerUtil::keyValue($matches, 3, ''));
				$this->metadata->set(ContainerUtil::keyValue($matches, 5, ''));
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
		elseif (ContainerUtil::isArray($version))
		{
			$this->major = ContainerUtil::keyValue($version, self::MAJOR, 0);
			$this->minor = ContainerUtil::keyValue($version, self::MINOR, 0);
			$this->patch = ContainerUtil::keyValue($version, self::PATCH, 0);
			$this->prerelease->set(ContainerUtil::keyValue($version, self::PRE_RELEASE, ''));
			$this->metadata->set(ContainerUtil::keyValue($version, self::METADATA, ''));
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

	/**
	 * @property-read integer $major Major
	 * @property-read integer $minor Minor
	 * @property-read integer $patch Patch level
	 * @property-read string $prerelease Pre-release data
	 * @property-read string $metadata Metadata
	 * 
	 * @param string $member
	 * @throws \InvalidArgumentException
	 * @return number|string
	 */
	public function __get($member)
	{
		switch ($member){
			case self::MAJOR: return $this->major;
			case self::METADATA: return strval($this->metadata);
			case self::MINOR: return $this->minor;
			case self::PATCH: return $this->patch;
			case self::PRE_RELEASE: return strval($this->prerelease);
		}
		
		throw new \InvalidArgumentException($member);
	}
	
	/**
	 * @property-write integer $major Major
	 * @property-write integer $minor Minor
	 * @property-write integer $patch Patch level
	 * @property-write string $prerelease Pre-release data
	 * @property-write string $metadata Metadata
	 *  
	 * @param string $member
	 * @param mixed $value
	 * @throws \InvalidArgumentException
	 */
	public function __set($member, $value)
	{
		switch ($member){
			case self::MAJOR: {
				if (is_int($value) && $value >= 0) {
					$this->major = $value;
				} else throw new \InvalidArgumentException($value);
			} break;
			case self::METADATA: {
				$this->metadata->set($value);
			} break;
			case self::MINOR: {
				if (is_int($value) && $value >= 0) {
					$this->minor = $value;
				} else throw new \InvalidArgumentException($value);
			} break;
			case self::PATCH: {
				if (is_int($value) && $value >= 0) {
					$this->patch = $value;
				} else throw new \InvalidArgumentException($value);
			} break;
			case self::PRE_RELEASE: {
				$this->prerelease->set ($value);
			} break;
		}
		
		throw new \InvalidArgumentException($member);
	}
	
	public function __call($name, $arguments)
	{
		if ($name == 'compare') {
			array_unshift($arguments, $this);
			return call_user_func_array(array (get_called_class(), 'compareVersions'), $arguments);
		}
		
		throw new \InvalidArgumentException($name . ' is not callable');
	}
	
	public static function __callstatic($name, $arguments)
	{
		if ($name == 'compare') {
			return call_user_func_array(array (get_called_class(), 'compareVersions'), $arguments);
		}
		
		throw new \InvalidArgumentException($name . ' is not callable statically');
	}
	
	/**
	 * 
	 * @param unknown $a
	 * @param unknown $b
	 * @return number
	 *         <ul>
	 *         <li>&lt; 0 if @c $a have lower precedence than @c $b/li>
	 *         <li>0 if @c $a have the same precedence than @c $b/li>
	 *         <li>&gt;0 0 if @c $a have higher precedence than @c $b/li>
	 *         </ul>
	 */
	public static function compareVersions($a, $b)
	{
		if (!($a instanceof SemanticVersion))
		{
			$a = new SemanticVersion($a);
		}
		
		if (!($b instanceof SemanticVersion))
		{
			$b = new SemanticVersion($b);
		}
		
		if ($a->major != $b->major)
		{
			return ($a->major < $b->major) ? -1 : 1;
		}
		if ($a->minor != $b->minor)
		{
			return ($a->minor < $b->minor) ? -1 : 1;
		}
		if ($a->patch != $b->patch)
		{
			return ($a->patch < $b->patch) ? -1 : 1;
		}
		
		return $a->prerelease->compare($b->prerelease);
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