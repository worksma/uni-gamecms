<?PHP
	include_once("{$_SERVER['DOCUMENT_ROOT']}/inc/start.php");
	
	if($_SESSION['token'] != $_POST['token']) {
		exit(json_encode(['status' => '2']));
	}
	
	if(empty($_POST['phpaction'])) {
		exit(json_encode(['status' => '2']));
	}
	
	if(!is_worthy("m")) {
		exit(json_encode(['status' => '2']));
	}
	
	if(isset($_POST['give_exp'])) {
		$lvl = new Levels($pdo);
		$lvl->add_user_exp($_POST['id'], $_POST['exp']);
		exit(json_encode(['status' => '1']));
	}