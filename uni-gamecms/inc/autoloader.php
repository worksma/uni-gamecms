<?PHP
	/*
		AutoLoader
	*/
	require_once __DIR__ . '/classes/class.autoloader.php';

	$Autoloader = new Autoloader();
	$Autoloader->addNamespace(
		Autoloader::CORE_NAMESPACE, [
			__DIR__ . '/classes/'
		]
	);

	$Autoloader->register();

	/*
		Source Query by xPaw
	*/
	require_once __DIR__ . '/../inc/classes/SourceQuery/bootstrap.php';
	use xPaw\SourceQuery\SourceQuery;
	$SourceQuery = new SourceQuery();