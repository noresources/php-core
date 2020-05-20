<?php
/**
 * You are supposed to run sami in the directory where you want
 * to output the API documentation
 */
return new Sami\Sami(__DIR__ . '/../../src', [
	'title' => 'NoreSource Core',
	'build_dir' => 'api'
]);
