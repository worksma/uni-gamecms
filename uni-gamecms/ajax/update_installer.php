<?PHP
	@session_set_cookie_params(0, "/", "", false, true);
	@session_start();

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	if(!isset($protection)):
		$protection = 1;
	endif;

	require_once("../inc/start.php");

	if(empty($_POST['phpaction'])):
		log_error("Прямой вызов update_installer.php");
		exit("Ошибка: [Прямой вызов скрипта]");
	endif;

	if(empty($_SESSION['id']) || !is_worthy(h)):
		exit("Ошибка: [Доступно только администраторам]");
	endif;

	if(isset($_POST['install_update'])):
		ignore_user_abort(1);
		set_time_limit(0);

		$api = new System;
		$secondary = json_decode($api->secondary()->update_link);

		/*
			Предворительная проверка папки
		*/
		$dir_updates = $_SERVER['DOCUMENT_ROOT'] . '/modules/updates';

		if(!file_exists($dir_updates)):
			mkdir($dir_updates);
		endif;

		/*
			Загрузка файла
		*/
		$result = $api->download("modules/updates", date("YmdHis") . ".zip", $secondary->file);

		if($result->compiled):
			$zip = new ZipArchive;
			
			if($zip->open($result->file) === true):
				$zip->extractTo($_SERVER['DOCUMENT_ROOT'] . '/');

				if($zip->close()):
					unlink($result->file);

					if(file_exists("$dir_updates/import_primary.php")):
						require_once("$dir_updates/import_primary.php");
						unlink("$dir_updates/import_primary.php");
					endif;

					if(file_exists("$dir_updates/import.sql")):
						try {
							pdo()->exec(trim(file_get_contents("$dir_updates/import.sql")));
							unlink("$dir_updates/import.sql");
						}
						catch (PDOException $e) {
							log_error($e->getMessage());
							exit(json_encode(['status' => '2']));
						}
					endif;

					try {
						pdo()->exec("UPDATE `config__secondary` SET `version`='{$secondary->name}', `update_link`='' LIMIT 1");
						
						if(file_exists("$dir_updates/import_secondary.php")):
							require_once("$dir_updates/import_secondary.php");
							unlink("$dir_updates/import_secondary.php");
						endif;

						exit(json_encode(['status' => '1']));
					}
					catch (PDOException $e) {
						log_error($e->getMessage());
						exit(json_encode(['status' => '2']));
					}
				endif;
			endif;
		endif;

		exit(json_encode(['status' => '2']));
	endif;