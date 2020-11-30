<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Test;

class DerivedFileManager extends \PHPUnit\Framework\TestCase
{

	const DIRECTORY_REFERENCE = 'reference';

	const DIRECTORY_DERIVED = 'derived';

	public function __construct($basePath = null)
	{
		$this->derivedDataFiles = new \ArrayObject();
		$this->basePath = ($basePath ? $basePath : __DIR__ . '/..');
	}

	public function __destruct()
	{
		if (count($this->derivedDataFiles))
		{
			foreach ($this->derivedDataFiles as $path => $persistent)
			{
				if ($persistent)
					continue;
				if (file_exists($path))
				{
					unlink($path);
				}
			}

			@rmdir($this->basePath . '/' . self::DIRECTORY_DERIVED);
		}
	}

	/**
	 * Save derived file, compare to reference
	 *
	 * @param unknown $data
	 * @param unknown $suffix
	 * @param unknown $extension
	 */
	public function assertDerivedFile($data, $method, $suffix,
		$extension, $label = '', $eol = null)
	{
		$reference = $this->buildFilename(self::DIRECTORY_REFERENCE,
			$method, $suffix, $extension);
		$derived = $this->buildFilename(self::DIRECTORY_DERIVED, $method,
			$suffix, $extension);
		if (\strlen($label) == 0)
			$label = \basename($derived);
		$label = (strlen($label) ? ($label . ': ') : '');

		$result = $this->createDirectoryPath($derived);

		if ($result)
		{
			$result = file_put_contents($derived, $data);
			$this->assertNotFalse($result, $label . 'Write derived data');
			$this->assertFileExists($derived,
				$label . 'Derived file exists');

			if ($result)
			{
				$this->derivedDataFiles->offsetSet($derived, false);
			}
		}

		if (\is_file($reference))
		{
			$this->derivedDataFiles->offsetSet($derived, true);
			// $this->assertFileEquals($reference, $derived, $label . 'Compare with reference');
			$this->assertEquals($this->loadFile($reference, 'lf'),
				$this->convertEndOfLine($data, 'lf'), $label);
			$this->derivedDataFiles->offsetSet($derived, false);
		}
		else
		{
			$result = $this->createDirectoryPath($reference);

			if ($result)
			{
				$result = file_put_contents($reference, $data);
				$this->assertNotFalse($result,
					$label . 'Write reference data to ' . $reference);
				$this->assertFileExists($reference,
					$label . 'Reference file exists');
			}
		}

		return $reference;
	}

	public function setPersistent($path, $value)
	{
		if ($this->derivedDataFiles->offsetExists($path))
			$this->derivedDataFiles->offsetSet($path, $value);
	}

	public function registerDerivedFile($subDirectory, $method, $suffix,
		$extension)
	{
		$directory = self::DIRECTORY_DERIVED;

		$path = self::buildFilename($directory, $method, $suffix,
			$extension);

		if (\is_string($subDirectory) && strlen($subDirectory))
		{
			$pi = pathinfo($path);
			$path = $pi['dirname'] . '/' . $subDirectory . '/' .
				$pi['basename'];
			$directory .= '/' . $subDirectory;
		}

		self::createDirectoryPath($path);
		$this->derivedDataFiles->offsetSet($path, false);

		return $path;
	}

	/**
	 *
	 * @param string $method
	 * @param string $suffix
	 * @param string $extension
	 *
	 * @return string
	 */
	public function getDerivedFilename($method, $suffix, $extension)
	{
		return $this->buildFilename(self::DIRECTORY_DERIVED, $method,
			$suffix, $extension);
	}

	private function buildFilename($directory, $method, $suffix,
		$extension)
	{
		if (preg_match('/.*\\\\(.*?)Test::test(.*)$/', $method, $m))
		{
			$cls = $m[1];
			$method = str_replace($cls, '', $m[2]);
		}
		elseif (preg_match('/.*\\\\(.*?)Test::(.*)$/', $method, $m))
		{
			$cls = $m[1];
			$method = '';
		}
		else
			throw new \Exception('Invalid method ' . $method);

		if (\is_string($suffix) && strlen($suffix))
			$method .= '_' .
				preg_replace('/[^a-zA-Z0-9._-]/', '_', $suffix);

		$name = $cls . '_' . $method . '.' . $extension;
		$name = preg_replace('/_+/', '_', $name);

		return $this->basePath . '/' . $directory . '/' . $name;
	}

	private function createDirectoryPath($filepath)
	{
		$path = dirname($filepath);
		$result = true;
		if (!is_dir($path))
			$result = @mkdir($path, 0777, true);
		$this->assertTrue($result, 'Create directory ' . $path);
		return $result;
	}

	private function loadFile($file, $eol)
	{
		return $this->convertEndOfLine(file_get_contents($file), $eol);
	}

	private function convertEndOfLine($data, $eol)
	{
		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\r", "\n", $data);

		if ($eol == 'crlf')
		{
			$data = str_replace("\n", "\r\n", $data);
		}
		elseif ($eol == 'cr')
		{
			$data = str_replace("\n", "\r",
				str_replace("\r\n", "\n", $data));
		}

		return $data;
	}

	/**
	 *
	 * @var array
	 */
	private $derivedDataFiles;

	/**
	 *
	 * @var string
	 */
	private $basePath;
}