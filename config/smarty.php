<?php

return [
	'extension' => 'tpl',
	'caching' => true,
	'cache_lifetime' => 120,
	'escape_html' => false,

	'template_path' => base_path('resources/views'),
	'cache_path' => storage_path('smarty/cache'),
	'compile_path' => storage_path('smarty/compile'),
	'plugins_path' => base_path('resources/smarty/plugins'),
	'config_path' => base_path('resources/smarty/config'),
];
