<?php

class ExtraModule
{
	const MODULES_URL  = '../modules_extra/';

	private $moduleName;

	public function __construct($moduleName = '')
	{
		$this->moduleName = $moduleName;
	}

	public function moduleAssetUrl($assetUrl)
	{
		return self::MODULES_URL . $this->moduleName . '/' . $assetUrl;
	}

	public function getClassesDirectory()
	{
		return $this->getDirectory('base/inc/classes');
	}

	public function getDirectory($subDirectory = '')
	{
		$modulesDirectory = __DIR__ . '/../../modules_extra/' . $this->moduleName . '/';

		return $subDirectory == ''
			? $modulesDirectory
			: $modulesDirectory . $subDirectory . '/';
	}

	public function get($id)
	{
		$STH = pdo()->prepare(
			"SELECT * FROM modules WHERE id=:id LIMIT 1"
		);
		$STH->execute([':id' => $id]);

		return $STH->fetch(PDO::FETCH_OBJ);
	}
}