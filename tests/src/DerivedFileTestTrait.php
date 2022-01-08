<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Core
 */
namespace NoreSources\Test;

trait DerivedFileTestTrait
{

	public function initializeDerivedFileTest($basePath = null,
		$referenceDirectory = null, $derivedDirectory = null)
	{
		$this->derivedDataFiles = new \ArrayObject();
		$this->basePath = ($basePath ? $basePath : __DIR__ . '/..');
		$this->referenceDirectory = ($referenceDirectory ? $referenceDirectory : 'reference');
		$this->derivedDirectory = ($derivedDirectory ? $derivedDirectory : 'derived');
	}

	public function cleanupDerivedFileTest()
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

			@rmdir($this->basePath . '/' . $this->derivedDirectory);
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
		$reference = $this->buildFilename($this->referenceDirectory,
			$method, $suffix, $extension);
		$derived = $this->buildFilename($this->derivedDirectory, $method,
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
		$directory = $this->derivedDirectory;

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
		return $this->buildFilename($this->derivedDirectory, $method,
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

	private $referenceDirectory;

	private $derivedDirectory;

	/**
	 *
	 * @var string
	 */
	private $basePath;
}