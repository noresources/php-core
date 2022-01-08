<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Reflection;

use NoreSources\Bitset;
use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionFile\PhpSourceToken;
use NoreSources\Reflection\ReflectionFile\PhpSourceTokenScope;
use NoreSources\Reflection\ReflectionFile\PhpSourceTokenVisitor;
use NoreSources\Type\TypeDescription;
use ReflectionClass;

/**
 * PHP source file informations
 *
 * Provide informations about global constants, free functions, interfaces, traits and classes
 * defined in the file.
 */
class ReflectionFile
{

	/**
	 * ReflectionFile inspection flag
	 *
	 * The file content can be safely evaluated.
	 *
	 * @var integer
	 */
	const SAFE = Bitset::BIT_01;

	/**
	 * ReflectionFile inspection flag
	 *
	 * The file will be loaded through the
	 * PHP autoloading system
	 *
	 * @var integer
	 */
	const AUTOLOADABLE = Bitset::BIT_02;

	/**
	 * ReflectionFile inspection flag
	 *
	 * The file was loaded by a call to require() or include()
	 *
	 * @var integer
	 */
	const LOADED = Bitset::BIT_03;

	/**
	 *
	 * @param string $filename
	 *        	PHP source file path
	 * @param integer $flags
	 *        	Option flags
	 * @throws \ReflectionException::
	 */
	public function __construct($filename, $flags = 0)
	{
		if (!\file_exists($filename))
			throw new \ReflectionException(
				$filename . ': File not found', 404);
		$this->filename = \realpath($filename);
		$this->fileFlags = $flags;
	}

	/**
	 *
	 * @return string File absolute path
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Get the names of namespaces declared in the PHP file
	 *
	 * @return string[]
	 */
	public function getNamespaces()
	{
		return $this->getElements(T_NAMESPACE);
	}

	/**
	 *
	 * @param string $name
	 *        	Expected namespace name
	 * @return boolean TRUE if the file declare the namespace
	 */
	public function hasNamespace($name)
	{
		return Container::valueExists($this->getElements(T_NAMESPACE),
			$name);
	}

	/**
	 * Get a map of all file-space "use" statements
	 *
	 * @return string[] class name -> alias alias
	 */
	public function getUseStatements()
	{
		return $this->getElements(T_USE);
	}

	/**
	 *
	 * @return mixed[] Constant name and values
	 */
	public function getConstants()
	{
		return $this->getElements(T_CONST);
	}

	/**
	 * List of constant names defined in the file.
	 *
	 * @return string[] Qualified names of constants defined in the file
	 */
	public function getConstantNames()
	{
		return Container::keys($this->getElements(T_CONST));
	}

	/**
	 *
	 * @param string $name
	 *        	Expected constant nmae (local of qualified)
	 * @return boolean TRUE if the file declare the given constant.
	 */
	public function hasConstant($name)
	{
		return $this->hasElement(T_CONST, $name);
	}

	/**
	 *
	 * @param string $name
	 *        	Constant name (local or qualified)
	 * @throws \ReflectionException
	 * @return mixed Constant value
	 */
	public function getConstant($name)
	{
		$o = $this->getElement(T_CONST, $name);
		if ($o === FALSE)
			throw new \ReflectionException(
				'Constant ' . $name . ' does not exists.');
		return $o;
	}

	/**
	 *
	 * @return \ReflectionFunction[]
	 */
	public function getFunctions()
	{
		return $this->getElements(T_FUNCTION);
	}

	/**
	 *
	 * @param string $name
	 *        	Function function name (local or qualified)
	 * @return boolean TRUE if file defines the function
	 */
	public function hasFunction($name)
	{
		return $this->hasElement(T_FUNCTION, $name);
	}

	/**
	 *
	 * @param string $name
	 *        	Function name (local or qualified)
	 * @throws \ReflectionException
	 * @return \ReflectionFunction
	 */
	public function getFunction($name)
	{
		$o = $this->getElement(T_FUNCTION, $name);
		if ($o === FALSE)
			throw new \ReflectionException(
				'Function ' . $name . ' does not exists.');
		if (!($o instanceof \ReflectionFunction))
			$o = new \ReflectionFunction($name);
		return $o;
	}

	/**
	 * Get interface names defined in this file
	 *
	 * @return \ReflectionClass[]|string[]. Key is always the interface name, value is a
	 *         \ReflectionClass if the LOADED flag is set. Otherwise value is the same as the key.
	 */
	public function getInterfaces()
	{
		return $this->getElements(T_INTERFACE);
	}

	/**
	 *
	 * @param string $name
	 *        	Interface local or qualified name
	 * @return boolean TRUE if the file defines the given interface
	 */
	public function hasInterface($name)
	{
		return $this->hasElement(T_INTERFACE, $name);
	}

	/**
	 *
	 * @param string $name
	 *        	Interface name
	 * @throws \ReflectionException When $name cannot be found in file or if
	 *         the file was not loaded nor autoloadable
	 * @return \ReflectionClass
	 */
	public function getInterface($name)
	{
		$name = $this->getQualifiedName($name);
		$o = $this->getElement(T_INTERFACE, $name);
		if ($o === FALSE)
			throw new \ReflectionException(
				'Interface ' . $name . ' does not exists.');
		if (!($o instanceof \ReflectionClass))
			$o = new \ReflectionClass($name);
		return $o;
	}

	/**
	 * List of interface names defined in the file.
	 *
	 * @return string[] Qualified names of interfaces defined in the file.
	 */
	public function getInterfaceNames()
	{
		return Container::keys($this->getElements(T_INTERFACE));
	}

	/**
	 * Get trait names defined in this file
	 *
	 * @return \ReflectionClass[]|string[]. Key is always the trait name, value is a
	 *         \ReflectionClass if the LOADED flag is set. Otherwise value is the same as the key.
	 */
	public function getTraits()
	{
		return $this->getElements(T_TRAIT);
	}

	/**
	 *
	 * @param string $name
	 *        	Trait name (local or qualified)
	 * @return boolean TRUE if the file defines the given trait
	 */
	public function hasTrait($name)
	{
		return $this->hasElement(T_TRAIT, $name);
	}

	/**
	 *
	 * @param string $name
	 *        	Trait name (local or qualified)
	 * @throws \ReflectionException When $name cannot be found in file or if
	 *         the file was not loaded nor autoloadable
	 * @return \ReflectionClass|mixed|array|\ArrayAccess|\Psr\Container\ContainerInterface|\Traversable
	 */
	public function getTrait($name)
	{
		$name = $this->getQualifiedName($name);
		$o = $this->getElement(T_TRAIT, $name);
		if ($o === FALSE)
			throw new \ReflectionException(
				'Trait ' . $name . ' does not exists.');
		if (!($o instanceof \ReflectionClass))
			$o = new \ReflectionClass($name);
		return $o;
	}

	/**
	 * List of trait names defined in file.
	 *
	 * @return string[] Qualified names of traits defined in the file.
	 */
	public function getTraitNames()
	{
		return Container::keys($this->getElements(T_TRAIT));
	}

	/**
	 * List of classes defined in the file.
	 *
	 * Key are the class names.
	 *
	 * @return \ReflectionClass[]|string[]. Key is always the class name, value is a
	 *         \ReflectionClass if the LOADED flag is set. Otherwise value is the same as the key.
	 */
	public function getClasses()
	{
		return $this->getElements(T_CLASS);
	}

	/**
	 *
	 * @param string $name
	 *        	Class name (local or qualified)
	 * @return boolean TRUE if the file defines the given class
	 */
	public function hasClass($name)
	{
		return $this->hasElement(T_CLASS, $name);
	}

	/**
	 *
	 * @param string $name
	 *        	Class name (local or qualified)
	 * @throws \ReflectionException When $name cannot be found in file or if
	 *         the file was not loaded nor autoloadable
	 * @return \ReflectionClass
	 */
	public function getClass($name)
	{
		$name = $this->getQualifiedName($name);
		$o = $this->getElement(T_CLASS, $name);
		if ($o === FALSE)
			throw new \ReflectionException(
				'Class ' . $name . ' does not exists.');
		if (!($o instanceof \ReflectionClass))
			$o = new \ReflectionClass($name);
		return $o;
	}

	/**
	 * List of class names defined in the file
	 *
	 * @return string[] Qualified names of classes defined in the file.
	 */
	public function getClassNames()
	{
		return Container::keys($this->getElements(T_CLASS));
	}

	/**
	 * Get all interfaces, traits and classes
	 *
	 * @return ReflectionClass[]
	 */
	public function getStructures()
	{
		return \array_merge($this->getElements(T_INTERFACE),
			$this->getElements(T_TRAIT), $this->getElements(T_CLASS));
	}

	/**
	 * Get all interface, trait and class names defined in the file.
	 *
	 * @return string[] Qualified names of interfaces, traits and classes defined in the file.
	 */
	public function getStructureNames()
	{
		return \array_merge(
			Container::keys($this->getElements(T_INTERFACE)),
			Container::keys($this->getElements(T_TRAIT)),
			Container::keys($this->getElements(T_CLASS)));
	}

	/**
	 * Indicates
	 *
	 * @param unknown $name
	 * @return boolean
	 */
	public function hasStructure($name)
	{
		return $this->hasElement(T_INTERFACE, $name) ||
			$this->hasElement(T_TRAIT, $name) ||
			$this->hasElement(T_CLASS, $name);
	}

	/**
	 * Get interface, trait or class of the given name
	 *
	 * @param string $name
	 *        	Interface, trait or class name (local or qualified)
	 * @throws \ReflectionException
	 * @return ReflectionClass
	 */
	public function getStructure($name)
	{
		$n = $name;
		$name = $this->getQualifiedName($name);
		$o = false;
		foreach ([
			T_INTERFACE,
			T_TRAIT,
			T_CLASS
		] as $type)
		{
			$o = $this->getElement($type, $name);
			if ($o)
				break;
		}
		if ($o === FALSE)
			throw new \ReflectionException(
				'Interface, trait or class ' . $n . ' does not exists.');
		if (!($o instanceof \ReflectionClass))
			$o = new \ReflectionClass($name);
		return $o;
	}

	/**
	 * Get the qualified name of a class, interface or trait
	 * declared or used in this file.
	 *
	 * @param string $name
	 *        	Local PHP entity name
	 * @param
	 *        	array|string|NULL Kind of element to look for.
	 * @return string Qualified entity name. Name is resolved by looking into "use" statements
	 *         first,
	 *         then by assuming the class is part of the file namespace.
	 *
	 */
	public function getQualifiedName($name, $types = null)
	{
		if (\substr($name, 0, 1) == '\\')
			return \substr($name, 1);

		$map = $this->getUseStatements();
		$map = \array_flip($map);
		if (($qualifiedName = Container::keyValue($map, $name, false)))
			return $qualifiedName;

		$names = [
			$name
		];
		$namespaces = $this->getNamespaces();
		foreach ($namespaces as $ns)
			$names[] = $ns . '\\' . $name;

		if (\is_integer($types))
			$types = [
				$types
			];
		elseif (!\is_array($types))
			$types = [
				T_INTERFACE,
				T_TRAIT,
				T_CLASS,
				T_FUNCTION,
				T_CONST
			];

		foreach ($names as $expected)
		{
			foreach ($types as $type)
			{
				foreach ($this->definitions[$type] as $qualifiedName => $value)
				{
					if ($expected == $qualifiedName)
						return $qualifiedName;
				}
			}
		}

		throw new \InvalidArgumentException($name . ' not found');
	}

	/**
	 * Get the fully qualified name of a class, interface or trait
	 * declared or used in this file.
	 *
	 * @param string $name
	 *        	Local PHP entity name
	 * @param
	 *        	array|string|NULL Kind of element to look for.
	 * @return string Qualified entity name. Name is resolved by looking into "use" statements
	 *         first,
	 *         then by assuming the class is part of the file namespace.
	 *
	 */
	public function getFullyQualifiedName($name, $types = null)
	{
		if (\substr($name, 0, 1) == '\\')
			return $name;
		return '\\' . $this->getQualifiedName($name, $types);
	}

	private function getTokens()
	{
		if (!isset($this->tokens))
			$this->tokens = new \ArrayObject(
				\token_get_all(\file_get_contents($this->filename)));

		return $this->tokens;
	}

	private function getElements($type)
	{
		if (!isset($this->tokens))
			$this->parseFile();
		return $this->definitions[$type];
	}

	private function hasElement($type, $name)
	{
		if (Container::keyExists($this->getElements($type), $name))
			return true;

		if (\strpos($name, '\\') !== false)
			return false;

		foreach ($this->getNamespaces() as $ns)
		{
			if (Container::keyExists($this->getElements($type),
				$ns . '\\' . $name))
				return true;
		}

		return false;
	}

	private function getElement($type, $name)
	{
		if (!$this->hasElement($type, $name))
			return false;

		if (\strpos($name, '\\') === false) // Local name
			$name = $this->getQualifiedName($name, $type);
		return Container::keyValue($this->definitions[$type], $name,
			false);
	}

	private function parseFile()
	{
		$visitor = new PhpSourceTokenVisitor($this->getTokens());
		$indexes = [
			T_NAMESPACE => [],
			T_USE => [],
			T_CONST => [],
			T_FUNCTION => [],
			T_INTERFACE => [],
			T_TRAIT => [],
			T_CLASS => []
		];

		$this->definitions = $indexes;

		$visitor->setScopeEventHandler(
			function ($event, PhpSourceTokenScope $scope, $visitor) use (
			&$indexes) {
				if ($event == PhpSourceTokenVisitor::EVENT_SCOPE_END &&
				$scope->entityToken &&
				Container::keyExists($indexes,
					$scope->entityToken->getTokenType()))
				{
					$indexes[$scope->entityToken->getTokenType()][] = [
						$scope,
						$scope->entityToken
					];
				}
			});

		foreach ($visitor as $index => $token)
		{
			/** @var PhpSourceToken $token */
			$type = $token->getTokenType();
			if (!($type == T_USE || $type == T_CONST))
				continue;

			/** @var PhpSourceTokenScope $scope */
			$scope = $visitor->getCurrentScope();
			if ($scope->level == 0 ||
				($scope->entityToken &&
				$scope->entityToken->getTokenType() == T_NAMESPACE))
				$indexes[$type][] = [
					$scope,
					$token
				];
		}

		foreach ($indexes as $type => $elements)
		{
			foreach ($elements as $e)
			{
				$scope = $e[0];

				if ($type == T_FUNCTION && $scope->parentEntityToken &&
					!\in_array(
						$scope->parentEntityToken->getTokenType(),
						[
							T_OPEN_TAG,
							T_NAMESPACE
						]))
				{
					continue;
				}

				$token = $e[1];
				$name = '';
				$index = $this->skipWhitespace(
					$token->getTokenIndex() + 1);
				$index = $this->readQualifiedName($name, $index);

				$key = \count($this->definitions[$type]);
				$value = $name;

				if ($type == T_CONST)
				{
					$key = $name;
					if ($scope->entityToken &&
						$scope->entityToken->getTokenType() ==
						T_NAMESPACE)
					{
						$pi = $scope->entityToken->getTokenIndex();
						$pi = $this->skipWhitespace($pi + 1);
						$pn = '';
						$this->readQualifiedName($pn, $pi);
						$key = $pn . '\\' . $key;
					}

					$index = $this->skipWhitespace($index);
					$token = $this->tokens[$index];
					if (!($token->getTokenType() == T_STRING &&
						$token->getTokenValue() == '='))
						throw new \ReflectionException(
							'Expect "=" after constant name at line ' .
							$token->getTokenLine());
					$index++;
					$index = $this->skipWhitespace($index);
					$constantCode = '';
					while ($index < $this->tokens->count() &&
						($token = $this->tokens[$index]) &&
						!($token->getTokenType() == T_STRING &&
						$token->getTokenValue() == ';'))
					{

						if (!$token->isIgnorable())
							$constantCode .= $token->getTokenValue();
						$index++;
					}
					if ($this->fileFlags & self::SAFE)
						eval('$value = ' . $constantCode . ';');
					else
						$value = $constantCode;
				}
				elseif ($type == T_USE)
				{
					$key = $name;
					$index = $this->skipWhitespace($index);
					$token = $this->tokens[$index];

					if ($token->getTokenType() == T_AS)
					{
						$index++;
						$index = $this->skipWhitespace($index);
						$token = $this->tokens[$index];
						$value = $token->getTokenValue();
					}
					else
						$value = TypeDescription::getLocalName($value,
							true);
				}
				else
				{
					if ($scope->parentEntityToken &&
						$scope->parentEntityToken->getTokenType() ==
						T_NAMESPACE)
					{
						$pi = $scope->parentEntityToken->getTokenIndex();
						$pi = $this->skipWhitespace($pi + 1);
						$pn = '';
						$this->readQualifiedName($pn, $pi);
						$value = $pn . '\\' . $name;
					}

					switch ($type)
					{
						case T_FUNCTION:
							$key = $value;
							if ($this->fileFlags & self::LOADED)
								$value = new \ReflectionFunction($value);
						break;
						case T_CLASS:
						case T_INTERFACE:
						case T_TRAIT:
							$key = $value;
							if ($this->fileFlags &
								(self::LOADED | self::AUTOLOADABLE))
								$value = new \ReflectionClass($value);
						break;
					}
				}

				$this->definitions[$type][$key] = $value;
			}
		}
	}

	private function skipWhitespace($index)
	{
		do
		{
			if ($this->tokens[$index][0] != T_WHITESPACE)
				return $index;
			$index++;
		}
		while ($index < $this->tokens->count());
		return $index;
	}

	private function readQualifiedName(&$name, $index)
	{
		$name = '';
		$i = $index;

		if (defined('T_NAME_QUALIFIED')) // PHP 8
		{
			$expected = [
				T_NAME_FULLY_QUALIFIED,
				T_NAME_QUALIFIED,
				T_STRING
			];

			/** @var PhpSourceTOken $token */
			$token = $this->tokens[$index];

			if (!($token instanceof PhpSourceToken))
				throw new \LogicException(
					__METHOD__ . ' requires PhpSourceToken list');

			if (!\in_array($token->getTokenType(), $expected))
				throw new \ReflectionException(
					Container::implodeValues(
						\array_map('\token_name', $expected),
						[
							Container::IMPLODE_BETWEEN => ', ',
							Container::IMPLODE_BETWEEN_LAST => ' or '
						]) . ' expected');

			$name = $token->getTokenValue();
			$index++;
			return $index;
		}

		while ($index < $this->tokens->count())
		{
			$token = $this->tokens[$index];
			switch ($token[0])
			{
				case T_NS_SEPARATOR:
					$name .= $token[1];
				break;
				case T_STRING:
					if (!\preg_match(
						chr(1) . self::PATTERN_IDENTIFIER . chr(1),
						$token[1]))
						return $index;
					$name .= $token[1];
				break;
				default:
					return $index;
			}
			$index++;
		}
		return $index;
	}

	const PATTERN_IDENTIFIER = '[a-zA-Z_][a-zA-Z0-9_]*';

	/**
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Classes, interfaces, trait and namespace definitions
	 *
	 * @var string[][]
	 */
	private $definitions;

	/**
	 * Token array
	 *
	 * @var \ArrayObject
	 */
	private $tokens;

	/**
	 * Option flags
	 *
	 * @var integer
	 */
	private $fileFlags;
}
