<?php
	require_once __DIR__ . '/classes/class.autoloader.php';

	$Autoloader = new Autoloader();
	$Autoloader->addNamespace(
		Autoloader::CORE_NAMESPACE,
		[
			__DIR__ . '/classes/',
			__DIR__ . '/classes/SourceQuery/'
		]
	);

	$Autoloader->register();