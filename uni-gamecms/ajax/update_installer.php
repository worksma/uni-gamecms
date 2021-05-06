<?PHP
	session_set_cookie_params(0, '/', '', false, true);
	session_start();
	
	if(!isset($protection)) {
		$protection = 1;
	}
	
	include_once("{$_SERVER['DOCUMENT_ROOT']}/inc/db.php");
	include_once("{$_SERVER['DOCUMENT_ROOT']}/inc/config.php");
	include_once("{$_SERVER['DOCUMENT_ROOT']}/inc/functions.php");
	
	if(empty($_POST['phpaction'])) {
		log_error("Прямой вызов update_installer.php");
		exit("Ошибка: [Прямой вызов инклуд]");
	}
	
	if(!is_admin()) {
		exit("Ошибка: [Доступно только администраторам]");
	}
	
	/*
		Powered by OverGame
		* https://vk.com/i17bb - моя страница.
		* https://worksma.ru - магазин цифровых товаров.
		* https://vfoxcms.ru - проект разработчиков.
	*/
	if(isset($_POST['install_update'])) {
		ignore_user_abort(1);
		set_time_limit(0);
		
		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		
		$sth = $pdo->query("SELECT `version`, `update_link` FROM `config__secondary` LIMIT 1");
		$sth->setFetchMode(PDO::FETCH_OBJ);
		$secondary = $sth->fetch();
		
		$dataUpdate = json_decode($secondary->update_link);
		$pdo->exec(trim(base64_decode($dataUpdate->request)));
		
		$rD = download_file(['temp' => 'modules/updates', 'file' => "update_{$dataUpdate->version}.zip", 'url' => $dataUpdate->file]);
		
		if($rD['status']) {
			$zipArchive = new ZipArchive;
			$zipOpen = $zipArchive->open($rD['file']);
			
			if($zipOpen === true) {
				$zipArchive->extractTo("{$_SERVER['DOCUMENT_ROOT']}/");
				
				if($zipArchive->close()) {
					$pdo->query("UPDATE `config__secondary` SET `version`='{$dataUpdate->version}', `update_link`='' WHERE 1");
					
					unlink($rD['file']);
					exit(json_encode(array('status' => '1')));
				}
			}
		}
	}
?>