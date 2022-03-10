<?PHP
	if(!isset($_SERVER['SERVER_SOFTWARE'])):
		$_SERVER['SERVER_SOFTWARE'] = 0;
	endif;
	
	if(!isset($_SERVER['GATEWAY_INTERFACE'])):
		$_SERVER['GATEWAY_INTERFACE'] = 0;
	endif;
	
	if(!isset($_SERVER['DOCUMENT_ROOT'])):
		$_SERVER['DOCUMENT_ROOT'] = 0;
	endif;
	
	$key = md5($_SERVER['SERVER_SOFTWARE'] . $_SERVER['GATEWAY_INTERFACE'] . $_SERVER['DOCUMENT_ROOT']);
	
	if($_POST['key'] != $key):
		exit(json_encode([
			'status' => '2',
			'data' => 'Ошибка: [Прямой вызов исполнителя]'
		]));
	endif;
	
	require($_SERVER['DOCUMENT_ROOT'] . '/inc/functions.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/inc/classes/class.users.php');
	
	if(isset($_POST['try_connect'])):
		$hostname = check($_POST['host'], null);
		$username = check($_POST['user'], null);
		$password = check($_POST['pass'], null);
		$dataname = check($_POST['base'], null);
		
		if(empty($hostname) || empty($username) || empty($password) || empty($dataname)):
			exit(json_encode([
				'status' => '2',
				'data' => 'Заполните все данные'
			]));
		endif;
		
		if(db_connect($hostname, $dataname, $username, $password)):
			exit(json_encode([
				'status' => '1',
				'data' => 'Соединение установлено'
			]));
		else:
			exit(json_encode([
				'status' => '2',
				'data' => 'Ошибка подключения к базе данных'
			]));
		endif;
	endif;
	
	if(isset($_POST['install'])):
		$hostname = check($_POST['host'], null);
		$username = check($_POST['user'], null);
		$password = check($_POST['pass'], null);
		$dataname = check($_POST['base'], null);
		$project = check($_POST['name'], null);
		
		if(empty($hostname) || empty($username) || empty($password) || empty($dataname)):
			exit(json_encode([
				'status' => '2',
				'data' => 'Заполните все данные'
			]));
		endif;
		
		if(!is_writable($_SERVER['DOCUMENT_ROOT'] . '/inc/db.php')):
			exit(json_encode([
				'status' => '2',
				'data' => 'Файл [inc/db.php] недоступен для записи!'
			]));
		endif;
		
		if(!is_writable($_SERVER['DOCUMENT_ROOT'] . '/files/temp/temp.txt')):
			exit(json_encode([
				'status' => '2',
				'data' => 'Файл [files/temp/temp.txt] недоступен для записи!'
			]));
		endif;
		
		$pdo = db_connect($hostname, $dataname, $username, $password);
		
		if(!$pdo):
			exit(json_encode([
				'status' => '2',
				'data' => 'Ошибка подключения к базе данных'
			]));
		endif;
		
		$pdo->exec("set names utf8"); 
		
		$result = importSqlFile($pdo, ($_SERVER['DOCUMENT_ROOT'] . '/modules/install/base.sql'), [
			'project' => $project,
			'salt' => crate_pass(10, 2),
			'code' => crate_pass(10, 2)
		]);
		
		if($result === false):
			exit(json_encode([
				'status' => '2',
				'data' => 'Ошибка импорта базы данных!'
			]));
		endif;
		
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/inc/configs/database.php', "<?PHP
	return [
		'hostname' => '{$hostname}',
		'username' => '{$username}',
		'password' => '{$password}',
		'dataname' => '{$dataname}'
	];");
		
		exit(json_encode([
			'status' => '1',
			'data' => 'Успешная установка!'
		]));
	endif;