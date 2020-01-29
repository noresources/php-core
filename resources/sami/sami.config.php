<?php
var_dump($_ENV);

return new Sami\Sami(__DIR__ . '/../../src',
	[
		'title' => 'NoreSource Core',
		'build_dir' => './doc',
		'cache_dir' => './doc/cache'
	]);
