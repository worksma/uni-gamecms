<?php

class Autoloader
{
	const CORE_NAMESPACE = 'GameCMS';

	private $namespaces = [];

	public function register()
	{
		spl_autoload_register([$this, 'loader'], true, true);
	}

	public function loader($className)
	{
		$classNameParts = explode('\\', $className);

		if (stripos($className, '\\') !== false) {
			$namespace = $classNameParts[0];
			$className = $classNameParts[1];
		} else {
			$namespace = self::CORE_NAMESPACE;
		}

		if(array_key_exists($namespace, $this->namespaces)) {
			$directories = $this->namespaces[$namespace];

			$classFileName = 'class.' . strtolower($className) . '.php';

			foreach($directories as $directory) {
				if(file_exists($directory . $classFileName)) {
					require_once $directory . $classFileName;

					return true;
				}
			}

			throw new Exception("Class $className not found!");
		}
	}

	public function addNamespace($namespace, $directories)
	{
		if(is_array($directories)) {
			$this->namespaces[$namespace] = $directories;
		} else {
			$this->namespaces[$namespace] = [$directories];
		}
	}
}