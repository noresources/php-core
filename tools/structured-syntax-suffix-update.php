<?php
$url = 'https://www.iana.org/assignments/media-type-structured-suffix/structured-syntax-suffix.csv';
$filename = __DIR__ . '/../resources/iana/structured-syntax-suffix.csv';

/*
 * Download IAN registry
 */
if (false) // 403
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

$file = fopen($filename, 'r');
$suffixes = [];
$rowIndex = 0;
while ($row = \fgetcsv($file))
{
	if ($rowIndex++ == 0)
		continue;
	$description = \addslashes($row[0]);
	$suffix = \substr($row[1], 1);

	$suffixes[] = "'" . $suffix . "' => '" . $description . "'" . PHP_EOL;
}
fclose($file);
sort($suffixes);

$sourceFile = __DIR__ . '/../src/MediaType/StructuredSyntaxSuffixRegistry.php';

$source = file_get_contents($sourceFile);

$source = \preg_replace(chr(1) . '(--<<sufixes>>--)(.*?)(--<</sufixes>>--)' . chr(1) . 'sm',
	'\1*/' . implode(', ', $suffixes) . '/*\3', $source);

file_put_contents($sourceFile, $source);
