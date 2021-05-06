<?PHP
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

	if ($_POST['key'] != $key) {
		exit(json_encode(array('status' => '2', 'data' => 'Ошибка: [Прямой вызов исполнителя]')));
	}

	include_once '../../inc/functions.php';
	include_once '../../inc/classes/class.users.php';

	if (isset($_POST['try_connect'])) {
		$host = check($_POST['host'],null);
		$base = check($_POST['base'],null);
		$user = check($_POST['user'],null);
		$pass = check($_POST['pass'],null);

		if (empty($host) or empty($base) or empty($user) or empty($pass)) {
			exit(json_encode(array('status' => 2, 'data' => 'Заполните все поля.')));
		}

		if(db_connect($host, $base, $user, $pass)) {
			exit(json_encode(array('status' => 1, 'data' => 'Соединение установленно')));
		} else {
			exit(json_encode(array('status' => 2, 'data' => 'Ошибка подключения к базе данных')));
		}
	}

	if (isset($_POST['install'])) {
		$host = check($_POST['host'],null);
		$base = check($_POST['base'],null);
		$user = check($_POST['user'],null);
		$pass = check($_POST['pass'],null);
		$name = check($_POST['name'],null);
		$password = check($_POST['password'],null);
		$email = check($_POST['email'],null);

		if (empty($host) or empty($base) or empty($user) or empty($pass) or empty($name) or empty($password)) {
			exit(json_encode(array('status' => '2', 'data' => 'Заполните все поля.')));
		}

		if (!is_writable('../../inc/db.php')) {
			exit(json_encode(array('status' => '2', 'data' => 'Файл inc/db.php недоступен для записи!')));
		}
		
		if (!is_writable('../../files/temp/temp.txt')) {
			exit(json_encode(array('status' => '2', 'data' => 'Файл files/temp/temp.txt недоступен для записи!')));
		}
		
		$sqli = new mysqli($host, $user, $pass, $base);
		$pdo = db_connect($host, $base, $user, $pass);
		
		if($sqli->connect_errno) {
			exit(json_encode(array('status' => 2, 'data' => 'Ошибка подключения к базе данных')));
		}

		mysqli_set_charset($sqli, "utf8");
		
		$U = new Users;

		$salt = crate_pass(10, 2);
		$code = crate_pass(10, 2);
		$password = $U->convert_password($password, $salt);
		
		$d = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/modules/install/base.sql");
		
		$d = str_replace("{name}", $name, $d);
		$d = str_replace("{password}", $password, $d);
		$d = str_replace("{email}", $email, $d);
		$d = str_replace("{salt}", $salt, $d);
		$d = str_replace("{code}", $code, $d);
		
		$sqli->multi_query($d);
		
		ini_set('max_execution_time', 1200);
		ignore_user_abort(1);
		set_time_limit(0);
		sleep(60);
		
		if(!$sqli->query("UPDATE `config` SET `password`='{$password}', `name`='{$name}', `salt`='{$salt}', `code`='{$code}', `email`='{$email}' WHERE `id`='1'")) {
			if(!$pdo->query("UPDATE `config` SET `password`='{$password}', `name`='{$name}', `salt`='{$salt}', `code`='{$code}', `email`='{$email}' WHERE `id`='1'")) {
				exit(json_encode(array('status' => 2, 'data' => 'База данных не успела импортироваться, нажми еще раз "Установка"')));
			}
		}
		
		$cfg = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/modules/install/database.bak.tpl");
		$cfg = str_replace('{hostname}', $host, $cfg);
		$cfg = str_replace('{username}', $user, $cfg);
		$cfg = str_replace('{database}', $base, $cfg);
		$cfg = str_replace('{password}', $pass, $cfg);
		
		if(file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/inc/db.php", $cfg)) {
			exit(json_encode(array('status' => 1, 'data' => 'Установка успешно завершена!')));
		}
		
		exit(json_encode(array('status' => 2, 'data' => 'Неизвестная ошибка..')));
	}