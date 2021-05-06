<?PHP
	include_once("{$_SERVER['DOCUMENT_ROOT']}/inc/start.php");
	
	if(empty($_POST['phpaction'])) {
		log_error("Прямой вызов verification/actions.php"); 
		echo 'Ошибка: [Прямой вызов инклуда]';
		
		exit(json_encode(['status' => '2']));
	}
	
	if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'], null))) {
		log_error("Неверный токен"); 
		echo 'Ошибка: [Неверный токен]';
		
		exit(json_encode(['status' => '2']));
	}
	
	if(empty($_SESSION['id'])) {
		echo 'Ошибка: [Доступно только авторизованным]';
		exit(json_encode(['status' => '2']));
	}
	
	if(isset($_POST['get_very'])) {
		$very = new Verification($pdo);
		
		if($very->is_very($_SESSION['id'], 0)) {
			if($very->send_very($_SESSION['id'])) {
				exit(json_encode(['status' => '1']));
			}
		}
		
		exit(json_encode(['status' => '2']));
	}
	
	if(isset($_POST['close_event'])) {
		if(setcookie("very_info_{$_POST['index']}", "1", strtotime('+7 days'), '/', $_SERVER['HOST_NAME'])) {
			exit(json_encode(['status' => '1']));
		}
		
		exit(json_encode(['status' => '2']));
	}