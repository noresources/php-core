<?php
require (__DIR__ . '/../vendor/autoload.php');

use NoreSources\MediaType\MediaType;
use NoreSources\Container;

$url = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
$filename = __DIR__ . '/../resources/iana/httpd-mime.types';

$outputFileBase = __DIR__ . '/../src/MediaType/MediaTypeFileExtensionRegistry';

/*
 * Download Apache httpd public registry
 */
if (\in_array('--download', $_SERVER['argv'])) // 403
{
	$file = fopen($filename, 'w');
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_FILE, $file);
	curl_exec($curl);
	curl_close($curl);
	fclose($file);
}

/*
 * Update source code
 */

$content = file_get_contents($filename);
$lines = explode("\n", $content);

$match = [];
$typeMap = [];
$extensionMap = [];
foreach ($lines as $index => $line)
{

	if (!\preg_match(
		chr(1) . '^(?:#\s+)?(' . MediaType::STRING_PATTERN . ')\s+([a-zA-Z0-9. ]+)' . chr(1), $line,
		$match))
	{
		continue;
	}

	$mediaType = MediaType::fromString($match[1]);
	$extensions = \preg_split('/\s+/', $match[5]);

	//echo (\sprintf('%04d ', $index) . $line . PHP_EOL);
	echo (sprintf('%-32.32s %s', \strval($mediaType), \implode(', ', $extensions)) . PHP_EOL);

	foreach ($extensions as $extension)
	{
		$extensionMap[$extension] = \strval($mediaType);
	}

	if (!Container::keyExists($typeMap, $mediaType->getMainType()))
	{
		$typeMap[$mediaType->getMainType()] = [];
	}

	$typeMap[$mediaType->getMainType()][\strval($mediaType->getSubType())] = $extensions;
}

file_put_contents($outputFileBase . '/extensions.json', \json_encode($extensionMap));
foreach ($typeMap as $main => $sub)
{
	file_put_contents($outputFileBase . '/types.' . $main . '.json', \json_encode($sub));
}


