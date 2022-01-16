<?php
include_once '../inc/start_lite.php';
if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов chat_actions.php"); 
	exit('Ошибка: [Прямой вызов инклуда]');
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'],null))) {
	log_error("Неверный токен"); 
	exit();
}

$very = new Verification($pdo);

if (isset($_POST['chat_first_messages'])) {
	$i=0;
	$users_groups = get_groups($pdo);

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->query("SELECT chat.*, users.login, users.avatar, users.rights FROM chat LEFT JOIN users ON chat.user_id = users.id ORDER BY chat.id DESC LIMIT 50");
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	if ($count == 0){
		exit('<p class="t-c">Сообщений нет</p>');
	} else {
		for ($i=$count-1; $i >= 0; $i--) { 
			$date = expand_date($row[$i]['message_date'],8);
			$tpl->load_template('elements/chat_message.tpl');
			$tpl->set("{id}", $row[$i]['id']);
			$tpl->set("{user_id}", $row[$i]['user_id']);
			$tpl->set("{login}", $row[$i]['login']);
			$tpl->set("{avatar}", $full_site_host.$row[$i]['avatar']);
			$tpl->set("{date_full}", $date['full']);
			$tpl->set("{date_short}", $date['short']);
			$tpl->set("{text}", $row[$i]['message_text']);
			$tpl->set("{gp_name}", $users_groups[$row[$i]['rights']]['name']);
			$tpl->set("{gp_color}", $users_groups[$row[$i]['rights']]['color']);
			$tpl->set(
				"{gp_rights}",
				$users_groups[
				(array_key_exists('rights', $_SESSION))
					? $_SESSION['rights']
					: 0
				]['rights']
			);
			$tpl->compile( 'chat' );
			$tpl->clear();
		}
		$tpl->show($tpl->result['chat']);
		$tpl->global_clear();
		exit('<script>$("#last_mess").val('.$row['0']['id'].');</script>');
	}
}
if (isset($_POST['chat_send_message'])) {
	if (!is_auth()){
		exit(json_encode(array('status' => '2')));
	}

	$message_text = check($_POST['message_text'],null);

	if (empty($message_text)) {
		exit(json_encode(array('status' => '2')));
	}

	include_once "../inc/protect.php";

	$STH = $pdo->prepare("SELECT `rights` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $_SESSION['id'] ));
	$row = $STH->fetch();
	$row->rights;

	$users_groups = get_groups($pdo);
	if (is_worthy("z", $row->rights) || is_worthy("x", $row->rights)){
		exit(json_encode(array('status' => '2')));
	}

	$message_text = find_img_mp3($message_text, $_SESSION['id']);
	$STH = $pdo->prepare("INSERT INTO `chat` (`user_id`,`message_text`,`message_date`) values (:user_id, :message_text, :message_date)");
	if ($STH->execute(array( ':user_id' => $_SESSION['id'], ':message_text' => $message_text, ':message_date' => date("Y-m-d H:i:s") )) == '1') {
		up_online($pdo);

		/*
			Выдаём опыт
		*/
		$l = new Levels;
		$l->add_user_exp($_SESSION['id'], $l->configs()['chat_send_message']);

		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if (isset($_POST['chat_load_messages'])) {
	$load_val = check($_POST['load_val'],"int");
	if (empty($load_val)){
		exit('2');
	}

	$start = ($load_val)*50;
	$end = 50;
	$users_groups = get_groups($pdo);
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->query("SELECT chat.*, users.login, users.avatar, users.rights FROM chat LEFT JOIN users ON chat.user_id = users.id ORDER BY chat.id DESC LIMIT ".$start.", ".$end);
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	if ($count > 0){
		for ($i=$count-1; $i >= 0; $i--) { 
			$date = expand_date($row[$i]['message_date'],8);
			$tpl->load_template('elements/chat_message.tpl');
			$tpl->set("{id}", $row[$i]['id']);
			$tpl->set("{user_id}", $row[$i]['user_id']);
			$tpl->set("{login}", $row[$i]['login']);
			$tpl->set("{avatar}", $full_site_host.$row[$i]['avatar']);
			$tpl->set("{date_full}", $date['full']);
			$tpl->set("{date_short}", $date['short']);
			$tpl->set("{text}", $row[$i]['message_text']);
			$tpl->set("{gp_name}", $users_groups[$row[$i]['rights']]['name']);
			$tpl->set("{gp_color}", $users_groups[$row[$i]['rights']]['color']);
			$tpl->set(
				"{gp_rights}",
				$users_groups[
					(array_key_exists('rights', $_SESSION))
					? $_SESSION['rights']
					: 0
				]['rights']
			);
			$tpl->compile( 'chat' );
			$tpl->clear();
		}
		$tpl->show($tpl->result['chat']);
		$tpl->global_clear();
	}
	if($count != 50) {
		exit('<script>$("#load_val").val(0);</script>');
	} else {
		exit();
	}
}
if(isset($_POST['drop_img'])) {
	if (empty($_SESSION['id'])){
		exit(json_encode(array('status' => '2', 'data' => 'Авторизуйтесь, чтобы отправлять сообщения!')));
	}

	include_once "../inc/protect.php";

	$STH = $pdo->prepare("SELECT `rights` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $_SESSION['id'] ));
	$row = $STH->fetch();
	$row->rights;

	$users_groups = get_groups($pdo);
	if (is_worthy("z", $row->rights) || is_worthy("x", $row->rights)){
		exit(json_encode(array('status' => '2', 'data' => 'Недостаточно прав!')));
	}

	if (empty($_FILES['file']['name'])) {
		exit(json_encode(array('status' => '2', 'data' => 'Пустой файл')));
	} else {
		$path = 'files/filemanager/'.$_SESSION['id'].'/';
		$date = time();
		$message_text = '';

		if(!file_exists ( $_SERVER["DOCUMENT_ROOT"].'/'.$path )) {
			mkdir( $_SERVER["DOCUMENT_ROOT"].'/'.$path , 0777);
		}
		if(!file_exists ( $_SERVER["DOCUMENT_ROOT"].'/files/thumbs/'.$_SESSION['id'].'/' )) {
			mkdir($_SERVER["DOCUMENT_ROOT"].'/files/thumbs/'.$_SESSION['id'].'/', 0777);
		}

		if (if_img($_FILES['file']['name']) || if_mp3($_FILES['file']['name'])) {
			$file_type = substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.')+1);
			$source = $_FILES['file']['tmp_name'];
			$file = $path.$date.$_SESSION['id'].".".$file_type;
			$target = '../'.$file;
			if (!move_uploaded_file($source, $target)) {
				exit(json_encode(array('status' => '2', 'data' => 'Ошибка загрузки файла!')));
			}
		} else {
			exit(json_encode(array('status' => '2', 'data' => 'Файл должен являться изображением в <br> формате JPG,GIF или PNG, либо <br> аудизаписью в формате MP3')));
		}
		if(if_img($_FILES['file']['name'])) {
			$message_text = '<a href="'.$full_site_host.$file.'" class="thumbnail" data-lightbox="'.$_SESSION['id'].'"><img src="'.$full_site_host.$file.'" class="thumbnail-img"></a>';
		} elseif(if_mp3($_FILES['file']['name'])) {
			$message_text = '<audio src="'.$full_site_host.$file.'" controls="controls">Аудио файл: '.$full_site_host.$file.'</audio>';
		}
	}

	$STH = $pdo->prepare("INSERT INTO `chat` (`user_id`,`message_text`,`message_date`) values (:user_id, :message_text, :message_date)");
	if ($STH->execute(array( ':user_id' => $_SESSION['id'], ':message_text' => $message_text, ':message_date' => date("Y-m-d H:i:s") )) == '1') {
		up_online($pdo);
		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
?>