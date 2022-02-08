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
	
	if(!is_admin()) {
		echo 'Ошибка: [Доступно только администрации]';
		exit(json_encode(['status' => '2']));
	}
	
	if(isset($_POST['send_very'])) {
		$very = new Verification($pdo);
		
		if($very->set_very($_POST['index'], '1')) {
			include_once "{$_SERVER['DOCUMENT_ROOT']}/inc/notifications.php";
			send_noty($pdo, "Поздравляем! Администрация одобрила Вашу заявку на Верификацию!", $_POST['index'], 1);
			
			exit(json_encode(['status' => '1', 'html' => $very->admin_request_verifications()]));
		}
		
		exit(json_encode(['status' => '2']));
	}
	
	if(isset($_POST['send_not_very'])) {
		$very = new Verification($pdo);
		
		if($very->set_very($_POST['index'], '0')) {
			include_once "{$_SERVER['DOCUMENT_ROOT']}/inc/notifications.php";
			send_noty($pdo, "Увы! Администрация отказала Вам в Верификации по причине: {$_POST['message']}", $_POST['index'], 3);
			
			exit(json_encode(['status' => '1', 'html' => $very->admin_request_verifications()]));
		}
		
		exit(json_encode(['status' => '2']));
	}

	if(isset($_POST['edit_very'])):
		$very = new Verification($pdo);

		if($very->set_very($_POST['user_id'], $_POST['value'])):
			incNotifications();
			send_noty(pdo(), "Администрация проекта " . ($_POST['value'] ? "выдала" : "изъяла") . " Верификацию.", $_POST['user_id'], ($_POST['value'] ? 2 : 3));

			exit(json_encode([
				'alert' => 'success',
				'message' => 'Вы успешно изменили статус верификации пользователя!'
			]));
		endif;
	endif;