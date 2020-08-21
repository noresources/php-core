<?php
/**
 * Copyright © 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\Tools;

use Nette\PhpGenerator\PhpFile;
use NoreSources\Container;
use SplFileInfo;

// ////////////////////////////////////////////////////////////
$projectPath = \realPath(__DIR__ . '/..');
$composer = require ($projectPath . '/vendor/autoload.php');

$workingDirectory = new SplFileInfo(\getcwd());

$argv = Container::keyValue($_SERVER, 'argv', []);

$typeName = Container::keyValue($argv, 1, null);

if (!$typeName)
	throw new \ErrorException('Missing type name');

$typeName = \str_replace('\\', '/', $typeName);

$typeFilePath = new \SplFileInfo($workingDirectory->getRealPath() . '/' . $typeName . '.php');

if ($typeFilePath->isReadable())
	throw new \Exception('File already exists');

if (!\is_dir($typeFilePath->getPath()))
	if (!@mkdir($typeFilePath->getPath(), 0755, true))
		throw new \Exception('Failed to create ' . $typeFilePath->getPath());

$composerRootPath = $typeFilePath->getPath();
$composerFilePath = null;

while ($composerRootPath != '/')
{
	$composerFilePath = $composerRootPath . '/composer.json';
	if (\file_exists($composerFilePath))
	{
		$composerFilePath = new \SplFileInfo($composerFilePath);
		break;
	}

	$composerRootPath = \dirname($composerRootPath);
}

if (!($composerFilePath instanceof \SplFileInfo))
	throw new \Exception('No composer file');

$composer = \json_decode(\file_get_contents($composerFilePath->getRealPath()), true);
$autoload = Container::keyValue($composer, 'autoload', []);

$namespaceName = null;

if (Container::keyExists($autoload, 'psr-4'))
{
	foreach ($autoload['psr-4'] as $n => $p)
	{
		$path = $composerRootPath . '/' . $p;
		$part = \substr(\strval($typeFilePath), 0, \strlen($path));

		if ($part == $path)
		{
			$namespaceName = \rtrim($n, '\\');
			$sub = \dirname(\substr(\strval($typeFilePath), \strlen($part)));
			if (!empty($sub) && $sub != '.')
				$namespaceName .= '\\' . \str_replace('/', '\\', $sub);
			break;
		}
	}
}

$typeName = \basename($typeName);
$typeType = 'Class';
if (\preg_match('/.+Interface$/', $typeName))
	$typeType = 'Interface';
if (\preg_match('/.+Trait$/', $typeName))
	$typeType = 'Trait';

$headerFilename = 'resources/templates/file-file-header.txt';
$header = '';
foreach ([
	$composerRootPath,
	$projectPath
] as $path)
{
	$headerFilePath = $path . '/' . $headerFilename;
	if (\file_exists($headerFilePath))
		$header = \file_get_contents($headerFilePath);
}
$header = \str_replace('{year}', date('Y'), $header);

$typeFile = new PhpFile();
$typeFile->addComment($header);
$ns = $typeFile;
if ($namespaceName)
	$ns = $typeFile->addNamespace($namespaceName);

$type = \call_user_func([
	$ns,
	'add' . $typeType
], $typeName);

\file_put_contents(\strval($typeFilePath), \strval($typeFile));

