<?PHP
	if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/inc/configs/database.php')):
		$db = include($_SERVER['DOCUMENT_ROOT'] . '/inc/configs/database.php');
		
		try {
			$pdo = new PDO("mysql:host=".$db["hostname"].";dbname=".$db["dataname"], $db["username"], $db["password"]);
			$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$pdo->exec("set names utf8"); 
		}
		catch(PDOException $e) {
			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/logs/pdo_errors.txt", "[".date("Y-m-d H:i:s")."] : [".$e->getMessage()."]\r\n", FILE_APPEND);
			exit("Ошибка подключения к базе данных.");
		}
		
		unset($db);
		
		$STH = $pdo->query("SELECT * FROM config LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$conf = $STH->fetch();
	else:
		include_once 'config.php';

		$tpl = new Template;
		$tpl->dir = 'templates/admin/tpl/'; 

		$tpl->load_template('head.tpl');
		$tpl->set("{title}", "Установка UNI GameCMS");
		$tpl->set("{image}", "../files/miniatures/standart.jpg");
		$tpl->set("{cache}", "1");
		$tpl->set("{site_host}", $site_host);
		$tpl->set("{other}", "");
		$tpl->set("{token}", "");
		$tpl->compile( 'content' );
		$tpl->clear();

		if(!isset($_SERVER['SERVER_SOFTWARE'])) {
			$_SERVER['SERVER_SOFTWARE'] = '0';
		}
		if(!isset($_SERVER['GATEWAY_INTERFACE'])) {
			$_SERVER['GATEWAY_INTERFACE'] = '0';
		}
		if(!isset($_SERVER['DOCUMENT_ROOT'])) {
			$_SERVER['DOCUMENT_ROOT'] = '0';
		}

		$key = md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['GATEWAY_INTERFACE'].$_SERVER['DOCUMENT_ROOT']);

		$tpl->load_template('install.tpl');
		$tpl->set("{site_host}", $site_host);
		$tpl->set("{key}", $key);
		$tpl->compile( 'content' );
		$tpl->clear();

		$tpl->set( '{content}', $tpl->result['content'] );
		$tpl->load_template('main.tpl');
		$tpl->compile('main');
		eval(' ?>'.$tpl->result['main'].'<?php ');
		$tpl->global_clear();
		exit();
	endif;