<?php
include_once '../inc/start.php';
if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов actions_z.php"); 
	echo 'Ошибка: [Прямой вызов инклуда]';
	exit(json_encode(array('status' => '2')));
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'],null))) {
	log_error("Неверный токен");
	echo 'Ошибка: [Неверный токен]';
	exit(json_encode(array('status' => '2')));
}

/* Форум
=========================================*/
if (isset($_POST['add_section']) and is_worthy("t")) {
	$name = check($_POST['section_name'],null);
	$groups = ';';

	if(!isset($_POST['0'])) {
		for ($i=0; $i <= count($users_groups); $i++) { 
			if(isset($users_groups[$i]['id']) && isset($_POST[$users_groups[$i]['id']]) && $_POST[$users_groups[$i]['id']] == 'on') {
				$groups .= $users_groups[$i]['id'].';';
			}
		}
	}

	if (empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'section_name', 'data' => 'Заполните!')));
	}

	if (mb_strlen($name, 'UTF-8') > 255) {
		exit (json_encode(array('status' => '2', 'input' => 'section_name', 'data' => 'Не более 255 символов!')));
	}

	$Forum = new Forum($pdo);
	if($Forum->add_section($name, $groups)) {
		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if (isset($_POST['edit_section']) and is_worthy("t")) {
	$name = check($_POST['section_name'],null);
	$id = check($_POST['id'],"int");
	$groups = ';';

	foreach ($_POST as $key => $value) {
		if($key == check($key,"int") && $key != 0 && $value == 'on') {
			if(isset($users_groups[$key]['id'])) {
				$groups .= $key.';';
			}
		}
	}

	if (empty($id)) {
		exit (json_encode(array('status' => '2')));
	}
	if (empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'section_name', 'data' => 'Заполните!')));
	}

	$Forum = new Forum($pdo);
	$Forum->edit_section($id, $name, $groups);

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['dell_section']) and is_worthy("t")) {
	$id = check($_POST['id'],"int");

	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	if($Forum->dell_section($id)) {
		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if (isset($_POST['up_section']) and is_worthy("t")) {
	$id = check($_POST['id'],"int");

	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	if($Forum->up_section($id)) {
		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if (isset($_POST['down_section']) and is_worthy("t")) {
	$id = check($_POST['id'],"int");

	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	if($Forum->down_section($id)) {
		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
/* Дать/забрать деньги
=========================================*/
if (isset($_POST['give_money']) and is_worthy("m")) {
	$id = checkJs($_POST['id'],"int");
	$money = checkJs($_POST['money'],"float");

	if (empty($id) or empty($money)) {
		exit (json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT id,shilings FROM users WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)){
		exit (json_encode(array('status' => '2')));
	} else {
		$res = $row->shilings+$money;
		$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id='$id' LIMIT 1");
		$STH->execute(array( 'shilings' => $row->shilings+$money ));

		$STH = $pdo->prepare("INSERT INTO money__actions (date,shilings,author,type,gave_out) values (:date, :shilings, :author, :type, :gave_out)");
		$STH->execute(array( 'date' => date("Y-m-d H:i:s"),'shilings' => $money,'author' => $id,'type' => '3','gave_out' => $_SESSION['id'] ));
		exit (json_encode(array('status' => '1', 'res' => $res)));
	}
}
if (isset($_POST['pick_up_money']) and is_worthy("m")) {
	$id = checkJs($_POST['id'],"int");
	$money = checkJs($_POST['money'],"float");

	if (empty($id) or empty($money)) {
		exit (json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT id,shilings FROM users WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)){
		exit (json_encode(array('status' => '2')));
	} else {
		if ($row->shilings-$money < 0) {
			$money2 = 0;
		} else {
			$money2 = $row->shilings-$money;
		}

		$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id='$id' LIMIT 1");
		$STH->execute(array( 'shilings' => $money2 ));

		$STH = $pdo->prepare("INSERT INTO money__actions (date,shilings,author,type,gave_out) values (:date, :shilings, :author, :type, :gave_out)");
		$STH->execute(array( 'date' => date("Y-m-d H:i:s"),'shilings' => -$money,'author' => $id,'type' => '3','gave_out' => $_SESSION['id'] ));
		exit (json_encode(array('status' => '1', 'res' => $money2)));
	}
}
if (isset($_POST['take_proc']) and is_worthy("c")) {
	$id = checkJs($_POST['id'],"int");
	$proc = checkJs($_POST['proc'],"int");

	if (empty($id)) {
		exit (json_encode(array('status' => '2')));
	}

	if (empty($proc)) {
		$proc = 0;
	}

	$STH = $pdo->query("SELECT id FROM users WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)){
		exit (json_encode(array('status' => '2')));
	} else {
		if ($proc > 100) {
			$proc = 100;
		}

		$STH = $pdo->prepare("UPDATE users SET proc=:proc WHERE id='$id' LIMIT 1");
		$STH->execute(array( 'proc' => $proc ));

		incNotifications();
		$noty = give_proc_noty($_SESSION['id'], $_SESSION['login'], $proc);
		send_noty($pdo, $noty, $id, 1);

		exit (json_encode(array('status' => '1', 'res' => $proc)));
	}
}

/* Удаление юзера
=========================================*/
if (isset($_POST['dell_user']) and (is_worthy("g") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	$type = checkJs($_POST['type'],"int");
	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}
	if (empty($type)) {
		$type = 1;
	}

	$STH = $pdo->query("SELECT id,login,avatar FROM users WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)) {
		exit(json_encode(array('status' => '2')));
	}

	if($type == 1 || $type == 4 || $type == 2) {
		$Forum = new Forum($pdo);
		$STH = $pdo->prepare("SELECT `id` FROM `forums__topics` WHERE `author`=:author"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':author' => $id ));
		while($row2 = $STH->fetch()) { 
			$Forum->dell_topic($row2->id);
		}
		$STH = $pdo->prepare("SELECT `id` FROM `forums__messages` WHERE `author`=:author"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':author' => $id ));
		while($row2 = $STH->fetch()) { 
			$Forum->dell_answer($row2->id, 1);
		}

		$pdo->exec("DELETE FROM thanks WHERE author='$id'");

		$STH = $pdo->prepare("DELETE FROM events WHERE author=:author");
		$STH->execute([':author' => $id]);

		unset($Forum);
	}
	if($type == 1) {
		if ($row->avatar != 'files/avatars/no_avatar.jpg') {
			if(file_exists ( '../'.$row->avatar )) {
				unlink('../'.$row->avatar);
			}
		}

		if(file_exists ( $_SERVER["DOCUMENT_ROOT"].'/files/filemanager/'.$row->id.'/' )) {
			removeDirectory($_SERVER["DOCUMENT_ROOT"].'/files/filemanager/'.$row->id.'/');
		}

		$STH = $pdo->prepare("DELETE FROM events WHERE data_id=:data_id AND type = '3' LIMIT 1");
		$STH->execute([':data_id' => $id]);

		write_log("Удален пользователь c ID:".$row->id." Login:".$row->login);
		$pdo->exec("DELETE FROM users WHERE id='$id' LIMIT 1");
	}
	if($type == 2) {
		write_log("Очищена вся активность пользователя ID:".$row->id." Login:".$row->login);
	}
	if($type == 3) {
		write_log("Удалены все сообщения из чата пользователя ID:".$row->id." Login:".$row->login);
	}
	if($type == 4) {
		write_log("Очищена вся сообщения с форума пользователя ID:".$row->id." Login:".$row->login);
	}

	if($type == 1 || $type == 3 || $type == 2) {
		$pdo->exec("DELETE FROM chat WHERE user_id='$id'");
		$pdo->exec("DELETE FROM last_online WHERE user_id='$id'");
	}
	if($type == 1 || $type == 2) {
		$pdo->exec("DELETE FROM users__friends WHERE id_sender='$id' or id_taker='$id'");
		$STH = $pdo->query("SELECT id FROM pm__dialogs WHERE user_id1='$id' or user_id2='$id'");
		$STH->execute();
		$row = $STH->fetchAll();
		for ($i=0; $i < count($row); $i++) { 
			$dialog_id = $row[$i]['id'];
			$pdo->exec("DELETE FROM pm__messages WHERE dialog_id='$dialog_id'");
		}
		$pdo->exec("DELETE FROM pm__dialogs WHERE user_id1='$id' or user_id2='$id'");

		$STH = $pdo->query("SELECT id FROM bans WHERE author='$id'");
		$STH->execute();
		$row = $STH->fetchAll();
		for ($i=0; $i < count($row); $i++) {
			$ban_id = $row[$i]['id'];
			$pdo->exec("DELETE FROM bans__comments WHERE ban_id='$ban_id'");
		}
		$pdo->exec("DELETE FROM bans WHERE author='$id'");

		$STH = $pdo->query("SELECT id FROM complaints WHERE author_id='$id'");
		$STH->execute();
		$row = $STH->fetchAll();
		for ($i=0; $i < count($row); $i++) {
			$ban_id = $row[$i]['id'];
			$pdo->exec("DELETE FROM complaints__comments WHERE complaint_id='$ban_id'");
		}
		$pdo->exec("DELETE FROM complaints WHERE author_id='$id'");

		$STH = $pdo->query("SELECT id FROM complaints WHERE author_id='$id' OR accused_profile_id='$id'");
		$STH->execute();
		$row = $STH->fetchAll();
		for ($i=0; $i < count($row); $i++) {
			$complaint_id = $row[$i]['id'];
			$pdo->exec("DELETE FROM complaints__comments WHERE complaint_id='$complaint_id'");
		}
		$pdo->exec("DELETE FROM complaints WHERE author_id='$id'");
		$pdo->exec("DELETE FROM complaints WHERE accused_profile_id='$id'");

		$STH = $pdo->query("SELECT id FROM tickets WHERE author='$id'");
		$STH->execute();
		$row = $STH->fetchAll();
		for ($i=0; $i < count($row); $i++) { 
			$ticket = $row[$i]['id'];
			$pdo->exec("DELETE FROM tickets__answers WHERE ticket='$ticket'");
		}

		$pdo->exec("DELETE FROM tickets__answers WHERE author='$id'");
		$pdo->exec("DELETE FROM tickets WHERE author='$id'");
		$pdo->exec("DELETE FROM last_actions WHERE user_id='$id'");
		$pdo->exec("DELETE FROM users__online WHERE user_id='$id'");
		$pdo->exec("DELETE FROM notifications WHERE user_id='$id'");
		// $pdo->exec("DELETE FROM money__actions WHERE author='$id'");
		$pdo->exec("DELETE FROM thanks WHERE author='$id'");

		if(check_table('sortition__participants', $pdo)) {
			$pdo->exec("DELETE FROM sortition__participants WHERE user_id='$id'");
		}
		if(check_table('cases__wins', $pdo)) {
			$pdo->exec("DELETE FROM cases__wins WHERE user_id='$id'");
		}
		if(check_table('activity_rewards__participants', $pdo)) {
			$pdo->exec("DELETE FROM activity_rewards__participants WHERE user_id='$id'");
		}
	}
	if($type == 1 || $type == 2 || $type == 5) {
		$pdo->exec("DELETE FROM complaints__comments WHERE user_id='$id'");
		$pdo->exec("DELETE FROM bans__comments WHERE user_id='$id'");
		$pdo->exec("DELETE FROM users__comments WHERE author='$id'");
		$pdo->exec("DELETE FROM news__comments WHERE user_id='$id'");
	}
	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['dell_multi_account_relation']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'],"int");
	$id_second = check($_POST['id_second'],"int");

	if(empty($id) || empty($id_second)) {
		exit();
	}

	$U = new Users();

	$STH = $pdo->prepare("SELECT `id`, `multi_account` FROM `users` WHERE `id`=:id OR `id`=:id_second LIMIT 2"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id, ':id_second' => $id_second ));
	while($row = $STH->fetch()) { 
		if($row->id == $id) {
			$STH2 = $pdo->prepare("UPDATE `users` SET `multi_account`=:multi_account WHERE `id`=:id LIMIT 1");
			$STH2->execute(array( ':multi_account' => $U->dell_multi_account_relation($id_second, $row->multi_account), ':id' => $row->id ));
		} else {
			$STH2 = $pdo->prepare("UPDATE `users` SET `multi_account`=:multi_account WHERE `id`=:id LIMIT 1");
			$STH2->execute(array( ':multi_account' => $U->dell_multi_account_relation($id, $row->multi_account), ':id' => $row->id ));
		}
	}

	exit();
}

if (isset($_POST['change_value']) and (is_worthy("f") or is_admin())) {
	$table = check($_POST['table'],null);
	$attr = check($_POST['attr'],null);
	$id = check($_POST['id'],"int");
	$value = check($_POST['value'], null);

	if($table != 'users') {
		exit();
	}
	if (empty($attr)) {
		exit();
	}
	if (empty($value)) {
		$value = '';
	}
	if(check_for_php($_POST['value'])) {
		exit();
	}

	$STH = $pdo->query("SELECT `admins_ids` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	$admins = explode(",", $row->admins_ids);
	for ($i=0; $i < count($admins); $i++) {
		$admins[$i] = trim($admins[$i]);
	}
	if((in_array($id, $admins) && $id == $_SESSION['id']) || (!in_array($id, $admins))) {
		$STH = $pdo->prepare("UPDATE `$table` SET `$attr`=:value WHERE `id`='$id' LIMIT 1");
		$STH->execute(array(':value' => $value));
	}
	exit();
}
if (isset($_POST['admin_change_avatar']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'],"int");
	if (empty($id)) {
		exit('<div class=pd-15>Ошибка: [Пустые переменные]</div>');
	}

	if (empty($_FILES['user_avatar']['name'])) {
		exit('<p class="text-danger">Выберите изображение!</p>');
	} else {
		$path = 'files/avatars/';

		if (if_img($_FILES['user_avatar']['name'])) {
			$filename = set_temp_file_name($_FILES['user_avatar']['name']);
			$source = $_FILES['user_avatar']['tmp_name'];
			$target = '../'.$path . $filename;
			move_uploaded_file($source, $target);

			if (if_gif($filename)) {
				$im = imagecreatefromgif('../'.$path . $filename);
			}
			if (if_png($filename)) {
				$im = imagecreatefrompng('../'.$path . $filename);
			}
			if (if_jpg($filename)) {
				$im = imagecreatefromjpeg('../'.$path . $filename);
			}

			$date = time();
			clip_image($im, 300, $path.$date);
			$user_avatar = $path . $date . ".jpg";
			unlink('../'.$path . $filename);

			$STH = $pdo->query("SELECT avatar FROM users WHERE id='$id'"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$tmp = $STH->fetch(); 
			if ($tmp->avatar != 'files/avatars/no_avatar.jpg') {
				unlink('../'.$tmp->avatar);
			}
		} else {
			exit('<p class="text-danger">Аватар должен быть в формате JPG,GIF или PNG</p>');
		}
		$STH = $pdo->prepare("UPDATE users SET avatar=:user_avatar WHERE id='$id' LIMIT 1");
		$STH->execute(array(':user_avatar' => $user_avatar));
		echo '<p class="text-success">Аватар изменен!</p><script> document.getElementById("avatar").src = "../'.$user_avatar.'" </script>';
	}
	exit();
}
if (isset($_POST['admin_change_vk']) and (is_worthy("f") or is_admin())) {
	$user_vk = check($_POST['user_vk'],null);
	$id = check($_POST['id'], "int");

	if (empty($user_vk)) {
		$user_vk = 0;
		$user_vk_api = 0;
	} else {
		if (empty($id)) {
			exit('Не указан id пользователя');
		}

		if (mb_strlen($user_vk, 'UTF-8') > 30) {
			exit('ID Вконтакте должен состоять не более чем из 30.');
		}

		if ((strnatcasecmp(substr($user_vk, 0, 7),'http://') == 0) or (strnatcasecmp(substr($user_vk, 0, 8),'https://') == 0) or (strnatcasecmp(substr($user_vk, 0, 6),'vk.com') == 0) or (strnatcasecmp(substr($user_vk, 0, 4),'www.') == 0)){
			exit('Укажите только ID!');
		}

		$user_vk_api = substr($user_vk, 2);
		$user_vk_api = check($user_vk_api, "int");
		if (empty($user_vk_api) or (substr($user_vk, 0, 2) != 'id')) {
			exit('Укажите ID в формате: id12345678!');
		}

		$answer = get_headers("https://vk.com/".$user_vk);
		if ($answer[0] == 'HTTP/1.1 404 Not Found') {
			exit('Аккаунт не найден.');
		}
	}

	$STH = $pdo->prepare("UPDATE users SET vk=:user_vk, vk_api=:user_vk_api WHERE id='$id' LIMIT 1");
	$STH->execute(array(':user_vk' => $user_vk, ':user_vk_api' => $user_vk_api)); 
	exit();
}
if (isset($_POST['admin_change_fb']) and (is_worthy("f") or is_admin())) {
	$user_fb = check($_POST['user_fb'], "int");
	$id = check($_POST['id'], "int");

	if (empty($user_fb)) {
		$user_fb = 0;
	} else {
		if(!empty($_POST['user_fb']) && empty($id)) {
			exit('ID должен состоять из цифр');
		}

		if(empty($id)) {
			exit('Не указан id пользователя');
		}

		if (mb_strlen($user_fb, 'UTF-8') < 2 or mb_strlen($user_fb, 'UTF-8') > 20) {
			exit('ID facebook должен состоять не менее чем из 2 символов и не более чем из 20.');
		}

		if ((strnatcasecmp(substr($user_fb, 0, 7),'http://') == 0) or (strnatcasecmp(substr($user_fb, 0, 8),'https://') == 0) or (strnatcasecmp(substr($user_fb, 0, 6),'fb.com') == 0) or (strnatcasecmp(substr($user_fb, 0, 4),'www.') == 0)){
			exit('Укажите только ID!');
		}

		$answer = get_headers("https://www.facebook.com/profile.php?id=".$user_fb);
		if ($answer[0] == 'HTTP/1.1 404 Not Found') {
			exit('Аккаунт Facebook не найден.');
		}
	}

	$STH = $pdo->prepare("UPDATE users SET fb=:user_fb WHERE id='$id' LIMIT 1");
	$STH->execute(array(':user_fb' => $user_fb)); 
	exit();
}
if (isset($_POST['admin_change_group']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");
	$rights = check($_POST['group'], null);

	if (empty($rights) or empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT `admins_ids` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	$admins = explode(",", $row->admins_ids);
	for ($i=0; $i < count($admins); $i++) {
		$admins[$i] = trim($admins[$i]);
	}

	if(!is_admin()) {
		if(in_array($id, $admins) || (!is_worthy('n') && is_worthy('n', $rights))) {
			exit(json_encode(['status' => '2']));
		}
	}

	$STH = $pdo->prepare("UPDATE `users` SET `rights`=:rights WHERE `id`='$id' LIMIT 1");
	$STH->execute(array(':rights' => $rights));

	$STH = $pdo->prepare("SELECT `id` FROM `admins` WHERE `user_id`=:user_id ");
	$STH->execute(array(':user_id' => $id));
	$row = $STH->fetchAll();
	for ($i = 0; $i < count($row); $i++) {
		$STH = $pdo->prepare("UPDATE `admins__services` SET `previous_group`=:previous_group WHERE `admin_id`=:admin_id LIMIT 1");
		$STH->execute(array( ':previous_group' => 0, ':admin_id' => $row[$i]['id'] ));
	}

	incNotifications();
	$noty = change_group_noty($users_groups[$rights]['name'], $users_groups[$rights]['rights']);
	send_noty($pdo, $noty, $id, 1);

	write_log("Смена группы пользователю с ID:".$id." на ".$users_groups[$rights]['name']);

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['admin_change_login']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");
	$login = check($_POST['user_login'], null);

	if (empty($login) or empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$U = new Users($pdo);

	if(!$U->check_login_length($login)) {
		exit(json_encode(array('status' => '2', 'data' => '<p class="text-danger">Логин должен состоять не менее чем из 3 символов и не более чем из 30.</p>')));
	}
	if(!$U->check_login_composition($login)) {
		exit(json_encode(array('status' => '2', 'data' => '<p class="text-danger">В логине разрешается использовать только буквы и цифры.</p>')));
	}
	if(!$U->check_login_busyness($login)) {
		exit(json_encode(array('status' => '2', 'data' => '<p class="text-danger">Введеный логин уже зарегистрирован!</p>')));
	}

	$STH = $pdo->prepare("UPDATE users SET login=:login WHERE id='$id' LIMIT 1");
	$STH->execute(array(':login' => $login));

	$STH = $pdo->prepare("SELECT `email` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$row = $STH->fetch();

	incNotifications();
	$letter = letter_of_change_login($conf->name, $login);
	sendmail($row->email, $letter['subject'], $letter['message'], $pdo);

	write_log("Смена логина пользователю с ID:".$id." на ".$login);

	exit(json_encode(array('status' => '1')));
}
if(isset($_POST['editUserRouteByAdmin']) and (is_worthy("f") or is_admin())) {
	$id    = check($_POST['id'], "int");
	$route = check($_POST['route'], null);

	if(empty($id)) {
		exit(json_encode(['status' => 2]));
	}

	if(empty($route)) {
		$route = null;
	} else {
		$U = new Users($pdo);

		if(!$U->check_route_length($route)) {
			exit(json_encode(['status' => 2, 'data' => '<p class="text-danger">Адрес должен состоять не менее чем из 3 символов и не более чем из 32.</p>']));
		}

		if(!$U->check_route_composition($route)) {
			exit(json_encode(['status' => 2, 'data' => '<p class="text-danger">В адресе разрешается использовать только буквы английского алфавита, цифры и символы: -_</p>']));
		}

		if(!$U->check_route_busyness($route)) {
			exit(json_encode(['status' => 2, 'data' => '<p class="text-danger">Введеный Вами адрес уже зарегистрирован!</p>']));
		}
	}

	$STH = $pdo->prepare("UPDATE users SET route=:route WHERE id = :id LIMIT 1");
	$STH->execute([':route' => $route, ':id' => $id]);

	write_log("Смена адреса страницы пользователю с ID:" . $id . " на " . $route);

	exit(json_encode(['status' => 1]));
}
if(isset($_POST['admin_change_password']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");
	$password = check($_POST['user_password'], null);

	if (empty($password) or empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$U = new Users($pdo);

	if(!$U->check_password_length($password)) {
		exit(json_encode(array('status' => '2', 'data' => '<p class="text-danger">Пароль должен состоять не менее чем из 6 символов и не более чем из 15</p>')));
	}

	$user_password = $U->convert_password($password, $conf->salt);
	$STH = $pdo->prepare("UPDATE users SET password=:password WHERE id='$id' LIMIT 1");
	$STH->execute(array(':password' => $user_password));

	$STH = $pdo->prepare("SELECT `email` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$row = $STH->fetch();

	incNotifications();
	$letter = letter_of_change_password($conf->name, $password);
	sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
	
	write_log("Смена пароля пользователю с ID:".$id);

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['admin_change_nick']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");
	$user_nick = check($_POST['user_nick'],null);
	$user_nick = str_replace(array('/', '\\'), '', $user_nick);

	if (empty($id)) {
		exit(json_encode(array('status' => '2', 'data' => 'Не указан id пользователя')));
	}

	if (empty($user_nick)) {
		exit(json_encode(array('status' => '2', 'data' => 'Вы не указали ник')));
	}

	if (mb_strlen($_POST['user_nick'], 'UTF-8') > 32) {
		exit(json_encode(array('status' => '2', 'data' => 'Ник должен состоять не более чем из 32 символов')));
	}

	$STH = $pdo->query("SELECT id,login FROM users WHERE nick='$user_nick' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (!empty($row->id)) {
		exit(json_encode(array('status' => '2', 'data' => 'Введеный Вами ник занят пользователем '.$row->login)));
	}

	$STH = $pdo->prepare("UPDATE users SET nick=:user_nick WHERE id='$id' LIMIT 1");
	$STH->execute(array(':user_nick' => $user_nick));

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['admin_change_signature']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");

	$signature = HTMLPurifier()->purify($_POST['signature']);
	$signature = find_img_mp3($signature, $id, 1);

	if (mb_strlen($signature, 'UTF-8') > 5000) {
		exit('Слишком длинный контент.');
	}
 
	$STH = $pdo->prepare("UPDATE users SET signature=:signature WHERE id='$id' LIMIT 1");
	$STH->execute(array(':signature' => $signature));

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['admin_change_birth']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");
	$birth_day = check($_POST['birth_day'],"int");
	$birth_month = check($_POST['birth_month'],"int");
	$birth_year = check($_POST['birth_year'],"int");

	if (empty($id)) {
		exit(json_encode(array('status' => '2', 'data' => 'Не указан id пользователя')));
	}

	if ($birth_day < 10) {
		$birth_day = "0" . $birth_day;
	}
	if ($birth_month < 10) {
		$birth_month = "0" . $birth_month;
	}

	$birth = $birth_year."-".$birth_month."-".$birth_day;

	if(!if_date($birth_day, $birth_month, $birth_year)) {
		exit(json_encode(array('status' => '2', 'data' => 'Введена некорректная дата!')));
	}
 
	$STH = $pdo->prepare("UPDATE users SET birth=:birth WHERE id='$id' LIMIT 1");
	$STH->execute(array(':birth' => $birth));

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['admin_activate_user']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");

	if (empty($id)) {
		exit(json_encode(array('status' => '2', 'data' => 'Не указан id пользователя')));
	}

	$STH = $pdo->prepare("SELECT `id`, `rights`, `password`, `login`, `protect`, `active`, `multi_account`, `invited` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$user = $STH->fetch();
	if(empty($user->id)) {
		exit(json_encode(array('status' => '2', 'data' => 'Пользователь не найден')));
	} else {
		if($user->active == 1) {
			exit(json_encode(array('status' => '2', 'data' => 'Пользователь уже активирован')));
		} else {
			$STH = $pdo->prepare("UPDATE users SET active=:active WHERE id='$id' LIMIT 1");  
			$STH->execute(array( 'active' => '1' ));

			$ES = new EventsRibbon($pdo);
			$ES->new_user($user->id, $user->login);

			if($user->invited != 0) {
				$noty = new_referal($user->id, $user->login);
				send_noty($pdo, $noty, $user->invited, 2);
			}
		}
	}

	exit(json_encode(array('status' => '1')));
}

/* Операции с админками
=========================================*/
if (isset($_POST['load_servers_admins']) and (is_worthy("j") or is_admin())){
	$server = check($_POST['id'],"int");
	if (empty($server)) {
		exit();
	}

	if(!is_worthy_specifically("j", $server) && !is_admin()) {
		exit();
	}

	$STH = $pdo->prepare("SELECT `id`,`name`,`type` FROM `servers` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $server ));
	$server = $STH->fetch();

	$i=0;

	if(isset($_POST['actions_panel']) && $_POST['actions_panel'] == 1 && is_admin()) {
		$conf->template = 'admin';
	}

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	
	if ($server->type == 0){
		$tpl->result['admins'] = '<tr><td colspan="10">Сервер не подключен к источникам информации.</td></tr>';
	} else {
		$STH = $pdo->prepare("SELECT `admins`.`id`, `admins`.`comment`, `admins`.`name`, `admins`.`pause`, `admins`.`active`, `admins`.`user_id`, `users`.`login`, `users`.`avatar` FROM `admins`
							  LEFT JOIN users ON `users`.`id` = `admins`.`user_id`
							  WHERE `admins`.`server`=:server"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':server' => $server->id ));
		while($row = $STH->fetch()) {
			$i++;
			$STH2 = $pdo->prepare("SELECT `services`.`name`, `services`.`show_adm`, `admins__services`.`ending_date` FROM `admins__services` LEFT JOIN `services` ON `admins__services`.`service` = `services`.`id` WHERE `admins__services`.`admin_id`=:admin_id LIMIT 10"); $STH2->setFetchMode(PDO::FETCH_OBJ);
			$STH2->execute(array( ':admin_id' => $row->id ));
			$services = '';
			$j = 0;
			while($row2 = $STH2->fetch()) {
				if(empty($row2->name)) {
					$row2->name = 'Неизвестно';
				}
				if($services == '') {
					$services .= $row2->name;
				} else {
					$services .= ' + '.$row2->name;
				}
				$ending_date = $row2->ending_date;
				$j++;
			}
			if($j == 1) {
				if($ending_date == '0000-00-00 00:00:00') {
					$ending_date = 'Никогда';
				} else {
					$ending_date = expand_date($ending_date, 1);
				}
			} else {
				$ending_date = '<a class="c-p" onclick="get_admin_info('.$row->id.');" data-target="#admin_modal'.$row->id.'" data-toggle="modal">Подробнее</a>';
			}

			$tpl->load_template('elements/editing_admins_admin.tpl');
			$tpl->set("{i}", $i);
			$tpl->set("{comment}", $row->comment);
			$tpl->set("{id}", $row->id);
			$tpl->set("{active}", $row->active);
			$tpl->set("{pause}", $row->pause);
			$tpl->set("{user_id}", $row->user_id);
			$tpl->set("{login}", $row->login);
			$tpl->set("{avatar}", $row->avatar);
			$tpl->set("{name}", $row->name);
			$tpl->set("{services}", $services);
			$tpl->set("{ending_date}", $ending_date);
			$tpl->compile( 'admins' );
			$tpl->clear();
		}
		if($i == 0){
			$tpl->result['admins'] = '<tr><td colspan="10">Администраторов нет</td></tr>';
		}
	}
	$tpl->load_template('elements/editing_admins_server.tpl');
	$tpl->set("{name}", $server->name);
	$tpl->set("{admins}", $tpl->result['admins']);
	$tpl->compile( 'servers' );
	$tpl->clear();
	$tpl->show($tpl->result['servers']);
	$tpl->global_clear();
	exit();
}
if(isset($_POST['load_edit_admin_result']) and (is_worthy("j") or is_admin())) {
	$id = check($_POST['id'],"int");
	if (empty($id)) {
		exit();
	}

	$STH = $pdo->prepare("SELECT `admins`.`name`, `admins`.`server`, `admins`.`user_id`, `users`.`login`, `users`.`avatar` FROM `admins`
						  LEFT JOIN users ON `users`.`id` = `admins`.`user_id`
						  WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$row = $STH->fetch();

	if(!is_worthy_specifically("j", $row->server) && !is_admin()) {
		exit();
	}

	$STH = $pdo->prepare("SELECT `services`.`name` FROM `admins__services` LEFT JOIN `services` ON `admins__services`.`service` = `services`.`id` WHERE `admins__services`.`admin_id`=:admin_id LIMIT 10"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':admin_id' => $id ));
	$services = '';
	while($row2 = $STH->fetch()) { 
		if(empty($row2->name)) {
			$row2->name = 'Неизвестно';
		}
		if($services == '') {
			$services .= $row2->name;
		} else {
			$services .= ' + '.$row2->name;
		}
	}

	$name = $row->name;
	$user = '<a target="_blank" href="../profile?id='.$row->user_id.'"><img src="../'.$row->avatar.'" alt="'.$row->login.'"> '.$row->login.'</a>';
	exit(json_encode(array('name' => $name, 'user' => $user, 'services' => $services)));
}
if(isset($_POST['get_admin_info']) and (is_worthy("j") or is_admin())) {
	$id = check($_POST['id'],"int");
	if (empty($id)) {
		exit();
	}

	$STH = $pdo->prepare("SELECT `admins`.*, `users`.`login`, `servers`.`binds`, `servers`.`type` AS `server_type`, `users`.`avatar` FROM `admins`
							  LEFT JOIN users ON `users`.`id` = `admins`.`user_id`
							  LEFT JOIN servers ON `servers`.`id` = `admins`.`server`
							  WHERE `admins`.`id`=:id"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$admin = $STH->fetch();

	if(!is_worthy_specifically("j", $admin->server) && !is_admin()) {
		exit();
	}

	if (empty($admin->pass) and empty($admin->pass_md5)) {
		$admin->pass = '';
	} elseif(empty($admin->pass_md5)) {
		$admin->pass = $admin->pass;
	} elseif(empty($admin->pass)) {
		$admin->pass = '';
	}

	if ($admin->active == 1) { 
		$disp1 = "";
		$disp2 = "disp-n";
		$class = "";
	} else {
		$disp1 = "disp-n";
		$disp2 = "";
		$class = "danger";
	}
	if ($admin->pause == 0) {
		$class2 = "";
	} else {
		$class2 = "warning";
	}

	if(isset($_POST['actions_panel']) && $_POST['actions_panel'] == 1 && is_admin()) {
		$conf->template = 'admin';
	}

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$j = 0;
	$binds = explode(';', $admin->binds);
	$STH = $pdo->prepare("SELECT `admins__services`.`id`, `admins__services`.`rights_und`, `admins__services`.`sb_group_und`, 
		`admins__services`.`immunity_und`, `services`.`sb_group`, `services`.`immunity`, `services`.`name`, `admins__services`.`service`, 
		`admins__services`.`bought_date`, `admins__services`.`ending_date`, `services`.`rights` 
		FROM `admins__services` LEFT JOIN `services` ON `admins__services`.`service` = `services`.`id` WHERE `admins__services`.`admin_id` = :admin_id"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':admin_id' => $id ));
	while($row = $STH->fetch()) { 
		$j++;
		$immunity = '';
		if(!empty($row->service)) {
			$name = $row->name;
			if($row->rights_und != 'none') {
				$rights = $row->rights_und;
			} else {
				$rights = $row->rights;
			}
			if($admin->server_type == 4) {
				$sb_group = $row->sb_group;
				$immunity = $row->immunity;
			}
		} else {
			$name = 'Неизвестно';
			$rights = $row->rights_und;
			if($admin->server_type == 4) {
				$sb_group = $row->sb_group_und;
				$immunity = $row->immunity_und;
			}
		}
		$groups = '';
		if(!empty($sb_group) AND !empty($rights)) {
			$rights = $rights;
			$groups = '+'.$sb_group;
		} else {
			if(!empty($rights)) {
				$rights = $rights;
			}
			if(!empty($sb_group)) {
				$groups = $sb_group;
			}
		}

		if($row->ending_date == '0000-00-00 00:00:00') {
			$left = "Вечность";
			$color = "success";
			$ending_date = '00.00.0000 00:00';
		} else {
			if ($admin->pause != 0) {
				$row->ending_date = date("Y-m-d H:i:s", strtotime($row->ending_date) + time() - $admin->pause);
			}
			$left = strtotime($row->ending_date)-time();
			if($left>60*60*24*5) {
				$color = "success";
			} elseif($left>60*60*24) {
				$color = "warning";
			} else {
				$color = "danger";
			}
			$left = expand_seconds2($left, 2);
			$ending_date = date( 'd.m.Y H:i', strtotime($row->ending_date));
		}

		$tpl->load_template('elements/editing_admins_store.tpl');
		$tpl->set("{j}", $j);
		$tpl->set("{id}", $row->id);
		$tpl->set("{admin_id}", $admin->id);
		$tpl->set("{name}", $name);
		$tpl->set("{rights}", $rights);
		$tpl->set("{active}", $admin->active);
		$tpl->set("{groups}", $groups);
		$tpl->set("{server_type}", $admin->server_type);
		$tpl->set("{immunity}", $immunity);
		$tpl->set("{bought_date}", $row->bought_date);
		$tpl->set("{bought_date_full}", expand_date($row->bought_date, 1));
		$tpl->set("{ending_date}", $ending_date);
		$tpl->set("{pause}", $admin->pause);
		$tpl->set("{color}", $color);
		$tpl->set("{left}", $left);
		$tpl->compile( 'stores' );
	}

	$tpl->load_template('elements/editing_admins_admin_full.tpl');
	$tpl->set("{id}", $admin->id);
	$tpl->set("{active}", $admin->active);
	$tpl->set("{user_id}", $admin->user_id);
	$tpl->set("{type}", $admin->type);
	$tpl->set("{pause}", $admin->pause);
	$tpl->set("{name}", $admin->name);
	$tpl->set("{pass}", $admin->pass);
	$tpl->set("{comment}", $admin->comment);
	$tpl->set("{server_type}", $admin->server_type);
	$tpl->set("{binds_0}", $binds[0]);
	$tpl->set("{binds_1}", $binds[1]);
	$tpl->set("{binds_2}", $binds[2]);
	$tpl->set("{class}", $class);
	$tpl->set("{class_2}", $class2);
	$tpl->set("{disp}", $disp1);
	$tpl->set("{disp_2}", $disp2);
	$tpl->set("{stores}", $tpl->result['stores']);
	$tpl->compile( 'admin_full' );
	$tpl->clear();
	$tpl->show($tpl->result['admin_full']);
	$tpl->global_clear();	
	exit();
}
if (isset($_POST['pause_admin']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit (json_encode(array('status' => '2', 'data' => 'Пустой ID!')));
	}

	$STH = $pdo->prepare("SELECT `admins`.*,`servers`.`db_host`, `servers`.`ip`, `servers`.`port`, `servers`.`id` AS `server_id`, `servers`.`db_code`, `servers`.`type` AS `server_type`, `servers`.`name` AS `server_name`, `servers`.`db_user`, `servers`.`db_pass`, `servers`.`db_db`, `servers`.`db_prefix` FROM `servers` 
		LEFT JOIN `admins` ON `admins`.`server` = `servers`.`id` 
		WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$info = $STH->fetch();
	if (empty($info->id)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("j", $info->server_id) && !is_admin()) {
		exit(json_encode(array('status' => '2', 'data' => 'Недостаточно прав')));
	}

	if($info->active == 2) {
		exit (json_encode(array('status' => '2', 'data' => 'Данный администратор выключен, его приостановка невозможна!')));
	}

	if($info->pause != 0) {
		exit (json_encode(array('status' => '2', 'data' => 'Данный уже администратор приостановлен!')));
	}

	if (empty($info->server_type)){
		exit (json_encode(array('status' => '2', 'data' => 'Невозможно подключение к FTP и DB серверу')));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $info->server_id)) {
		exit (json_encode(array('status' => '2', 'data' => $messages['server_connect_error'])));
	}

	$STH = $pdo->prepare("UPDATE admins SET pause=:pause WHERE id='$id' LIMIT 1");
	$STH->execute(array( 'pause' => time() ));

	if ($info->server_type == 1 || $info->server_type == 3){
		if(!$AM->export_to_users_ini($pdo, $info->server_id, 'PAUSE_ADMIN')){
			exit (json_encode(array('status' => '2', 'data' => 'Не удалось экспортировать администраторов в файл')));
		}
	}
	if ($info->server_type == 2 || $info->server_type == 4){
		if(!$pdo2 = db_connect($info->db_host, $info->db_db, $info->db_user, $info->db_pass)) {
			exit (json_encode(array('status' => '2', 'data' => 'Не удалось подключиться к DB серверу')));
		}
		set_names($pdo2, $info->db_code);

		$info->name = htmlspecialchars_decode($info->name, ENT_QUOTES);

		if ($info->server_type == 2) {
			if(!$admin_id = $AM->get_admin_id($info->name, 1, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
				exit (json_encode(array('status' => '2', 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "serverinfo");
			$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':address' => $info->ip.':'.$info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers");
			$pdo2->exec("DELETE FROM $table WHERE admin_id='$admin_id' and server_id='$row->id' LIMIT 1");
		} else {
			if(!$admin_id = $AM->get_admin_id($info->name, 2, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
				exit (json_encode(array('status' => '2', 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "servers");
			$STH = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':ip' => $info->ip, ':port' => $info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers_groups");
			$pdo2->exec("DELETE FROM $table WHERE admin_id='$admin_id' and server_id='$row->sid' LIMIT 1");
		}
	}

	if(isset($info->user_id)) {
		incNotifications();
		$noty = pause_service_noty(clean($info->name, null), $info->server_name);
		send_noty($pdo, $noty, $info->user_id, 1);
	}

	try {
		(new OurSourceQuery())->reloadAdmins($info->server_id);
	} catch(Exception $e) {
		log_error($e->getMessage());
	}
	
	service_log("Управление админами: Приостановка прав", $id, $info->server_id, $pdo);
	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['resume_admin']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit (json_encode(array('status' => '2', 'data' => 'Пустой ID!')));
	}

	$STH = $pdo->prepare("SELECT `admins`.*,`servers`.`db_host`, `servers`.`ip`, `servers`.`port`, `servers`.`id` AS `server_id`, `servers`.`db_code`, `servers`.`type` AS `server_type`, `servers`.`name` AS `server_name`, `servers`.`db_user`, `servers`.`db_pass`, `servers`.`db_db`, `servers`.`db_prefix` FROM `servers` 
		LEFT JOIN `admins` ON `admins`.`server` = `servers`.`id` 
		WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$info = $STH->fetch();
	if (empty($info->id)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("j", $info->server_id) && !is_admin()) {
		exit(json_encode(array('status' => '2', 'data' => 'Недостаточно прав')));
	}	

	if($info->active == 2) {
		exit (json_encode(array('status' => '2', 'data' => 'Данный администратор выключен, его активация невозможна!')));
	}
	if($info->pause == 0) {
		exit (json_encode(array('status' => '2', 'data' => 'Данный администратор не приостановлен!')));
	}

	if (empty($info->server_type)){
		exit (json_encode(array('status' => '2', 'data' => 'Невозможно подключение к FTP и DB серверу')));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $info->server_id)) {
		exit (json_encode(array('status' => '2', 'data' => $messages['server_connect_error'])));
	}

	$STH = $pdo->prepare("UPDATE admins SET pause=:pause WHERE id='$id' LIMIT 1");
	$STH->execute(array( 'pause' => 0 ));

	$time = time() - $info->pause;

	$STH = $pdo->prepare("SELECT `ending_date`, `id` FROM `admins__services` WHERE `admin_id`=:admin_id"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':admin_id' => $id ));
	while($row = $STH->fetch()) { 
		if($row->ending_date != 0) {
			$ending_date = date("Y-m-d H:i:s", strtotime($row->ending_date) + $time);
			$STH2 = $pdo->prepare("UPDATE `admins__services` SET `ending_date`=:ending_date WHERE `id`=:id AND `ending_date`=:old_ending_date LIMIT 1");
			$STH2->execute(array( ':ending_date' => $ending_date, ':id' => $row->id, ':old_ending_date' => $row->ending_date ));
		}
	}

	if ($info->server_type == 1 || $info->server_type == 3){
		if(!$AM->export_to_users_ini($pdo, $info->server_id, 'RESUME_ADMIN')){
			exit (json_encode(array('status' => '2', 'data' => 'Не удалось экспортировать администраторов в файл')));
		}
	}
	if ($info->server_type == 2 || $info->server_type == 4){
		if(!$pdo2 = db_connect($info->db_host, $info->db_db, $info->db_user, $info->db_pass)) {
			exit (json_encode(array('status' => '2', 'data' => 'Не удалось подключиться к DB серверу')));
		}
		set_names($pdo2, $info->db_code);

		$info->name = htmlspecialchars_decode($info->name, ENT_QUOTES);
		if(!empty($info->pass)) {
			$info->pass = htmlspecialchars_decode($info->pass, ENT_QUOTES);
		}

		if ($info->server_type == 2) {
			if(!$admin_id = $AM->get_admin_id2($info->id, $info->name, $info->pass, $info->pass_md5, $info->server_id, 1, $pdo, $pdo2, $info->db_prefix)) {
				exit (json_encode(array('status' => '2', 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "serverinfo");
			$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':address' => $info->ip.':'.$info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers");
			$STH = $pdo2->prepare("INSERT INTO $table (admin_id,server_id,use_static_bantime,custom_flags) values (:admin_id, :server_id, :use_static_bantime, :custom_flags)");
			$STH->execute(array( 'admin_id' => $admin_id, 'server_id' => $row->id, 'use_static_bantime' => 'no', 'custom_flags' => '' ));
		} else {
			if(!$admin_id = $AM->get_admin_id2($info->id, $info->name, $info->pass, $info->pass_md5, $info->server_id, 2, $pdo, $pdo2, $info->db_prefix)) {
				exit (json_encode(array('status' => '2', 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "servers");
			$STH = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':ip' => $info->ip, ':port' => $info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers_groups");
			$STH = $pdo2->prepare("INSERT INTO $table (admin_id,server_id,group_id,srv_group_id) values (:admin_id, :server_id, :group_id, :srv_group_id)");
			$STH->execute(array( 'admin_id' => $admin_id, 'server_id' => $row->sid, 'group_id' => '0', 'srv_group_id' => '-1' ));
		}
	}

	if(isset($info->user_id)) {
		incNotifications();
		$noty = resume_service_noty(clean($info->name, null), $info->server_name);
		send_noty($pdo, $noty, $info->user_id, 2);
	}

	try {
		(new OurSourceQuery())->reloadAdmins($info->server_id);
	} catch(Exception $e) {
		log_error($e->getMessage());
	}

	service_log("Управление админами: Запуск прав", $id, $info->server_id, $pdo);
	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['edit_admin']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	$type = checkJs($_POST['type'],null);
	$param = checkJs($_POST['param'],null);

	if($type == 'comment') {
		if (empty($id) or empty($type)) {
			exit (json_encode(array('status' => '2', 'reply' => 'Заполните все поля!')));
		}
	} else {
		if (empty($id) or empty($type) or empty($param)) {
			exit (json_encode(array('status' => '2', 'reply' => 'Заполните все поля!')));
		}
	}

	$STH = $pdo->prepare("SELECT `admins`.`id`, `admins`.`active`, `admins`.`pause`, `admins`.`type`, `admins`.`name`, `admins`.`pass`, `admins`.`pass_md5`, `servers`.`type` AS `server_type`, `servers`.`id` AS `server_id` FROM `admins` 
		LEFT JOIN `servers` ON `servers`.`id` = `admins`.`server`
		WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$admin = $STH->fetch();
	if(empty($admin->id)) {
		exit (json_encode(array('status' => '2', 'reply' => 'Ошибка!')));
	}

	if(!is_worthy_specifically("j", $admin->server_id) && !is_admin()) {
		exit(json_encode(array('status' => '2', 'reply' => 'Недостаточно прав')));
	}	

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $admin->server_id)) {
		exit (json_encode(array('status' => '2', 'reply' => $messages['server_connect_error'])));
	}

	$SIDO = new SteamIDOperations();

	if($admin->active == 2) {
		exit(json_encode(array('status' => '2', 'reply' => 'Услуга заблокирована!')));
	}

	if($admin->pause != 0) {
		exit (json_encode(array('status' => '2', 'reply' => 'Услуга приостановлена!')));
	}

	$old_name = null;
	if ($type == 'user_id'){
		$STH = $pdo->prepare("SELECT `id`, `nick` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $param ));
		$row = $STH->fetch();
		if(empty($row->id)) {
			exit (json_encode(array('status' => '2', 'reply' => 'Пользователь не существует')));
		}
		if ($admin->server_type == 4) {
			if ($row->nick == '---' or empty($row->nick)) {
				exit (json_encode(array('status' => '2', 'reply' => 'Заполните поле "ник" в профиле пользователя')));
			}
		}

		$STH = $pdo->prepare("UPDATE `admins` SET `user_id`=:user_id WHERE `id`=:id LIMIT 1");
		$STH->execute(array( ':user_id' => $param, ':id' => $id ));
	}
	if ($type == 'comment'){
		if(empty($param)) {
			$param = '';
		}
		if (mb_strlen(htmlspecialchars_decode($param, ENT_QUOTES), 'UTF-8') > 1000) {
			exit (json_encode(array('status' => '2', 'reply' => 'Комментарий не может содержать более 1000 символов!')));
		}
		$STH = $pdo->prepare("UPDATE `admins` SET `comment`=:comment WHERE `id`=:id LIMIT 1");
		$STH->execute(array( ':comment' => $param, ':id' => $id ));
	}
	if ($type == 'type'){
		if($param == 1) {
			if(empty($admin->pass) and empty($admin->pass_md5)) {
				exit (json_encode(array('status' => '2', 'reply' => 'Сначала укажите пароль!')));
			}

			$STH = $pdo->prepare("UPDATE `admins` SET `type`=:type WHERE `id`=:id LIMIT 1");
			$STH->execute(array( ':type' => 'a', ':id' => $id ));
		}
		if($param == 2) {
			if (!$admin->name = $SIDO->GetSteamID32($admin->name)) {
				exit (json_encode(array('status' => '2', 'reply' => 'Введите корректный STEAM ID!')));
			}

			$STH = $pdo->prepare("UPDATE `admins` SET `type`=:type WHERE `id`=:id LIMIT 1");
			$STH->execute(array( ':type' => 'ce', ':id' => $id ));
		}
		if($param == 3) {
			if(empty($admin->pass) and empty($admin->pass_md5)) {
				exit (json_encode(array('status' => '2', 'reply' => 'Сначала укажите пароль!')));
			}

			if (!$admin->name = $SIDO->GetSteamID32($admin->name)) {
				exit (json_encode(array('status' => '2', 'reply' => 'Введите корректный STEAM ID!')));
			}

			$STH = $pdo->prepare("UPDATE `admins` SET `type`=:type WHERE `id`=:id LIMIT 1");
			$STH->execute(array( ':type' => 'ca', ':id' => $id ));
		}
	}
	if ($type == 'name'){
		if ($admin->server_type == 1 || $admin->server_type == 3) {
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '"') !== FALSE) {
				exit (json_encode(array('status' => '2', 'reply' => 'Идентификатор содержит запрещенный символ: "')));
			}
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '#') !== FALSE) {
				exit (json_encode(array('status' => '2', 'reply' => 'Идентификатор содержит запрещенный символ: #')));
			}
		}

		if ($admin->type == 'a'){
			if (mb_strlen(htmlspecialchars_decode($param, ENT_QUOTES), 'UTF-8') > 32) {
				exit (json_encode(array('status' => '2', 'reply' => 'Не более 32 символов!')));
			}
		}
		if ($admin->type == 'ce' or $admin->type == 'ca'){
			if (mb_strlen($param, 'UTF-8') > 32) {
				exit (json_encode(array('status' => '2', 'reply' => 'Не более 32 символов!')));
			}
			if (!$param = $SIDO->GetSteamID32($param)) {
				exit (json_encode(array('status' => '2', 'reply' => 'Введите корректный STEAM ID!')));
			}
		}

		$STH = $pdo->prepare("SELECT `id` FROM `admins` WHERE `name`=:name AND `server`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':name' => $param, ':server' => $admin->server_id ));
		$row = $STH->fetch();
		if(isset($row->id)) {
			if($row->id == $id) {
				exit (json_encode(array('status' => '1')));
			} else {
				exit (json_encode(array('status' => '2', 'reply' => 'Идентификатор уже используется другим игроком!')));
			}
		}
		$STH = $pdo->prepare("UPDATE `admins` SET `name`=:name WHERE `id`=:id LIMIT 1");
		$STH->execute(array( ':name' => $param, ':id' => $id ));

		$old_name = $admin->name;
	}
	if ($type == 'pass'){
		if ($admin->server_type == 1 || $admin->server_type == 3) {
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '"') !== FALSE) {
				exit (json_encode(array('status' => '2', 'reply' => 'Ваш пароль содержит запрещенный символ: "')));
			}
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '#') !== FALSE) {
				exit (json_encode(array('status' => '2', 'reply' => 'Ваш пароль содержит запрещенный символ: #')));
			}
		}

		if (mb_strlen($param, 'UTF-8') > 32) {
			exit (json_encode(array('status' => '2', 'reply' => 'Не более 32 символов!')));
		}

		$STH = $pdo->prepare("UPDATE `admins` SET `pass`=:pass, `pass_md5`=:pass_md5 WHERE `id`=:id LIMIT 1");
		$STH->execute(array( ':pass' => $param, ':pass_md5' => md5($param), ':id' => $id ));
	}

	if ($admin->server_type == 1 || $admin->server_type == 3){
		if(!$AM->export_to_users_ini($pdo, $admin->server_id, 'EDIT_ADMIN')){
			exit (json_encode(array('status' => '2', 'reply' => 'Не удалось экспортировать администраторов в файл')));
		}
	} else {
		if(!$AM->export_admin($pdo, $id, $admin->server_id, 'EDIT_ADMIN', $old_name)){
			exit (json_encode(array('status' => '2', 'reply' => 'Не удалось экспортировать администратора в базу данных сервера')));
		}
	}

	service_log("Управление админами: Смена ".$type." на ".$param, $id, $admin->server_id, $pdo);
	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['stop_adm']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	$cause = checkJs($_POST['cause'],null);
	$link = checkJs($_POST['link'],null);
	$price = checkJs($_POST['price'],"int");

	if (empty($id) or empty($cause) or empty($price)) {
		exit(json_encode(array('status' => 2)));
	}

	if (empty($link)) {
		$link = '';
	}

	$STH = $pdo->prepare("SELECT `admins`.*,`servers`.`db_host`, `servers`.`ip`, `servers`.`port`, `servers`.`id` AS `server_id`, `servers`.`db_code`, `servers`.`type` AS `server_type`, `servers`.`name` AS `server_name`, `servers`.`db_user`, `servers`.`db_pass`, `servers`.`db_db`, `servers`.`db_prefix` FROM `servers` 
		LEFT JOIN `admins` ON `admins`.`server` = `servers`.`id` 
		WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$info = $STH->fetch();
	if (empty($info->id)){
		exit(json_encode(array('status' => 2)));
	}

	if(!is_worthy_specifically("j", $info->server_id) && !is_admin()) {
		exit(json_encode(array('status' => 2, 'data' => 'Недостаточно прав')));
	}	

	if ($info->active == 2) {
		exit (json_encode(array('status' => 2, 'data' => 'Администратор уже выключен')));
	}
	if ($info->pause != 0) {
		exit (json_encode(array('status' => 2, 'data' => 'Администратор приостановлен, отключение невозможно')));
	}

	if (empty($info->server_type)){
		exit (json_encode(array('status' => 2, 'data' => 'Невозможно подключение к FTP и DB серверу')));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $info->server_id)) {
		exit (json_encode(array('status' => 2, 'data' => $messages['server_connect_error'])));
	}

	$STH = $pdo->prepare("UPDATE admins SET active=:active,cause=:cause,link=:link,price=:price WHERE id='$id' LIMIT 1");
	$STH->execute(array( 'active' => 2, 'cause' => $cause, 'link' => $link, 'price' => $price ));

	if ($info->server_type == 1 || $info->server_type == 3){
		if(!$AM->export_to_users_ini($pdo, $info->server_id, 'STOP_ADMIN')){
			exit (json_encode(array('status' => 2, 'data' => 'Не удалось экспортировать администраторов в файл')));
		}
	}
	if($info->server_type == 2 || $info->server_type == 4) {
		if(!$pdo2 = db_connect($info->db_host, $info->db_db, $info->db_user, $info->db_pass)) {
			exit (json_encode(array('status' => 2, 'data' => 'Не удалось подключиться к DB серверу')));
		}
		set_names($pdo2, $info->db_code);

		$info->name = htmlspecialchars_decode($info->name, ENT_QUOTES);

		if ($info->server_type == 2) {
			if(!$admin_id = $AM->get_admin_id($info->name, 1, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
				exit (json_encode(array('status' => 2, 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "serverinfo");
			$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':address' => $info->ip.':'.$info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers");
			$pdo2->exec("DELETE FROM $table WHERE admin_id='$admin_id' and server_id='$row->id' LIMIT 1");
		} else {
			if(!$admin_id = $AM->get_admin_id($info->name, 2, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
				exit (json_encode(array('status' => 2, 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "servers");
			$STH = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':ip' => $info->ip, ':port' => $info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers_groups");
			$pdo2->exec("DELETE FROM $table WHERE admin_id='$admin_id' and server_id='$row->sid' LIMIT 1");
		}
	}

	if(isset($info->user_id)) {
		incNotifications();
		$noty = block_service_noty(clean($info->name, null), $info->server_name, $cause, $price, $link);
		send_noty($pdo, $noty, $info->user_id, 3);
	}

	try {
		(new OurSourceQuery())->reloadAdmins($info->server_id);
	} catch(Exception $e) {
		log_error($e->getMessage());
	}

	service_log("Управление админами: Выключение прав", $id, $info->server_id, $pdo);
	exit (json_encode(array('status' => 1)));
}
if (isset($_POST['start_adm']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("SELECT `admins`.*,`servers`.`db_host`, `servers`.`ip`, `servers`.`port`, `servers`.`db_code`, `servers`.`type` AS `server_type`, `servers`.`id` AS `server_id`, `servers`.`name` AS `server_name`, `servers`.`db_user`, `servers`.`db_pass`, `servers`.`db_db`, `servers`.`db_prefix` FROM `servers` 
		LEFT JOIN `admins` ON `admins`.`server` = `servers`.`id` 
		WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$info = $STH->fetch();
	if (empty($info->id)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("j", $info->server_id) && !is_admin()) {
		exit(json_encode(array('status' => '2', 'data' => 'Недостаточно прав')));
	}	

	if ($info->active == 1) {
		exit (json_encode(array('status' => '2', 'data' => 'Администратор уже включен')));
	}
	if ($info->pause != 0) {
		exit (json_encode(array('status' => '2', 'data' => 'Администратор приостановлен, включение невозможно')));
	}

	if (empty($info->server_type)){
		exit (json_encode(array('status' => '2', 'data' => 'Невозможно подключение к FTP и DB серверу')));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $info->server_id)) {
		exit (json_encode(array('status' => '2', 'data' => $messages['server_connect_error'])));
	}

	$STH = $pdo->prepare("UPDATE admins SET active=:active,cause=:cause,link=:link,price=:price WHERE id='$id' LIMIT 1");
	$STH->execute(array( 'active' => '1', 'cause' => '', 'link' => '', 'price' => 0 ));

	if ($info->server_type == 1 || $info->server_type == 3){
		if(!$AM->export_to_users_ini($pdo, $info->server_id, 'START_ADMIN')){
			exit (json_encode(array('status' => '2', 'data' => 'Не удалось экспортировать администраторов в файл')));
		}
	}
	if($info->server_type == 2 || $info->server_type == 4) {
		if(!$pdo2 = db_connect($info->db_host, $info->db_db, $info->db_user, $info->db_pass)) {
			exit (json_encode(array('status' => '2', 'data' => 'Не удалось подключиться к DB серверу')));
		}
		set_names($pdo2, $info->db_code);

		$info->name = htmlspecialchars_decode($info->name, ENT_QUOTES);
		if(!empty($info->pass)) {
			$info->pass = htmlspecialchars_decode($info->pass, ENT_QUOTES);
		}

		if ($info->server_type == 2) {
			if(!$admin_id = $AM->get_admin_id2($info->id, $info->name, $info->pass, $info->pass_md5, $info->server_id, 1, $pdo, $pdo2, $info->db_prefix)) {
				exit (json_encode(array('status' => '2', 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "serverinfo");
			$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':address' => $info->ip.':'.$info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers");
			$STH = $pdo2->prepare("INSERT INTO $table (admin_id,server_id,use_static_bantime,custom_flags) values (:admin_id, :server_id, :use_static_bantime, :custom_flags)");
			$STH->execute(array( 'admin_id' => $admin_id, 'server_id' => $row->id, 'use_static_bantime' => 'no', 'custom_flags' => '' ));
		} else {
			if(!$admin_id = $AM->get_admin_id2($info->id, $info->name, $info->pass, $info->pass_md5, $info->server_id, 2, $pdo, $pdo2, $info->db_prefix)) {
				exit (json_encode(array('status' => '2', 'data' => 'Не найден ID админа')));
			}

			$table = set_prefix($info->db_prefix, "servers");
			$STH = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':ip' => $info->ip, ':port' => $info->port ));
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers_groups");
			$STH = $pdo2->prepare("INSERT INTO $table (admin_id,server_id,group_id,srv_group_id) values (:admin_id, :server_id, :group_id, :srv_group_id)");
			$STH->execute(array( 'admin_id' => $admin_id, 'server_id' => $row->sid, 'group_id' => '0', 'srv_group_id' => '-1' ));
		}
	}

	if(isset($info->user_id)) {
		incNotifications();
		$noty = unlock_service_noty(clean($info->name, null), $info->server_name);
		send_noty($pdo, $noty, $info->user_id, 2);
	}

	try {
		(new OurSourceQuery())->reloadAdmins($info->server_id);
	} catch(Exception $e) {
		log_error($e->getMessage());
	}
	
	service_log("Управление админами: Включение прав", $id, $info->server_id, $pdo);
	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['add_admin']) and (is_worthy("j") or is_admin())) {
	$user_id = checkJs($_POST['user_id'],"int");
	$server = checkJs($_POST['server'],"int");
	$service = checkJs($_POST['service'],"int");
	$tarif = checkJs($_POST['tarifs'],"int");
	$type = checkJs($_POST['type'],"int");
	$nick = checkJs($_POST['nick'],null);
	$pass = checkJs($_POST['pass'],null);
	$steam_id = checkJs($_POST['steam_id'],null);
	$check1 = checkJs($_POST['check1'],"int");
	$check2 = checkJs($_POST['check2'],"int");

	if (empty($check1)){
		$check1 = 0;
	}
	if (empty($check2)){
		$check2 = 0;
	}

	if (empty($server) or empty($service) or empty($tarif) or empty($type) or empty($user_id)) {
		exit (json_encode(array('status' => '3', 'data' => 'Заполните все поля!')));
	}

	if(!is_worthy_specifically("j", $server) && !is_admin()) {
		exit (json_encode(array('status' => '3', 'data' => 'Недостаточно прав')));
	}	

	$admin['user_id'] = $user_id;

	$STH = $pdo->query("SELECT id,type,ip,port,name,pass_prifix,discount,binds FROM servers WHERE id='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$server = $STH->fetch();
	if (empty($server->id)){
		exit (json_encode(array('status' => '3', 'data' => 'Данного сервера не существует')));
	}
	if (empty($server->type)){
		exit (json_encode(array('status' => '3', 'data' => 'Невозможно подключение к FTP и DB серверу')));
	}
	$server->address = $server->ip.':'.$server->port;

	$binds = explode(';', $server->binds);

	if ($type != '1' and $type != '2' and $type != '3') {
		exit (json_encode(array('status' => '3', 'data' => 'Неверно указан тип!')));
	}

	if ( ($binds[0] == 0 and $type == 1) || ($binds[1] == 0 and $type == 2) || ($binds[2] == 0 and $type == 3) ) {
		exit (json_encode(array('status' => '3', 'data' => 'Данный тип запрещен!')));
	}

	$SIDO = new SteamIDOperations();

	if ($type == '1'){
		$admin['type'] = 'a';
		if (empty($nick)) {
			exit (json_encode(array('status' => '2', 'input' => 'player_nick', 'reply' => 'Заполните!')));
		}
		if (mb_strlen(htmlspecialchars_decode($nick, ENT_QUOTES), 'UTF-8') > 32) {
			exit (json_encode(array('status' => '2', 'input' => 'player_nick', 'reply' => 'Не более 32 символов!')));
		}
		if (empty($pass)) {
			exit (json_encode(array('status' => '2', 'input' => 'player_pass', 'reply' => 'Заполните!')));
		}
		if (mb_strlen($pass, 'UTF-8') > 32) {
			exit (json_encode(array('status' => '2', 'input' => 'player_pass', 'reply' => 'Не более 32 символов!')));
		}
		$admin['name'] = $nick;
		$admin['pass'] = $pass;
		$admin['pass_md5'] = md5($pass);
	}
	if ($type == '2'){
		$admin['type'] = 'ce';
		if (empty($steam_id)) {
			exit (json_encode(array('status' => '2', 'input' => 'player_steam_id', 'reply' => 'Заполните!')));
		}
		if (mb_strlen($steam_id, 'UTF-8') > 32) {
			exit (json_encode(array('status' => '2', 'input' => 'player_steam_id', 'reply' => 'Не более 32 символов!')));
		}
		if (!$steam_id = $SIDO->GetSteamID32($steam_id)) {
			exit (json_encode(array('status' => '2', 'input' => 'player_steam_id', 'reply' => 'Неверный STEAM ID!')));
		}
		$admin['name'] = $steam_id;
		$admin['pass'] = '';
		$admin['pass_md5'] = '';
	}
	if ($type == '3'){
		$admin['type'] = 'ca';
		if (empty($steam_id)) {
			exit (json_encode(array('status' => '2', 'input' => 'player_steam_id', 'reply' => 'Заполните!')));
		}
		if (mb_strlen($steam_id, 'UTF-8') > 32) {
			exit (json_encode(array('status' => '2', 'input' => 'player_steam_id', 'reply' => 'Не более 32 символов!')));
		}
		if (!$steam_id = $SIDO->GetSteamID32($steam_id)) {
			exit (json_encode(array('status' => '2', 'input' => 'player_steam_id', 'reply' => 'Неверный STEAM ID!')));
		}
		if (empty($pass)) {
			exit (json_encode(array('status' => '2', 'input' => 'player_pass', 'reply' => 'Заполните!')));
		}
		if (mb_strlen($pass, 'UTF-8') > 32) {
			exit (json_encode(array('status' => '2', 'input' => 'player_pass', 'reply' => 'Не более 32 символов!')));
		}
		$admin['name'] = $steam_id;
		$admin['pass'] = $pass;
		$admin['pass_md5'] = md5($pass);
	}

	$STH = $pdo->prepare("SELECT `id`,`user_id`,`active`,`pause` FROM `admins` WHERE `name`=:name AND `server`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':name' => $admin['name'], ':server' => $server->id ));
	$row = $STH->fetch();
	if (isset($row->id)){
		if ($row->user_id != $admin['user_id']){
			exit (json_encode(array('status' => '3', 'data' => 'На сервере уже имеется администратор с такими данными!')));
		} else {
			if($check1 == 0) {
				exit (json_encode(array('status' => '4')));
			}
		}

		if($row->active == 2){
			exit (json_encode(array('status' => '3', 'data' => 'Данный аккаунт заблокирован!')));
		}
		if($row->pause != 0) {
			exit (json_encode(array('status' => '3', 'data' => 'Данный аккаунт приостановлен!')));
		}

		$admin['id'] = $row->id;
		$admin['has_rights'] = 1;
	} else {
		$admin['has_rights'] = 0;
	}

	$STH = $pdo->query("SELECT * FROM services WHERE id='$service' and server='$server->id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$service = $STH->fetch();
	if (empty($service->id)){
		exit (json_encode(array('status' => '3', 'data' => 'Услуга не найдена')));
	}
	if($service->users_group != 0) {
		if($check2 == 0) {
			$STH = $pdo->prepare("SELECT `name` FROM `users__groups` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':id' => $service->users_group ));
			$row = $STH->fetch();

			exit (json_encode(array('status' => '5', 'group' => $row->name)));
		}
	}

	if(isset($admin['id'])) {
		$STH = $pdo->prepare("SELECT `id` FROM `admins__services` WHERE `service`=:service and `admin_id`=:admin_id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':service' => $service->id, ':admin_id' => $admin['id'] ));
		$row = $STH->fetch();
		if(isset($row->id)) {
			exit (json_encode(array('status' => '3', 'data' => 'У пользователя уже имеется данная услуга.')));
		}
	}

	$STH = $pdo->query("SELECT id,price,time,discount FROM services__tarifs WHERE id='$tarif' and service='$service->id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$tarif = $STH->fetch();
	if (empty($tarif->id)){
		exit (json_encode(array('status' => '3', 'data' => 'Тариф не найден')));
	}

	$STH = $pdo->query("SELECT id,login,nick,email,proc,rights FROM users WHERE id='$admin[user_id]' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$user = $STH->fetch();
	if (empty($user->id)){
		exit (json_encode(array('status' => '3', 'data' => 'Пользователь не найден')));
	}

	$STH = $pdo->query("SELECT discount FROM config__prices LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$proc = calculate_discount($server->discount, $row->discount, $user->proc, $service->discount, $tarif->discount);
	$price = calculate_price($tarif->price, $proc);
	$admin['irretrievable'] = 0;

	if ($server->type == 4) {
		if ($user->nick == '---' or empty($user->nick)) {
			exit (json_encode(array('status' => '3', 'data' => 'Заполните в <a href="edit_user?id='.$admin['user_id'].'" target="_blank">профиле пользователя</a> поле ник')));
		} else {
			$admin['nick_for_sb'] = $user->nick;
		}

		if($admin['has_rights'] == 1) {
			if($service->sb_group != '') {
				$STH = $pdo->query("SELECT `admins__services`.`id` FROM `admins__services` LEFT JOIN `services` ON `admins__services`.`service` = `services`.`id` WHERE `services`.`sb_group`!='' AND `admins__services`.`admin_id` = '$admin[id]' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
				if(isset($row->id)) {
					exit (json_encode(array('status' => '3', 'data' => 'Данные услуги объединить невозможно!')));
				}
			}
		}
	}

	if ($server->type == 1 || $server->type == 3) {
		if(stristr(htmlspecialchars_decode($admin['name'], ENT_QUOTES), '"') !== FALSE) {
			exit (json_encode(array('status' => '3', 'data' => 'Ваш идентификатор содержит запрещенный символ: "')));
		}
		if(stristr(htmlspecialchars_decode($admin['name'], ENT_QUOTES), '#') !== FALSE) {
			exit (json_encode(array('status' => '3', 'data' => 'Ваш идентификатор содержит запрещенный символ: #')));
		}

		if(stristr(htmlspecialchars_decode($admin['pass'], ENT_QUOTES), '"') !== FALSE) {
			exit (json_encode(array('status' => '3', 'data' => 'Ваш пароль содержит запрещенный символ: "')));
		}
		if(stristr(htmlspecialchars_decode($admin['pass'], ENT_QUOTES), '#') !== FALSE) {
			exit (json_encode(array('status' => '3', 'data' => 'Ваш пароль содержит запрещенный символ: #')));
		}
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $server->id)) {
		exit (json_encode(array('status' => '3', 'data' => $messages['server_connect_error'])));
	}

	$admin['ending_date'] = $AM->get_ending_date($tarif->time);
	$admin['bought_date'] = date("Y-m-d H:i:s");
	$admin['service_time'] = $tarif->id;
	$admin['service'] = $service->id;

	if($admin['has_rights'] == 1) {
		$STH = $pdo->prepare("UPDATE `admins` SET `name`=:name, `pass`=:pass, `pass_md5`=:pass_md5, `type`=:type WHERE `id`=:id LIMIT 1");
		if (!$STH->execute(array( 'name' => $admin['name'], 'pass' => $admin['pass'], 'pass_md5' => $admin['pass_md5'], 'type' => $admin['type'], 'id' => $admin['id'] )) == '1') {
			exit (json_encode(array('status' => '3', 'data' => 'Ошибка записи админа в базу данных.')));
		}
	} else {
		$STH = $pdo->prepare("INSERT INTO admins (name,pass,pass_md5,type,server,user_id) values (:name, :pass, :pass_md5, :type, :server, :user_id)");
		if (!$STH->execute(array( 'name' => $admin['name'], 'pass' => $admin['pass'], 'pass_md5' => $admin['pass_md5'], 'type' => $admin['type'], 'server' => $server->id, 'user_id' => $admin['user_id'] )) == '1') {
			exit (json_encode(array('status' => '3', 'data' => 'Ошибка записи админа в базу данных.')));
		}
	}

	if(empty($admin['id'])) {
		$STH = $pdo->prepare("SELECT `id` FROM `admins` WHERE `name`=:name and `server`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':name' => $admin['name'], ':server' => $server->id ));
		$row = $STH->fetch();
		$admin['id'] = $row->id;
	}

	if($service->users_group != 0 and $check2 == 1) {
		$STH = $pdo->prepare("SELECT `admins__services`.`previous_group` FROM `admins__services` 
			LEFT JOIN `admins` ON `admins`.`id` = `admins__services`.`admin_id` WHERE `admins`.`user_id`=:user_id AND `admins__services`.`previous_group`!='0' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':user_id' => $admin['user_id'] ));
		$row = $STH->fetch();

		if(isset($row->previous_group)) {
			$admin['previous_group'] = $row->previous_group;
		} else {
			$admin['previous_group'] = $user->rights;
		}
	} else {
		$admin['previous_group'] = 0;
	}

	$STH = $pdo->prepare("INSERT INTO `admins__services` (`admin_id`,`service`,`service_time`,`bought_date`,`ending_date`,`irretrievable`,`previous_group`) values (:admin_id, :service, :service_time, :bought_date, :ending_date, :irretrievable, :previous_group)");  
	if (!$STH->execute(array( ':admin_id' => $admin['id'], ':service' => $admin['service'], ':service_time' => $admin['service_time'], ':bought_date' => $admin['bought_date'], ':ending_date' => $admin['ending_date'], ':irretrievable' => $admin['irretrievable'], ':previous_group' => $admin['previous_group'] )) == '1') {
		exit (json_encode(array('status' => '3', 'data' => 'Ошибка записи прав в базу данных.')));
	}

	if ($server->type == 1 || $server->type == 3){
		if(!$AM->export_to_users_ini($pdo, $server->id, 'ADD_ADMIN')){
			exit (json_encode(array('status' => '3', 'data' => 'Не удалось экспортировать администраторов в файл')));
		}
	} else {
		if(!$AM->export_admin($pdo, $admin['id'], $server->id, 'ADD_ADMIN')){
			exit (json_encode(array('status' => '3', 'data' => 'Не удалось экспортировать администратора в базу данных сервера')));
		}
	}

	if($service->users_group != 0 and $check2 == 1) {
		$STH = $pdo->prepare("UPDATE `users` SET `rights`=:rights WHERE `id`=:id LIMIT 1");
		$STH->execute(array( ':rights' => $service->users_group, ':id' => $admin['user_id'] ));
	}

	incNotifications();
	$noty = give_service_noty($admin['name'], $admin['pass'], $tarif->time, $admin['ending_date'], $server->pass_prifix);
	send_noty($pdo, $noty, $admin['user_id'], 2);

	$noty = give_service_noty_for_admin($user->id, $user->login, $admin['name'], $tarif->time, $admin['ending_date'], $server->name, $server->address, $service->name);
	send_noty($pdo, $noty, 0, 2);

	service_log("Управление админами: Выданы права", $admin['id'], $server->id, $pdo, $service->id);
	exit (json_encode(array('status' => '1', 'data' => 'Услуга успешно выдана!')));
}
if (isset($_POST['dell_admin']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("SELECT `services`.`users_group`, `admins`.`server`, `admins`.`user_id` FROM `admins` 
		LEFT JOIN `admins__services` ON `admins`.`id` = `admins__services`.`admin_id` 
		LEFT JOIN `services` ON `services`.`id` = `admins__services`.`service` 
		WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$row = $STH->fetch();

	if(!is_worthy_specifically("j", $row->server) && !is_admin()) {
		exit (json_encode(array('status' => '2')));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $row->server)) {
		exit (json_encode(array('status' => '2')));
	}

	$AM->set_admin_group($pdo, $row->user_id, $id, 0);
	service_log("Полное удаление прав", $id, $row->server, $pdo);

	if(!$AM->dell_admin_full($pdo, $id, "DELL_ADMIN")) {
		exit(json_encode(array('status' => '2')));
	}

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['dell_admin_service']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare(
		"SELECT 
						services.users_group,
    					services.id AS service_id,
						admins__services.admin_id,
						servers.type AS server_type, 
						admins.server, 
						admins.user_id 
					FROM admins__services 
						LEFT JOIN admins ON admins.id = admins__services.admin_id 
						LEFT JOIN servers ON servers.id = admins.server
						LEFT JOIN services ON services.id = admins__services.service 
						WHERE admins__services.id=:id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$admin = $STH->fetch();
	if(empty($admin->admin_id)) {
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("j", $admin->server) && !is_admin()) {
		exit (json_encode(array('status' => '2')));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $admin->server)) {
		exit (json_encode(array('status' => '2')));
	}

	$AM->set_admin_group($pdo, $admin->user_id, 0, $id);
	service_log("Управление админами: Удаление услуги", $admin->admin_id, $admin->server, $pdo, $admin->service_id);

	$STH = $pdo->prepare("SELECT `id` FROM `admins__services` WHERE `admin_id`=:id ");
	$STH->execute(array( ':id' => $admin->admin_id ));
	$row = $STH->fetchAll();
	$count = count($row);

	if($count == 1) {
		if(!$AM->dell_admin_full($pdo, $admin->admin_id, "DELL_ADMIN_SERVICE")) {
			exit(json_encode(array('status' => '2')));
		}
		$dell = 1;
	} else {
		$STH = $pdo->prepare("DELETE FROM `admins__services` WHERE `id`=:id LIMIT 1");
		$STH->execute(array( ':id' => $id ));

		if ($admin->server_type == 1 || $admin->server_type == 3){
			if(!$AM->export_to_users_ini($pdo, $admin->server, 'DELL_ADMIN_SERVICE')){
				exit (json_encode(array('status' => '2')));
			}
		} else {
			if(!$AM->export_admin($pdo, $admin->admin_id, $admin->server, 'DELL_ADMIN_SERVICE')){
				exit (json_encode(array('status' => '2')));
			}
		}

		$dell = 0;
	}

	exit(json_encode(array('status' => '1', 'id' => $admin->admin_id, 'dell' => $dell)));
}
if (isset($_POST['get_services']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit();
	}

	if(!is_worthy_specifically("j", $id) && !is_admin()) {
		exit ();
	}

	$i = 0;
	$data = '';
	$STH = $pdo->query("SELECT id,name FROM services WHERE server = '$id' ORDER BY trim"); $STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if ($i == 0){
			$service = $row->id;
			$i++;
		}
		$data .= '<option value="'.$row->id.'">'.$row->name.'</option>';
	}
	exit(json_encode(array( 'status' => '1', 'data' => $data, 'service' => $service )));
}
if (isset($_POST['get_tarifs']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit();
	}

	$data = '';
	$STH = $pdo->query("SELECT id,time FROM services__tarifs WHERE service = '$id' ORDER BY time"); $STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) { 
		if ($row->time == 0){
			$time = 'Навсегда';
		} else {
			$time = $row->time.' дней';
		}

		$data .= '<option value="'.$row->id.'">'.$time.'</option>';
	}
	exit(json_encode(array( 'status' => '1', 'data' => $data )));
}
if (isset($_POST['change_admin_days']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	$date = checkJs($_POST['date'],null);

	if (empty($id) or empty($date)) {
		exit (json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare(
		"SELECT 
					    services.name AS service_name, 
					    services.id AS service_id, 
					    admins.id, admins.name, 
					    admins.user_id, 
					    servers.type AS server_type, 
					    servers.id AS server_id, 
					    servers.name AS server_name 
					FROM admins__services 
						LEFT JOIN admins ON admins__services.admin_id = admins.id 
						LEFT JOIN servers ON admins.server = servers.id
						LEFT JOIN services ON admins__services.service = services.id
						WHERE admins__services.id=:id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $id ));
	$admin = $STH->fetch();
	if(empty($admin->id)) {
		exit (json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("j", $admin->server_id) && !is_admin()) {
		exit (json_encode(array('status' => '2')));
	}

	$AM = new AdminsManager;
	if($date == '00.00.0000 00:00') {
		$date = '0000-00-00 00:00:00';
	} else {
		$date = date( 'Y-m-d H:i:s', strtotime($date));
	}

	$STH = $pdo->prepare("UPDATE `admins__services` SET `ending_date`=:ending_date WHERE `id`=:id LIMIT 1");
	$STH->execute(array( ':ending_date' => $date, ':id' => $id ));

	if(strtotime($conf->dell_admin_time) > strtotime($date)) {
		$STH = $pdo->prepare("UPDATE `config` SET `dell_admin_time`=:dell_admin_time LIMIT 1");
		$STH->execute(array( ':dell_admin_time' => $date ));
	}

	if ($admin->server_type == 1 || $admin->server_type == 3){
		if(!$AM->export_to_users_ini($pdo, $admin->server_id, 'CHANGE_ADMIN_DAYS')){
			exit (json_encode(array('status' => '2')));
		}
	} else {
		if(!$AM->export_admin($pdo, $admin->id, $admin->server_id, 'CHANGE_ADMIN_DAYS')){
			exit (json_encode(array('status' => '2')));
		}
	}

	if(!empty($admin->user_id)) {
		incNotifications();
		if($date == '0000-00-00 00:00:00') {
			$date = 'Никогда';
		} else {
			$date = expand_date($date, 1);
		}
		$noty = change_days_noty($admin->name, $admin->service_name, $admin->server_name, $date);
		send_noty($pdo, $noty['message'], $admin->user_id, $noty['type']);
	}

	service_log(
		"Управление админами: Установлен срок окончания прав: " . $date,
		$admin->id,
		$admin->server_id,
		$pdo,
		$admin->service_id
	);

	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['change_admin_flags']) and (is_worthy("j") or is_admin())) {
	$id = checkJs($_POST['id'],"int");
	$flags = checkJs($_POST['flags'],null);

	if (empty($id) or empty($flags)) {
		exit (json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare(
		"SELECT 
					    services.rights AS service_rights, 
					    services.id AS service_id, 
					    admins.id, 
					    admins.name, 
					    admins.user_id, 
					    servers.type AS server_type, 
					    servers.id AS server_id, 
					    servers.name AS server_name 
					FROM admins__services 
						LEFT JOIN admins ON admins__services.admin_id = admins.id 
						LEFT JOIN servers ON admins.server = servers.id
						LEFT JOIN services ON admins__services.service = services.id
						WHERE admins__services.id=:id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $id ));
	$admin = $STH->fetch();
	if(empty($admin->id)) {
		exit (json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("j", $admin->server_id) && !is_admin()) {
		exit (json_encode(array('status' => '2')));
	}

	if($admin->service_rights == $flags) {
		$flags = 'none';
	}

	$AM = new AdminsManager;
	$STH = $pdo->prepare("UPDATE `admins__services` SET `rights_und`=:rights_und WHERE `id`=:id LIMIT 1");
	$STH->execute(array( ':rights_und' => $flags, ':id' => $id ));

	if ($admin->server_type == 1 || $admin->server_type == 3){
		if(!$AM->export_to_users_ini($pdo, $admin->server_id, 'CHANGE_ADMIN_FLAGS')){
			exit (json_encode(array('status' => '2')));
		}
	} else {
		if(!$AM->export_admin($pdo, $admin->id, $admin->server_id, 'CHANGE_ADMIN_FLAGS')){
			exit (json_encode(array('status' => '2')));
		}
	}

	service_log("Управление админами: Смена флагов у прав", $admin->id, $admin->server_id, $pdo, $admin->service_id);
	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['get_user_shilings_operations']) and (is_worthy("f") or is_admin())) {
	$id = check($_POST['id'], "int");
	if(empty($id)) {
		exit('<tr><td colspan="10"><span class="empty-element">Пусто</span></td></tr>');
	}

	$i=0;
	$tpl = new Template;
	if($_POST['type'] == 1) {
		$tpl->dir = '../templates/admin/tpl/';
	} else {
		$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	}

	$STH = $pdo->query("SELECT `money__actions`.*, `money__actions_types`.`name`, `money__actions_types`.`class` FROM `money__actions` 
		INNER JOIN `money__actions_types` ON `money__actions_types`.`id` = `money__actions`.`type`
		WHERE `money__actions`.`author`='$id' ORDER BY `money__actions`.`date` DESC");$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		$tpl->load_template('elements/money_action.tpl');
		$tpl->set("{shilings}", $row->shilings);
		$tpl->set("{type}", collect_consumption_str(2, $row->type, $row->class, $row->name, $pdo, $row->gave_out));
		$tpl->set("{date}", expand_date($row->date, 7));
		$tpl->compile( 'content' );
		$tpl->clear();
	}
	if ($i==0){
		exit('<tr><td colspan="10"><span class="empty-element">Пусто</span></td></tr>');
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}

if (isset($_POST['doRconCommand']) && (is_worthy("s") || is_worthy("v"))) {
	$commandId = clean($_POST['commandId'], "int");

	$ServerCommands = new ServerCommands();
	$command = $ServerCommands->getCommandById($commandId);
	$params = $ServerCommands->getCommandParams($commandId);

	if(
		(
			$ServerCommands->isCategoryIsActionOnPlayer($command->category)
			&& !is_worthy_specifically('s', $command->server_id)
		)
		|| (
			$ServerCommands->isCategoryIsServerManagement($command->category)
			&& !is_worthy_specifically('v', $command->server_id)
		)
	) {
		exit(json_encode(['status' => 2]));
	}

	$commandValue = $command->command . ' ';
	
	foreach($params as $param) {
		if(
			array_key_exists($param->name, $_POST)
		) {
			if(!ServerCommands::validateParam($_POST[$param->name])) {
				exit (json_encode(['status' => 2, 'data' => 'Неверное значение для «' . $param->title . '»']));
			} else {
				if(is_numeric($_POST[$param->name])) {
					$paramValue = $_POST[$param->name]. ' ';
				} else {
					$paramValue = '"' . $_POST[$param->name]. '" ';
				}
			}
		} else {
			$paramValue = '"" ';
		}

		$commandValue .= $paramValue;
	}

	$server = (new ServersManager())->getServer($command->server_id);
	$SourceQuery = (new OurSourceQuery)->setServer($server);

	if(!$SourceQuery->isServerCanWorkWithRcon()) {
		exit(json_encode(['status' => 2, 'data' => 'Отправка rcon команды невозможна']));
	}

	try {
		$answer = "Команда отправлена, ответ: "
			. $SourceQuery->checkConnect()->auth()->send($commandValue);
	} catch( Exception $e ) {
		$answer = "Ошибка: ". $e->getMessage();
	}

	$SourceQuery->Disconnect();

	exit(json_encode(['status' => 1, 'data' => clean($answer, null)]));
}

exit(json_encode(['status' => 2]));