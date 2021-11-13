<?php
include_once "../inc/start.php";

if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов actions_a.php"); 
	echo 'Ошибка: [Прямой вызов инклуда]';
	exit(json_encode(array('status' => '2')));
}

if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'],null))) {
	log_error("Неверный токен"); 
	echo 'Ошибка: [Неверный токен]';
	exit(json_encode(array('status' => '2')));
}

if (empty($_SESSION['id'])){
	echo 'Ошибка: [Доступно только авторизованным]';
	exit(json_encode(array('status' => '2')));
}

include_once "../inc/protect.php";

/* Проверка на бота
=========================================*/
if(isset($_POST['bot_check'])) {
	if(!validateCaptcha($_POST["captcha"])) {
		exit('<p class="text-danger">Неверно введена капча!</p>');
	}

	$STH = $pdo->prepare("DELETE FROM `last_actions` WHERE `user_id`=:user_id AND `action_type`=:action_type LIMIT 1");
	$STH->execute(array( ':user_id' => $_SESSION['id'], ':action_type' => 5 ));

	exit('<p class="text-success">Проверка пройдена, можете выполнить действие!</p><script>reset_page();</script>');
}

/* Настройка профиля 
=========================================*/
if (isset($_POST['edit_user_vk'])) {
	$user_vk = check($_POST['user_vk'],null);

	if (empty($user_vk)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали ID.');
	}

	if ((strnatcasecmp(substr($user_vk, 0, 7),'http://') == 0) or (strnatcasecmp(substr($user_vk, 0, 8),'https://') == 0) or (strnatcasecmp(substr($user_vk, 0, 6),'vk.com') == 0) or (strnatcasecmp(substr($user_vk, 0, 4),'www.') == 0)){
		exit('<span class="m-icon icon-remove"></span> Укажите только ID!');
	}

	if (mb_strlen($user_vk, 'UTF-8') < 2 or mb_strlen($user_vk, 'UTF-8') > 30) {
		exit('<span class="m-icon icon-remove"></span> ID Вконтакте должен состоять не менее чем из 2 символов и не более чем из 30.');
	}

	$answer = get_headers("https://vk.com/".$user_vk);
	if ($answer[0] == 'HTTP/1.1 404 Not Found') {
		exit('<span class="m-icon icon-remove"></span> Аккаунт Вконтакте не найден.');
	}

	$U = new Users($pdo);

	if(!$U->check_busyness('vk', $user_vk, $_SESSION['id'])) {
		exit('<span class="m-icon icon-remove"></span> Введеный Вами ID занят другим пользователем!');
	}

	$STH = $pdo->prepare("UPDATE users SET vk=:user_vk WHERE id='$_SESSION[id]' LIMIT 1");
	if ($STH->execute(array( ':user_vk' => $user_vk)) == '1') {
		exit('<span class="m-icon icon-ok"></span> ID вконтакте изменен!');
	}

	exit();
}
if (isset($_POST['unset_vk'])) {
	$STH = $pdo->prepare("SELECT `vk_api`,`password` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $_SESSION['id'] ));
	$row = $STH->fetch();
	if($row->vk_api == '0') {
		exit('<span class="m-icon icon-remove"></span> Профиль уже откреплен.');
	}
	if(substr($row->password, 0, 5) == "none_") {
		exit('<span class="m-icon icon-remove"></span> Сначала укажите пароль для своего профиля.');
	}

	$STH = $pdo->prepare("UPDATE `users` SET `vk_api`=:vk_api, `vk`=:vk WHERE `id`=:id LIMIT 1");
	if ($STH->execute(array( ':vk_api' => '0', ':vk' => '---', ':id' => $_SESSION['id'] )) == '1') {
		exit('<script>reset_page();</script>');
	}
}

if (isset($_POST['edit_user_steam_id'])) {
	$user_steam_id = check($_POST['user_steam_id'],null);

	if (empty($user_steam_id)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали STEAM ID!');
	}

	if (mb_strlen($user_steam_id, 'UTF-8') > 32) {
		exit('<span class="m-icon icon-remove"></span> STEAM ID должен состоять не более чем из 30 символов.');
	}

	$SIDO = new SteamIDOperations();
	if (!$user_steam_id = $SIDO->GetSteamID32($user_steam_id)) {
		exit('<span class="m-icon icon-remove"></span> Неверный STEAM ID!');
	}

	$STH = $pdo->query("SELECT `id`, `login` FROM users WHERE steam_id='$user_steam_id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (!empty($row->id) && $row->id != $_SESSION['id']) {
		exit('<p class="text-danger">Введеный Вами steam id занят пользователем <a href="../profile?id='.$row->id.'" target="_blank">'.$row->login.'</a></p>');
	}

	$STH = $pdo->prepare("UPDATE users SET steam_id=:user_steam_id WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_steam_id' => $user_steam_id));

	write_log("Steam ID изменен на ".$user_steam_id); 
	exit('<span class="m-icon icon-ok"></span> Ваш Steam ID изменен!');
}
if (isset($_POST['unset_steam'])) {
	$STH = $pdo->prepare("SELECT `steam_api`,`password` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $_SESSION['id'] ));
	$row = $STH->fetch();

	if($row->steam_api == '0') {
		exit('<span class="m-icon icon-remove"></span> Профиль уже откреплен.');
	}
	if(substr($row->password, 0, 5) == "none_") {
		exit('<span class="m-icon icon-remove"></span> Сначала укажите пароль для своего профиля.');
	}

	$STH = $pdo->prepare("UPDATE `users` SET `steam_api`=:steam_api WHERE `id`=:id LIMIT 1");
	if ($STH->execute(array( ':steam_api' => '0', ':id' => $_SESSION['id'] )) == '1') {
		exit('<script>reset_page();</script>');
	}
}

if (isset($_POST['edit_user_fb'])) {
	$user_fb = check($_POST['user_fb'],null);

	if (empty($user_fb)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали ID.');
	}

	if ((strnatcasecmp(substr($user_fb, 0, 7),'http://') == 0) or (strnatcasecmp(substr($user_fb, 0, 8),'https://') == 0) or (strnatcasecmp(substr($user_fb, 0, 6),'facebook.com') == 0) or (strnatcasecmp(substr($user_fb, 0, 4),'www.') == 0)){
		exit('<span class="m-icon icon-remove"></span> Укажите только ID!');
	}

	if (mb_strlen($user_fb, 'UTF-8') < 2 or mb_strlen($user_fb, 'UTF-8') > 20) {
		exit('<span class="m-icon icon-remove"></span> ID facebook должен состоять не менее чем из 2 символов и не более чем из 20.');
	}

	$answer = get_headers("https://www.facebook.com/profile.php?id=".$user_fb);
	if ($answer[0] == 'HTTP/1.1 404 Not Found') {
		exit('<span class="m-icon icon-remove"></span> Аккаунт Facebook не найден.');
	}

	$U = new Users($pdo);

	if(!$U->check_busyness('fb', $user_fb, $_SESSION['id'])) {
		exit('<span class="m-icon icon-remove"></span> Введеный Вами ID занят другим пользователем!');
	}

	$STH = $pdo->prepare("UPDATE users SET fb=:user_fb WHERE id='$_SESSION[id]' LIMIT 1");
	if ($STH->execute(array( ':user_fb' => $user_fb)) == '1') {
		exit('<span class="m-icon icon-ok"></span> ID Facebook изменен!');
	}

	exit();
}
if (isset($_POST['unset_fb'])) {
	$STH = $pdo->prepare("SELECT `fb_api`,`password` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $_SESSION['id'] ));
	$row = $STH->fetch();

	if($row->fb_api == '0') {
		exit('<span class="m-icon icon-remove"></span> Профиль уже откреплен.');
	}
	if(substr($row->password, 0, 5) == "none_") {
		exit('<span class="m-icon icon-remove"></span> Сначала укажите пароль для своего профиля.');
	}

	$STH = $pdo->prepare("UPDATE `users` SET `fb_api`=:fb_api WHERE `id`=:id LIMIT 1");
	if ($STH->execute(array( ':fb_api' => '0', ':id' => $_SESSION['id'] )) == '1') {
		exit('<script>reset_page();</script>');
	}
}

if (isset($_POST['edit_user_login'])) {
	$user_login = checkJs($_POST['user_login'],null);

	if (empty($user_login)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали логин!');
	}

	$U = new Users($pdo);

	if(!$U->check_login_length($user_login)) {
		exit('<span class="m-icon icon-remove"></span> Логин должен состоять не менее чем из 3 символов и не более чем из 30.');
	}
	if(!$U->check_login_composition($user_login)) {
		exit('<span class="m-icon icon-remove"></span> В логине разрешается использовать только буквы и цифры.');
	}
	if(!$U->check_login_busyness($user_login, $_SESSION['id'])) {
		exit('<span class="m-icon icon-remove"></span> Введеный Вами логин уже зарегистрирован!');
	}

	if ($host == 'demo.worksma.ru' and $_SESSION['id'] == '2') {
		exit('<span class="m-icon icon-remove"></span> Менять логин на данном аккаунте запрещено!');
	}

	$STH = $pdo->query("SELECT `col_login` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$conf2 = $STH->fetch();

	$date = time() - 24*60*60*$conf2->col_login;
	$pdo->exec("DELETE FROM `last_actions` WHERE `date`<'$date' and `user_id`='$_SESSION[id]' and `action_type` = '3' LIMIT 1");

	$STH = $pdo->query("SELECT `id`, `date` FROM `last_actions` WHERE `user_id` = '$_SESSION[id]' and `action_type` = '3'"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (!empty($row->id)) {
		$delta = time() - $row->date;
		if ($delta < (24*60*60*$conf2->col_login)) {
			exit('<span class="m-icon icon-remove"></span> Логин можно менять раз в '.$conf2->col_login.' сут.');
		}
	}

	$STH = $pdo->prepare("UPDATE `users` SET `login`=:user_login WHERE `id`='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_login' => $user_login));

	$STH = $pdo->prepare("INSERT INTO `last_actions` (`user_id`,`action_type`,`date`) values (:user_id, :action_type, :date)");
	$STH->execute(array( 'user_id' => $_SESSION['id'], 'action_type' => '3', 'date' => time() ));

	$_SESSION['login'] = $user_login;

	write_log("Логин изменен на ".$user_login); 
	exit('<span class="m-icon icon-ok"></span> Ваш логин изменен!');
}

if(isset($_POST['editUserRoute'])) {
	$route = check($_POST['route'], null);

	if(empty($route)) {
		$route = null;
	} else {
		$U = new Users($pdo);

		if(!$U->check_route_length($route)) {
			exit('<span class="m-icon icon-remove"></span> Адрес должен состоять не менее чем из 3 символов и не более чем из 32.');
		}

		if(!$U->check_route_composition($route)) {
			exit('<span class="m-icon icon-remove"></span> В адресе разрешается использовать только буквы английского алфавита, цифры и символы: -_');
		}

		if(!$U->check_route_busyness($route, $_SESSION['id'])) {
			exit('<span class="m-icon icon-remove"></span> Введеный Вами адрес уже зарегистрирован!');
		}
	}

	$STH = $pdo->prepare("UPDATE users SET route=:route WHERE id=:id LIMIT 1");
	$STH->execute([':route' => $route, ':id' => $_SESSION['id']]);

	write_log('Адрес страницы изменен на ' . $route);
	exit('<span class="m-icon icon-ok"></span> Адрес вашей страницы изменен!');
}

if (isset($_POST['edit_user_name'])) {
	$user_name = check($_POST['user_name'],null);

	if (empty($user_name)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали имя!');
	}

	if (mb_strlen($user_name, 'UTF-8') > 30) {
		exit('<span class="m-icon icon-remove"></span> Имя должно состоять не более чем из 30 символов.');
	}

	$STH = $pdo->prepare("UPDATE users SET name=:user_name WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_name' => $user_name));

	write_log("Имя изменено на ".$user_name); 
	exit('<span class="m-icon icon-ok"></span> Ваше имя изменено!');
}
if (isset($_POST['edit_user_nick'])) {
	$user_nick = check($_POST['user_nick'],null);
	$user_nick = str_replace(array('/', '\\'), '', $user_nick);

	if (empty($user_nick)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали ник!');
	}

	if (mb_strlen($_POST['user_nick'], 'UTF-8') > 32) {
		exit('<span class="m-icon icon-remove"></span> Ник должен состоять не более чем из 32 символов.');
	}

	$STH = $pdo->query("SELECT id,login FROM users WHERE nick='$user_nick' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (!empty($row->id) && $row->id != $_SESSION['id']) {
		exit('<p class="text-danger">Введеный Вами ник занят пользователем <a href="../profile?id='.$row->id.'" target="_blank">'.$row->login.'</a></p>');
	}

	$STH = $pdo->prepare("UPDATE users SET nick=:user_nick WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_nick' => $user_nick));

	write_log("Ник изменен на ".$user_nick); 
	exit('<span class="m-icon icon-ok"></span> Ваш ник изменен!');
}
if (isset($_POST['edit_user_birth'])) {
	$birth_day = check($_POST['birth_day'],"int");
	$birth_month = check($_POST['birth_month'],"int");
	$birth_year = check($_POST['birth_year'],"int");

	if ($birth_day < 10) {
		$birth_day = "0" . $birth_day;
	}
	if ($birth_month < 10) {
		$birth_month = "0" . $birth_month;
	}

	$birth = $birth_year."-".$birth_month."-".$birth_day;

	if(!if_date($birth_day, $birth_month, $birth_year)) {
		exit('<span class="m-icon icon-remove"></span> Введена некорректная дата!');
	}

	$STH = $pdo->prepare("UPDATE users SET birth=:birth WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':birth' => $birth));

	write_log("Дата рождения изменена на ".$birth); 
	exit('<span class="m-icon icon-ok"></span> Ваша дата рождения изменена!');
}
if (isset($_POST['edit_user_skype'])) {
	$user_skype = check($_POST['user_skype'],null);

	if (empty($user_skype)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали логин скайпа!');
	}

	if (mb_strlen($user_skype, 'UTF-8') < 6 or mb_strlen($user_skype, 'UTF-8') > 32) {
		exit('<span class="m-icon icon-remove"></span> Логин скайпа должен состоять не менее чем из 6 символов и не более чем из 32.');
	}

	$STH = $pdo->prepare("UPDATE users SET skype=:user_skype WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_skype' => $user_skype));

	write_log("Логин скайпа изменен на ".$user_skype); 
	exit('<span class="m-icon icon-ok"></span> Ваш логин скайпа изменен!');
}
if (isset($_POST['edit_user_telegram'])) {
	$user_telegram = check($_POST['user_telegram'],null);

	if (empty($user_telegram)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали логин Telegram!');
	}

	if (mb_strlen($user_telegram, 'UTF-8') < 3 or mb_strlen($user_telegram, 'UTF-8') > 50) {
		exit('<span class="m-icon icon-remove"></span> Логин Telegram должен состоять не менее чем из 3 символов и не более чем из 50.');
	}

	$STH = $pdo->prepare("UPDATE users SET telegram=:user_telegram WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_telegram' => $user_telegram));

	write_log("Логин телеграма изменен на ".$user_telegram); 
	exit('<span class="m-icon icon-ok"></span> Ваш логин Telegram изменен!');
}
if (isset($_POST['edit_user_discord'])) {
	$user_discord = check($_POST['user_discord'],null);

	if (empty($user_discord)) {
		exit('<span class="m-icon icon-remove"></span> Вы не указали логин Discord!');
	}

	if (mb_strlen($user_discord, 'UTF-8') < 3 or mb_strlen($user_discord, 'UTF-8') > 32) {
		exit('<span class="m-icon icon-remove"></span> Логин Discord должен состоять не менее чем из 3 символов и не более чем из 32.');
	}

	$STH = $pdo->prepare("UPDATE users SET discord=:user_discord WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_discord' => $user_discord));

	write_log("Логин Discord изменен на ".$user_discord);
	exit('<span class="m-icon icon-ok"></span> Ваш логин Discord изменен!');
}
if (isset($_POST['edit_first_user_password'])) {
	$user_password = check($_POST['user_password'],null);
	$user_password2 = check($_POST['user_password2'],null);

	if (empty($user_password) or empty($user_password2)) {
		exit('<span class="m-icon icon-remove"></span> Вы заполнили не все поля!');
	}

	$U = new Users($pdo);

	if(!$U->check_password_length($user_password)) {
		exit('<span class="m-icon icon-remove"></span> Пароль должен состоять не менее чем из 6 символов и не более чем из 15.');
	}
	if($user_password != $user_password2) {
		exit('<span class="m-icon icon-remove"></span> Новые пароли не совпадают!');
	}

	$user_password = $U->convert_password($user_password, $conf->salt);

	$STH = $pdo->prepare("UPDATE users SET password=:user_password WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_password' => $user_password));

	$_SESSION['cache'] = $SC->get_cache($user_password);
	$SC->set_cookie("cache", $_SESSION['cache']);

	exit('<span class="m-icon icon-ok"></span> Пароль успешно изменен!');
}
if (isset($_POST['edit_user_password'])) {
	$user_old_password = check($_POST['user_old_password'],null);
	$user_password = check($_POST['user_password'],null);
	$user_password2 = check($_POST['user_password2'],null);

	if (empty($user_old_password) or empty($user_password) or empty($user_password2)) {
		exit('<span class="m-icon icon-remove"></span> Вы заполнили не все поля!');
	}

	$U = new Users($pdo);
	$user_old_password = $U->convert_password($user_old_password, $conf->salt);

	$STH = $pdo->prepare("SELECT `password` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $_SESSION['id'] ));
	$row = $STH->fetch();

	if ($user_old_password != $row->password) {
		exit('<span class="m-icon icon-remove"></span> Неверно введен текущий пароль!');
	}

	if(!$U->check_password_length($user_password)) {
		exit('<span class="m-icon icon-remove"></span> Пароль должен состоять не менее чем из 6 символов и не более чем из 15.');
	}

	if ($user_password != $user_password2) {
		exit('<span class="m-icon icon-remove"></span> Новые пароли не совпадают!');
	}

	if ($host == 'demo.worksma.ru' and $_SESSION['id'] == '2') {
		exit('<span class="m-icon icon-remove"></span> Менять пароль на данном аккаунте запрещено!');
	}

	$user_password = $U->convert_password($user_password, $conf->salt);

	$STH = $pdo->prepare("UPDATE users SET password=:user_password WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':user_password' => $user_password));

	$_SESSION['cache'] = $SC->get_cache($user_password);
	$SC->set_cookie("cache", $_SESSION['cache']);

	exit('<span class="m-icon icon-ok"></span> Пароль успешно изменен!');
}
if (isset($_POST['edit_user_avatar'])) {
	if (empty($_FILES['user_avatar']['name'])) {
		exit('<span class="m-icon icon-remove"></span> Выберите изображение!');
	} else {
		$path = 'files/avatars/';

		if (if_img($_FILES['user_avatar']['name'])) {
			$filename = set_temp_file_name($_FILES['user_avatar']['name']);
			$source = $_FILES['user_avatar']['tmp_name'];
			$target = '../'.$path . $filename;
			if (!move_uploaded_file($source, $target)) {
				exit('<span class="m-icon icon-remove"></span> Ошибка загрузки файла!');
			}

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

			$STH = $pdo->query("SELECT avatar FROM users WHERE id='$_SESSION[id]'"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$tmp = $STH->fetch(); 
			if ($tmp->avatar != 'files/avatars/no_avatar.jpg') {
				if(file_exists('../'.$tmp->avatar)) {
					unlink('../'.$tmp->avatar);
				}
			}
		} else {
			exit('<span class="m-icon icon-remove"></span> Аватар должен быть в формате JPG,GIF или PNG');
		}
		$STH = $pdo->prepare("UPDATE users SET avatar=:user_avatar WHERE id='$_SESSION[id]' LIMIT 1");
		$STH->execute(array(':user_avatar' => $user_avatar));
		echo '<span class="m-icon icon-ok"></span> Аватар изменен! <script>document.getElementById("avatar").src = "'.$user_avatar.'" </script>';
	}
	exit();
}
if (isset($_POST['edit_signature'])) {
	$signature = HTMLPurifier()->purify($_POST['signature']);
	$signature = find_img_mp3($signature, $_SESSION['id'], 1);

	if (mb_strlen($signature, 'UTF-8') > 1000) {
		exit('<span class="m-icon icon-remove"></span> Слишком длинный контент.');
	}

	$STH = $pdo->prepare("UPDATE users SET signature=:signature WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':signature' => $signature));

	write_log("Подпись изменена");
	exit('<span class="m-icon icon-ok"></span> Подпись изменена!');
}
/* Друзья
=========================================*/
if (isset($_POST['load_friends'])) {
	$id = checkJs($_POST['id'],"int");
	if(isset($_POST['login'])) {
		$login = check($_POST['login'],null);

		if (empty($login)) {
			exit('<div class="col-md-12">Введите логин пользователя</div>');
		}
	}

	if (empty($id)) {
		exit();
	}

	$i = 0;
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$tpl->result['content'] = '';
	if(isset($login)) {
		$STH = $pdo->prepare("SELECT users.id, users.login, users.avatar, users.nick, users.birth, users.skype, users.vk, users.rights, users.regdate, users.name FROM users__friends 
			LEFT JOIN users ON users__friends.id_taker = users.id 
			WHERE (users__friends.id_sender='$id') AND users__friends.accept='1' AND users.login LIKE :login
			UNION SELECT users.id, users.login, users.avatar, users.nick, users.birth, users.skype, users.vk, users.rights, users.regdate, users.name FROM users__friends 
			LEFT JOIN users ON users__friends.id_sender = users.id 
			WHERE (users__friends.id_taker='$id') AND users__friends.accept='1' AND users.login LIKE :login"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(":login" => "%".strip_data($login)."%"));
	} else {
		$STH = $pdo->query("SELECT users.id, users.login, users.avatar, users.nick, users.birth, users.skype, users.vk, users.rights, users.regdate, users.name FROM users__friends 
			LEFT JOIN users ON users__friends.id_taker = users.id 
			WHERE (users__friends.id_sender='$id') AND users__friends.accept='1' 
			UNION SELECT users.id, users.login, users.avatar, users.nick, users.birth, users.skype, users.vk, users.rights, users.regdate, users.name FROM users__friends 
			LEFT JOIN users ON users__friends.id_sender = users.id 
			WHERE (users__friends.id_taker='$id') AND users__friends.accept='1'"); $STH->setFetchMode(PDO::FETCH_OBJ);
	}
	while($row = $STH->fetch()) {  
		$gp = $users_groups[$row->rights];
		$tpl->load_template('elements/friend.tpl');
		$tpl->set("{login}", $row->login);
		$tpl->set("{id}", $row->id);
		$tpl->set("{avatar}", $row->avatar);
		$tpl->set("{date1}", expand_date($row->regdate,2));
		$tpl->set("{nick}", $row->nick);
		$tpl->set("{name}", $row->name);
		$tpl->set("{date2}", expand_date($row->birth,2));
		$tpl->set("{skype}", $row->skype);
		$tpl->set("{vk}", $row->vk);
		$tpl->set("{gp_color}", $gp['color']);
		$tpl->set("{gp_name}", $gp['name']);
		$tpl->set("{type}", 1);
		$tpl->compile( 'content' );
		$tpl->clear();
		$i++;
	}

	if ($i == 0){
		if(isset($login)) {
			exit('<div class="col-md-12">Пользователь с данным логином не найден.</div>');
		} else {
			if($id == $_SESSION['id']) {
				exit('<div class="col-md-12">У Вас нет друзей. <a href="../users">Добавить друга?</a></div>');
			} else {
				exit('<div class="col-md-12">Друзей нет</div>');
			}
		}
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
if (isset($_POST['load_friend_requests'])) {
	$type = check($_POST['type'], null);
	$id = $_SESSION['id'];

	$i = 0;
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$tpl->result['content'] = '';
	if($type == 'un') {
		$STH = $pdo->query("SELECT users.id, users.login, users.avatar, users.nick, users.birth, users.skype, users.vk, users.rights, users.regdate, users.name FROM users__friends LEFT JOIN users ON users__friends.id_taker = users.id WHERE (id_sender='$id') AND accept='0'"); $STH->setFetchMode(PDO::FETCH_OBJ);
	} else {
		$STH = $pdo->query("SELECT users.id, users.login, users.avatar, users.nick, users.birth, users.skype, users.vk, users.rights, users.regdate, users.name FROM users__friends LEFT JOIN users ON users__friends.id_sender = users.id WHERE (id_taker='$id') AND accept='0'"); $STH->setFetchMode(PDO::FETCH_OBJ);
	}
	while($row = $STH->fetch()) {
		$gp = $users_groups[$row->rights];
		$tpl->load_template('elements/friend.tpl');
		$tpl->set("{login}", $row->login);
		$tpl->set("{id}", $row->id);
		$tpl->set("{avatar}", $row->avatar);
		$tpl->set("{date1}", expand_date($row->regdate,2));
		$tpl->set("{nick}", $row->nick);
		$tpl->set("{name}", $row->name);
		$tpl->set("{date2}", expand_date($row->birth,2));
		$tpl->set("{skype}", $row->skype);
		$tpl->set("{vk}", $row->vk);
		$tpl->set("{gp_color}", $gp['color']);
		$tpl->set("{gp_name}", $gp['name']);
		if($type == 'un') {
			$tpl->set("{type}", 2);
		} else {
			$tpl->set("{type}", 3);
		}
		$tpl->compile( 'content' );
		$tpl->clear();
		$i++;
	}

	if ($i == 0){
		if($type == 'un') {
			echo '<div class="col-md-12">Исходящих заявок нет.</div>';
		} else {
			echo '<div class="col-md-12">Входящих заявок нет.</div>';
		}
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
if (isset($_POST['add_new_friend'])) {
	$id = checkJs($_POST['id'],"int");

	if(empty($id)) {
		exit (json_encode(['status' => '2', 'message' => 'Не указан ID']));
	}

	$STH = $pdo->query("SELECT id FROM users WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)) {
		exit (json_encode(['status' => 2, 'message' => 'Пользователя не существует']));
	}

	$STH = $pdo->query("SELECT id FROM users__friends WHERE (id_sender='$id' and id_taker='$_SESSION[id]') or (id_sender='$_SESSION[id]' and id_taker='$id')"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (isset($row->id)) {
		exit (json_encode(['status' => 2, 'message' => 'Заявка уже существует']));
	}

	if(isOnHisBlacklist($pdo, $id)) {
		exit (json_encode(['status' => 2, 'message' => 'Вы в черном списке']));
	}

	$STH = $pdo->prepare("INSERT INTO users__friends (id_sender,id_taker,accept) values (:id_sender, :id_taker, :accept)"); 
	$STH->execute(array('id_sender' => $_SESSION['id'], 'id_taker' => $id, 'accept' => '0'));

	exit (json_encode(['status' => 1, 'message' => 'Заявка отправлена']));
}
if (isset($_POST['cancel_friend'])) {
	$id = checkJs($_POST['id'],"int");

	if (empty($id)) {
		exit (json_encode(['status' => 2, 'message' => 'Не указан ID']));
	}

	$STH = $pdo->prepare("DELETE FROM users__friends WHERE id_sender=:id_sender AND id_taker=:id_taker LIMIT 1");
	$STH->execute([':id_sender' => $_SESSION['id'], ':id_taker' => $id]);

	exit (json_encode(['status' => 1, 'message' => 'Заявка отменена']));
}
if (isset($_POST['reject_friend'])) {
	$id = checkJs($_POST['id'],"int");

	if (empty($id)) {
		exit (json_encode(['status' => 2, 'message' => 'Не указан ID']));
	}

	$STH = $pdo->prepare("DELETE FROM users__friends WHERE id_sender=:id_sender AND id_taker=:id_taker LIMIT 1");
	$STH->execute([':id_taker' => $_SESSION['id'], ':id_sender' => $id]);

	exit (json_encode(['status' => 1, 'message' => 'Заявка удалена']));
}
if (isset($_POST['take_friend'])) {
	$id = checkJs($_POST['id'],"int");

	if (empty($id)) {
		exit (json_encode(['status' => 2, 'message' => 'Не указан ID']));
	}

	$STH = $pdo->query("SELECT id FROM users WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)){
		exit (json_encode(['status' => 2, 'message' => 'Пользователя не существует']));
	}

	$STH = $pdo->prepare("UPDATE users__friends SET accept = 1 WHERE id_sender=:id_sender AND id_taker=:id_taker LIMIT 1");
	$STH->execute([':id_taker' => $_SESSION['id'], ':id_sender' => $id]);

	exit (json_encode(['status' => 1, 'message' => 'Заявка принята']));
}
if (isset($_POST['dell_friend'])) {
	$id = checkJs($_POST['id'],"int");

	if (empty($id)) {
		exit (json_encode(['status' => 2, 'message' => 'Не указан ID']));
	}

	$STH = $pdo->prepare("DELETE FROM users__friends WHERE (id_sender=:id_sender AND id_taker=:id_taker) OR (id_sender=:id_taker AND id_taker=:id_sender) LIMIT 1");
	$STH->execute([':id_taker' => $_SESSION['id'], ':id_sender' => $id]);

	exit (json_encode(['status' => 1, 'message' => 'Друг удален']));
}
if (isset($_POST['load_col_infriends'])) {
	$STH = $pdo->prepare("SELECT `id` FROM `users__friends` WHERE (`id_taker`=:id) AND accept='0'");
	$STH->execute(array( ':id' => $_SESSION['id'] ));
	$row = $STH->fetchAll();
	$count = count($row);

	if ($count > 0) {
		echo "(+".$count.")";
	}
	exit();
}

/* Новости
=========================================*/
if (isset($_POST['send_new_comment'])) {
	$id = checkJs($_POST['id'],"int");

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);

	if (empty($id)) {
		exit ();
	}

	if (empty($text)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!')));
	}

	if (mb_strlen($text, 'UTF-8') > 10000) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.')));
	}
	$date = date("Y-m-d H:i:s");

	$STH = $pdo->prepare("SELECT `id`, `new_name` FROM `news` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$row = $STH->fetch();
	if(empty($row->new_name)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Новость не существует')));
	}

	$STH = $pdo->prepare("INSERT INTO `news__comments` (user_id,new_id,text,date) values (:user_id, :new_id, :text, :date)");
	if ($STH->execute(array( 'user_id' => $_SESSION['id'], 'new_id' => $id, 'text' => $text, 'date' => $date )) == '1') {
		$id = get_ai($pdo, "news__comments");
		$id--;

		$ES = new EventsRibbon($pdo);
		$ES->new_new_comment($id, $row->new_name, $row->id);

		exit (json_encode(array('status' => '1')));
	}
}

/* Добавление бана
=========================================*/
if (isset($_POST['add_ban'])) {
	$server = check($_POST['server'],"int");
	$nick = check($_POST['nick'],null);
	$reason = check($_POST['reason'],null);
	$screens = check($_POST['screens'],null);
	$demo = check($_POST['demo'],null);
	$bid_db = checkJs($_POST['bid_db'], "int");
	$nick_db = checkJs($_POST['nick_db'],null);
	$reason_db = checkJs($_POST['reason_db'],null);

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $_SESSION['id'], 1);

	if (empty($screens)){
		$screens = 0;
	}
	if (empty($demo)){
		$demo = 0;
	} else {
		if(!filter_var($demo, FILTER_VALIDATE_URL)) {
			exit (json_encode(['status' => '2', 'input' => 'demo', 'reply' => 'Введите корректную ссылку!']));
		}
	}
	if (empty($bid_db)){
		$bid_db = 0;
	}
	if (empty($server)) {
		exit (json_encode(array('status' => '2', 'input' => 'server', 'reply' => 'Заполните!')));
	}
	if (empty($text)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!')));
	}
	if (!empty($bid_db) or !empty($nick_db) or !empty($reason_db)){
		if (empty($bid_db) or empty($nick_db) or empty($reason_db)){
			exit(json_encode(array('status' => '2')));
		}
	}
	if (mb_strlen($demo, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'demo', 'reply' => 'Не более 250 символов!')));
	}

	if (!empty($bid_db)){
		if (empty($nick_db)) {
			exit (json_encode(array('status' => '2', 'input' => 'nick_db', 'reply' => 'Заполните!')));
		}
		if (empty($reason_db)) {
			exit (json_encode(array('status' => '2', 'input' => 'reason_db', 'reply' => 'Заполните!')));
		}
		if (mb_strlen($nick_db, 'UTF-8') > 250) {
			exit (json_encode(array('status' => '2', 'input' => 'nick_db', 'reply' => 'Не более 250 символов!')));
		}
		if (mb_strlen($reason_db, 'UTF-8') > 250) {
			exit (json_encode(array('status' => '2', 'input' => 'reason_db', 'reply' => 'Не более 250 символов!')));
		}
		$nick = $nick_db;
		$reason = $reason_db;
		if(empty($reason)) {
			$reason = 'empty';
		}
	} else {
		if (empty($nick)) {
			exit (json_encode(array('status' => '2', 'input' => 'nick', 'reply' => 'Заполните!')));
		}
		if (empty($reason)) {
			exit (json_encode(array('status' => '2', 'input' => 'reason', 'reply' => 'Заполните!')));
		}
		if (mb_strlen($nick, 'UTF-8') > 250) {
			exit (json_encode(array('status' => '2', 'input' => 'nick', 'reply' => 'Не более 250 символов!')));
		}
		if (mb_strlen($reason, 'UTF-8') > 250) {
			exit (json_encode(array('status' => '2', 'input' => 'reason', 'reply' => 'Не более 250 символов!')));
		}
	}

	$STH = $pdo->query("SELECT date FROM bans WHERE author='$_SESSION[id]' ORDER BY date DESC LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if(isset($row->date)) {
		$delta = time() - strtotime($row->date);
		if ($delta < (24*60*60)) {
			exit (json_encode(array('status' => '3')));
		}
	}

	if (!empty($bid_db)){
		$STH = $pdo->query("SELECT id,ip,port,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE type!=0 and type!=1 and id='$server'"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		$STH = $pdo->query("SELECT * FROM config__prices LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$bans_conf = $STH->fetch();

		$db_host = $row->db_host;
		$db_user = $row->db_user;
		$db_pass = $row->db_pass;
		$db_db = $row->db_db;
		$db_prefix = $row->db_prefix;
		$address = $row->ip.':'.$row->port;
		$ip = $row->ip;
		$port = $row->port;
		$type = $row->type;
		if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			exit(json_encode(array('status' => '2')));
		}
		set_names($pdo2, $row->db_code);

		if ($type == '2' || $type == '3' || $type == '5') {
			$table = set_prefix($db_prefix, 'bans');
			$STH = $pdo2->query("SELECT bid, player_nick as name FROM $table WHERE server_ip = '$address' and bid='$bid_db' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		} else {
			$table = set_prefix($db_prefix, 'servers');
			$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			$sid = $row->sid;
			$table = set_prefix($db_prefix, 'bans');
			$STH = $pdo2->query("SELECT bid, name FROM $table WHERE (sid = '$sid' OR sid = '0') and bid='$bid_db' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		}
		$row = $STH->fetch();
		if (empty($row->bid)){
			exit(json_encode(array('status' => '2')));
		}

		$nick = check($row->name, null);
	}

	$date = date("Y-m-d H:i:s");
	$STH = $pdo->prepare("INSERT INTO bans (server,nick,reason,text,img,date,author,demo,bid,have_answer) values (:server, :nick, :reason, :text, :img, :date, :author, :demo, :bid, :have_answer)");
	if ($STH->execute(array( 'server' => $server, 'nick' => $nick, 'reason' => $reason, 'text' => $text, 'img' => $screens, 'date' => $date, 'author' => $_SESSION['id'], 'demo' => $demo, 'bid' => $bid_db, 'have_answer' => '0' )) == '1') {
		$STH = $pdo->query("SELECT id FROM bans WHERE date='$date' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		incNotifications();
		$letter = letter_of_new_ban($conf->name);
		sendmail('none', $letter['subject'], $letter['message'], $pdo, 1);
		exit (json_encode(array('status' => '1', 'id' => $row->id)));
	}
}
if (isset($_POST['send_ban_comment'])) {
	$id = checkJs($_POST['id'],"int");

	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);

	if (empty($text)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!')));
	}

	if (mb_strlen($text, 'UTF-8') > 10000) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.')));
	}

	$date = date("Y-m-d H:i:s");

	$STH = $pdo->query("SELECT `bans`.`author`, `bans`.`closed`, `users`.`email`, `users`.`email_notice` FROM `bans` LEFT JOIN `users` ON `users`.`id`=`bans`.`author` WHERE `bans`.`id`='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$STH = $pdo->prepare("INSERT INTO `bans__comments` (`user_id`, `ban_id`, `text`, `date`) values (:user_id, :ban_id, :text, :date)");
	$STH->execute(array( 'user_id' => $_SESSION['id'], 'ban_id' => $id, 'text' => $text, 'date' => $date ));
	if ($row->author != $_SESSION['id']){

		$STH = $pdo->prepare("UPDATE `bans` SET `have_answer`=:have_answer WHERE `id`='$id' LIMIT 1");
		$STH->execute(array( 'have_answer' => '1' ));

		incNotifications();

		$noty = noty_of_ban_answer($id);
		send_noty($pdo, $noty, $row->author, 1);

		if($row->email_notice == 1) {
			$letter = letter_of_ban_answer($id, $full_site_host);
			sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
		}
	} else {
		if($row->closed == 0) {
			$STH = $pdo->prepare("UPDATE `bans` SET `have_answer`=:have_answer WHERE `id`='$id' LIMIT 1");
			$STH->execute(array( 'have_answer' => '0' ));
		}
	}
	exit (json_encode(array('status' => '1')));
}

/* Форум
=========================================*/
if (isset($_POST['add_topic']) and is_worthy("w")) {
	$img = checkJs($_POST['img'],null);
	$name = check($_POST['name'],null);
	$forum_id = checkJs($_POST['id'],"int");
	if (empty($forum_id)) {
		exit(json_encode(array('status' => '2')));
	}

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $forum_id, 1);

	if (empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Заполните!')));
	}
	if (empty($text)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!')));
	}
	if (mb_strlen($name, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Не более 250 символов!')));
	}

	$forum = new Forum($pdo);
	if( $topic = $forum->add_topic($name, $img, $text, $forum_id) ) {

		$ES = new EventsRibbon($pdo);
		$ES->new_topic($topic['id'], $name, $topic['access']);

		write_sitemap($full_site_host."forum/topic?id=".$topic['id']);

		exit(json_encode(array('status' => '1', 'id' => $topic['id'])));
	} else {
		exit (json_encode(array('status' => '2')));
	}
}
if (isset($_POST['add_topic_img']) and is_worthy("w")) {
	if(isset($_POST['id'])) {
		$id = check($_POST['id'],"int");
	}

	if (empty($_FILES['img']['name'])) {
		exit('<p class="text-danger">Выберите изображение!</p><script>show_input_error("new_img", "", null);setTimeout(show_error, 500);</script>');
	} else {
		$path = 'files/forums_imgs/';
		$date = time();

		if (if_jpg($_FILES['img']['name'])) {

			$filename = $_FILES['img']['name'];
			$source = $_FILES['img']['tmp_name'];
			$target = '../'.$path . $filename;
			if (!move_uploaded_file($source, $target)) {
				exit('<p class="text-danger">Ошибка загрузки файла!</p>');
			}

			$im = imagecreatefromjpeg('../'.$path . $filename);
			clip_image($im, 300, $path.$date);

			$img = $path.$date.".jpg";
			unlink($target);
		} elseif (if_gif($_FILES['img']['name']) || if_png($_FILES['img']['name'])) {
			$file_type = substr($_FILES['img']['name'], strrpos($_FILES['img']['name'], '.')+1);
			$source = $_FILES['img']['tmp_name'];
			$img = $path.$date.".".$file_type;
			$target = '../'.$img;
			if (!move_uploaded_file($source, $target)) {
				exit('<p class="text-danger">Ошибка загрузки файла!</p>');
			}
		} else {
			exit('<p class="text-danger">Изображение должно быть в формате JPG,GIF или PNG</p><script>show_input_error("new_img", "", null);setTimeout(show_error, 500);</script>');
		}

		if(!empty($id)) {
			$STH = $pdo->query("SELECT `img` FROM `forums__topics` WHERE id='$id'"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$tmp = $STH->fetch();
			if ($tmp->img != 'files/forums_imgs/none.jpg') {
				unlink('../'.$tmp->img);
			}
		}

		echo '<script>$("#pic").attr("src","../'.$img.'");setTimeout(show_ok, 500);</script><input value="'.$img.'" type="hidden" id="topic_img" maxlength="255" autocomplete="off">';
	}
	exit();
}
if (isset($_POST['send_answer'])) {
	$topic_id = checkJs($_POST['id'],"int");
	if (empty($topic_id)) {
		exit(json_encode(array('status' => '2')));
	}

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $topic_id, 1);

	if (empty($text)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!')));
	}
	if (mb_strlen($text, 'UTF-8') > 10000) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.')));
	}

	$forum = new Forum($pdo);
	if( $answer = $forum->add_answer($topic_id, $text) ) {
		$ES = new EventsRibbon($pdo);
		$ES->new_answer($answer['answer'], $answer['topic'], $answer['name'], $answer['access']);

		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if (isset($_POST['thank'])) {
	$id = checkJs($_POST['id'],"int");
	$type = checkJs($_POST['type'],"int");

	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}
	if (empty($type)) {
		$type = 0;
	}

	$Forum = new Forum($pdo);
	if($Forum->thank($id, $type)) {
		exit (json_encode(array('status' => '1', 'idd' => $_SESSION['id'], 'login' => $_SESSION['login'])));
	} else {
		exit (json_encode(array('status' => '2')));
	}
}
/* Поддержка
=========================================*/
if (isset($_POST['load_tickets'])){
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit ();
	}

	$i=0;

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->query("SELECT id,name,status,date,have_answer FROM tickets WHERE author='$id' ORDER BY date DESC"); $STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		if ($row->status == '1'){
			if($row->have_answer == 0) {
				$color = 'warning';
				$status = 'Ожидает ответа';
			} else {
				$color = 'info';
				$status = 'Есть ответ';
			}
		} else {
			$status = 'Закрыт';
			$color = 'success';
		}
		$tpl->load_template('elements/ticket.tpl');
		$tpl->set("{status}", $status);
		$tpl->set("{color}", $color);
		$tpl->set("{i}", $i);
		$tpl->set("{id}", $row->id);
		$tpl->set("{name}", $row->name);
		$tpl->set("{date}", expand_date($row->date,7));
		$tpl->compile( 'content' );
		$tpl->clear();
	}

	if ($i == 0){
		exit('<tr><td colspan="10">Вы не создавали тикетов</td></tr>');
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
if (isset($_POST['add_ticket'])) {
	$file = checkJs($_POST['file'],null);
	$name = check($_POST['name'],null);

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $_SESSION['id'], 1);

	if (empty($file)) {
		$file = 'none';
	}

	if (empty($name)) {
		$result = array('status' => '2', 'input' => 'name', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if (empty($text)) {
		$result = array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if (mb_strlen($name, 'UTF-8') > 255) {
		$result = array('status' => '2', 'input' => 'name', 'reply' => 'Не более 255 символов!');
		exit (json_encode($result));
	}

	if (mb_strlen($text, 'UTF-8') > 10000) {
		$result = array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.');
		exit (json_encode($result));
	}

	$STH = $pdo->query("SELECT date FROM tickets WHERE author='$_SESSION[id]' ORDER BY date DESC LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(isset($row->date)) {
		$delta = time() - strtotime($row->date);
		$ticket_interval = 3; //раз в 3 часа
		if ($delta < ($ticket_interval*60*60)) {
			exit (json_encode(array('status' => '3', 'ticket_interval' => $ticket_interval)));
		}
	}

	$date = date("Y-m-d H:i:s");
	$STH = $pdo->prepare("INSERT INTO tickets (name,text,files,status,date,author,last_answer,closed,have_answer) values (:name, :text, :files, :status, :date, :author, :last_answer, :closed, :have_answer)");
	if ($STH->execute(array( 'name' => $name, 'text' => $text, 'files' => $file, 'status' => '1', 'date' => $date, 'author' => $_SESSION['id'], 'last_answer' => $date, 'closed' => '0', 'have_answer' => '0' )) == '1') {
		$STH = $pdo->query("SELECT id FROM tickets WHERE date='$date' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		incNotifications();
		$letter = letter_of_new_ticket($conf->name);
		sendmail('none', $letter['subject'], $letter['message'], $pdo, 1);
		exit (json_encode(array('status' => '1', 'id' => $row->id)));
	}
}
if (isset($_POST['close_ticket'])) {
	$id = check($_POST['id'],"int");
	if (empty($id)){
		exit(json_encode(array('status' => '2')));
	}
	$STH = $pdo->query("SELECT `tickets`.`author`, `users`.`email`, `users`.`email_notice` FROM `tickets` LEFT JOIN `users` ON `users`.`id`=`tickets`.`author` WHERE `tickets`.`id`='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (!empty($row->author)){
		if (($row->author == $_SESSION['id']) or (is_worthy("p"))){
			$STH = $pdo->prepare("UPDATE `tickets` SET `status`=:status, `closed`=:closed, `have_answer`=:have_answer WHERE `id`='$id' LIMIT 1");
			if ($STH->execute(array( 'status' => '2', 'closed' => $_SESSION['id'], 'have_answer' => '1' )) == '1') {
				if ($row->author != $_SESSION['id']) {
					incNotifications();

					$noty = close_ticket_noty($id);
					send_noty($pdo, $noty, $row->author, 1);

					if($row->email_notice == 1) {
						$letter = close_ticket_letter($id, $full_site_host);
						sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
					}
				}

				exit (json_encode(array('status' => '1')));
			}
		}
	}
	exit(json_encode(array('status' => '2')));
}
if (isset($_POST['send_ticket_answer'])) {
	$id = checkJs($_POST['id'],"int");

	if (empty($id)) {
		exit (json_encode(array('status' => '2')));
	}

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);

	if (empty($text)) {
		$result = array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if (mb_strlen($text, 'UTF-8') > 10000) {
		$result = array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.');
		exit (json_encode($result));
	}

	$STH = $pdo->query("SELECT `tickets`.`author`, `tickets`.`closed`, `users`.`email`, `users`.`email_notice` FROM `tickets` LEFT JOIN `users` ON `users`.`id`=`tickets`.`author` WHERE `tickets`.`id`='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if (($row->author != $_SESSION['id']) and (!is_worthy("p"))){
		exit (json_encode(array('status' => '2')));
	}

	if ($row->closed != 0){
		exit (json_encode(array('status' => '2')));
	}

	$date = date("Y-m-d H:i:s");

	$STH = $pdo->prepare("INSERT INTO `tickets__answers` (`author`, `ticket`, `text`, `date`) values (:author, :ticket, :text, :date)");
	if ($STH->execute(array( 'author' => $_SESSION['id'], 'ticket' => $id, 'text' => $text, 'date' => $date )) == '1') {
		$STH = $pdo->prepare("UPDATE `tickets` SET `last_answer`=:last_answer WHERE `id`='$id' LIMIT 1");
		$STH->execute(array( 'last_answer' => $date ));
		if (is_worthy("p")){
			$STH = $pdo->prepare("UPDATE `tickets` SET `have_answer`=:have_answer WHERE `id`='$id' LIMIT 1");
			$STH->execute(array( 'have_answer' => '1' ));

			incNotifications();

			$noty = noty_of_ticket_answer($id);
			send_noty($pdo, $noty, $row->author, 1);

			if($row->email_notice == 1) {
				$letter = letter_of_ticket_answer($id, $full_site_host);
				sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
			}
		} else {
			$STH = $pdo->prepare("UPDATE `tickets` SET `have_answer`=:have_answer WHERE `id`='$id' LIMIT 1");
			$STH->execute(array( 'have_answer' => '0' ));
		}
		exit (json_encode(array('status' => '1')));
	}

	exit (json_encode(array('status' => '2')));
}
if (isset($_POST['load_ticket_answers'])) {
	$id = checkJs($_POST['id'],"int");
	$i = 0;

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$tpl->result['content'] = '';
	$STH = $pdo->query("SELECT tickets__answers.*, users.login, users.avatar, users.rights, users.id AS us_id FROM tickets__answers LEFT JOIN users ON tickets__answers.author = users.id WHERE ticket = '$id' ORDER BY id DESC"); $STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$gp = $users_groups[$row->rights];
		$date = expand_date($row->date,8);
		$i++;
		$tpl->load_template('elements/comment.tpl');
		$tpl->set("{id}", $row->id);
		$tpl->set("{dell}", '');
		$tpl->set("{login}", $row->login);
		$tpl->set("{avatar}", $row->avatar);
		$tpl->set("{text}", $row->text);
		$tpl->set("{date_short}", $date['short']);
		$tpl->set("{date_full}", $date['full']);
		$tpl->set("{user_id}", $row->us_id);
		$tpl->set("{gp_color}", $gp['color']);
		$tpl->set("{gp_name}", $gp['name']);
		$tpl->compile( 'content' );
		$tpl->clear();
	}
	if ($i == 0){
		echo '<span class="empty-element">Сообщений не найдено</span>';
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
/* Стена
=========================================*/
if (isset($_POST['send_user_comment'])) {
	$id = checkJs($_POST['id'],"int");

	if (empty($id)) {
		exit ();
	}

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);
	$text = str_replace('="files/', '="../files/', $text);

	if (empty($text)) {
		$result = array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if (mb_strlen($text, 'UTF-8') > 5000) {
		$result = array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.');
		exit (json_encode($result));
	}

	$STH = $pdo->query("SELECT id FROM users__friends WHERE ((id_sender='$id' and id_taker='$_SESSION[id]') or (id_sender='$_SESSION[id]' and id_taker='$id')) and (accept='1') LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)){
		if ($_SESSION['id'] != $id){
			exit(json_encode(array('status' => '2')));
		}
	}

	$STH = $pdo->prepare("INSERT INTO users__comments (author,user_id,text,date) values (:author, :user_id, :text, :date)");
	if ($STH->execute(array( 'author' => $_SESSION['id'], 'user_id' => $id, 'text' => $text, 'date' => date("Y-m-d H:i:s") )) == '1') {
		exit (json_encode(array('status' => '1')));
	}
}
if (isset($_POST['dell_user_comment'])){
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit ();
	}
	$STH = $pdo->query("SELECT user_id FROM users__comments WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (($_SESSION['id'] != $row->user_id) and (!is_worthy("y"))){
		exit(json_encode(array('status' => '2')));
	}
	$pdo->exec("DELETE FROM users__comments WHERE id='$id' LIMIT 1");
	exit(json_encode(array('status' => '1')));
}
/* Загрузка rar, zip, 7z, jpg, jpeg, png
=========================================*/
if (isset($_POST['load_file'])) {
	if (empty($_FILES['file']['name'])) {
		exit('<script>show_input_error("file", "", null);setTimeout(show_error, 500);</script>');
	} else {
		$path = 'files/files/';
		$date = time();

		if (if_img($_FILES['file']['name']) || if_archive($_FILES['file']['name'])) {
			$file_type = substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.')+1);
			$source = $_FILES['file']['tmp_name'];
			$file = $path.$date.".".$file_type;
			$target = '../'.$file;
			if (!move_uploaded_file($source, $target)) {
				exit('<p class="text-danger">Ошибка загрузки файла!</p>');
			}
		} else {
			exit('<script>$("#load_error_message").css("color", "#B74747")</script>');
		}
		exit('<input value="'.$file.'" type="hidden" id="loaded_file" maxlength="255" autocomplete="off">
			<p class="text-success"></span>Файл загружен! <span class="m-icon icon-ok"></p>
			');
	}
}
/* Банлист
=========================================*/
if (isset($_POST['search_ban2'])) {
	$bid = checkJs($_POST['ban'], "int");
	$server = checkJs($_POST['server'], null);
	if (empty($bid) or empty($server)){
		exit();
	}

	$STH = $pdo->query("SELECT id,ip,port,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE type!=0 and type!=1 and id='$server'"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$STH = $pdo->query("SELECT * FROM config__prices LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$bans_conf = $STH->fetch();

	$db_host = $row->db_host;
	$db_user = $row->db_user;
	$db_pass = $row->db_pass;
	$db_db = $row->db_db;
	$db_prefix = $row->db_prefix;
	$address = $row->ip.':'.$row->port;
	$ip = $row->ip;
	$port = $row->port;
	$type = $row->type;
	if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
		exit('<span class="empty-element">Не удалось подключиться к базе данных</span>');
	}
	set_names($pdo2, $row->db_code);

	if ($type == '2' || $type == '3' || $type == '5') {
		$table = set_prefix($db_prefix, 'bans');
		$STH = $pdo2->prepare("SELECT player_nick,ban_reason,ban_length,bid,expired,player_ip,player_id,ban_created FROM $table WHERE server_ip = '$address' and bid=:bid LIMIT 1");
		$STH->execute(array(':bid' => $bid));
	} else {
		$table = set_prefix($db_prefix, 'servers');
		$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$sid = $row->sid;
		$table = set_prefix($db_prefix, 'bans');
		$STH = $pdo2->prepare("SELECT bid,ip AS player_ip,RemoveType AS expired,authid AS player_id,name AS player_nick,created AS ban_created,length AS ban_length,reason AS ban_reason FROM $table WHERE (sid = '$sid' OR sid = '0') and bid=:bid LIMIT 1");
		$STH->execute(array(':bid' => $bid));
	}
	$result = $STH->fetchAll();

	$disp = "";
	$player_nick = check($result['0']['player_nick'], null);
	if(empty($result['0']['ban_reason'])) {
		$ban_reason = 'Неизвестно';
	} else {
		$ban_reason = check($result['0']['ban_reason'], null);
	}

	if ($type == '2' || $type == '3' || $type == '5') {
		$ban_length = $result['0']['ban_length']*60;
	} else {
		$ban_length = $result['0']['ban_length'];
	}
	$ban_created = $result['0']['ban_created'];
	if ($result['0']['expired'] == 1 or $result['0']['expired'] == "E" or $result['0']['expired'] == "U"){
		$color = "success";
		$disp = "disp-n";
		$time = expand_date(date("Y-m-d H:i:s", ($ban_created+$ban_length)), 1);
	} else {
		if ($ban_length == 0){
			$color = "danger";
			$time =  'Никогда';
			$price = $bans_conf->price3;
		} else {
			$now = time();
			$ban_created = $result['0']['ban_created'];
			$time = expand_date(date("Y-m-d H:i:s", ($ban_created+$ban_length)), 1);
			if (($ban_created+$ban_length) < $now){
				$color = "success";
				$disp = "disp-n";
			} else {
				$color = "danger";
				$date = diff_date(date("Y-m-d H:i:s", ($ban_created+$ban_length)), date("Y-m-d H:i:s"));
				if ($date['2'] < '7' and $date['1']=='0' and $date['0']=='0'){
					$price = $bans_conf->price1;
				} else {
					$price = $bans_conf->price2;
				}
			}
		}
	}

	if (empty($result['0']['bid'])){
		exit('<div class="panel panel-default mt-10"><div class="panel-body">Бан не найден</div></div>');
	}

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$tpl->load_template('elements/search_ban2.tpl');
	$tpl->set("{bid}", $result['0']['bid']);
	$tpl->set("{player_ip}", isNeedHidePlayerId() ? hidePlayerId($result['0']['player_ip']) : $result['0']['player_ip']);
	$tpl->set("{player_id}", isNeedHidePlayerId() ? hidePlayerId($result['0']['player_id']) : $result['0']['player_id']);
	$tpl->set("{player_nick}", $player_nick);
	$tpl->set("{ban_reason}", $ban_reason);
	$tpl->set("{time}", $time);
	$tpl->set("{color}", $color);
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->show($tpl->result['content']);
	$tpl->global_clear();
	exit();
}
if (isset($_POST['find_bans'])) {
	$server = checkJs($_POST['server'], "int");
	$name = check($_POST['ban'], null);

	if (empty($server)){
		exit(json_encode(array('status' => '2', 'data' => '<tr><td colspan="10">Ошибка: [Неизвестные переменные]</td></tr>')));
	}

	if (empty($name)){
		exit(json_encode(array('status' => '2', 'data' => '<tr><td colspan="10">Бан не найден</td></tr>')));
	}

	$STH = $pdo->query("SELECT id,ip,port,name,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE type!=0 and type!=1 and id='$server'"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$db_host = $row->db_host;
	$db_user = $row->db_user;
	$db_pass = $row->db_pass;
	$db_db = $row->db_db;
	$db_prefix = $row->db_prefix;
	$address = $row->ip.':'.$row->port;
	$ip = $row->ip;
	$port = $row->port;
	$type = $row->type;
	$server_name = $row->name;
	if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
		exit(json_encode(array('status' => '2', 'data' => '<tr><td colspan="10">Не удалось подключиться к базе данных</td></tr>')));
	}
	set_names($pdo2, $row->db_code);

	$i=0;
	$table = set_prefix($db_prefix, 'bans');
	if ($type == '2' || $type == '3' || $type == '5') {
		$table = set_prefix($db_prefix, 'bans');
		$STH = $pdo2->prepare("SELECT * FROM $table WHERE server_ip = '$address' and (player_ip LIKE :name or player_nick LIKE :name or player_id LIKE :name ) and (expired = '0' or expired IS NULL) ORDER BY bid DESC"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(":name" => getNameLike($name)));
	} else {
		$table = set_prefix($db_prefix, 'servers');
		$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$sid = $row->sid;
		$table1 = set_prefix($db_prefix, 'bans');
		$table2 = set_prefix($db_prefix, 'admins');
		$STH = $pdo2->prepare("SELECT $table1.bid,$table1.ip AS player_ip,$table1.RemoveType AS expired,$table1.authid AS player_id,$table1.name AS player_nick,$table1.created AS ban_created,$table1.length AS ban_length,$table1.reason AS ban_reason,$table2.user AS admin_nick,$table2.nick AS admin_nick2 FROM $table1 LEFT JOIN $table2 ON $table1.aid = $table2.aid WHERE ($table1.sid = '$sid' OR  $table1.sid = '0') and ($table1.ip LIKE :name or $table1.authid LIKE :name or $table1.name LIKE :name ) and ($table1.RemoveType IS NULL or $table1.RemoveType = '0') ORDER BY $table1.bid DESC"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(":name" => "%".strip_data($name)."%"));
	}
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	while($row = $STH->fetch()) {
		if ($type == '2' || $type == '3' || $type == '5') {
			$ban_length = $row->ban_length*60;
		} else {
			$ban_length = $row->ban_length;
		}
		if(!isset($row->admin_nick2)) {
			$row->admin_nick2 = null;
		}
		$admin_nick = get_ban_admin_nick($row->admin_nick, $row->admin_nick2, $server_name, $type);

		$ban_length2 = expand_seconds2($ban_length);
		$player_nick = check($row->player_nick, null);
		if(empty($row->ban_reason)) {
			$ban_reason = 'Неизвестно';
		} else {
			$ban_reason = check($row->ban_reason, null);
		}

		if (($ban_length == 0) or (($row->ban_created+$ban_length) > time())){
			$i++;
			if ($ban_length == 0) {
				$time =  'Никогда';
				$color = "danger";
			} else {
				$time = expand_date(date("Y-m-d H:i:s", ($row->ban_created+$ban_length)), 1);
				$color = "info";
			}
			$tpl->load_template('elements/ban_small.tpl');
			$tpl->set("{bid}", $row->bid);
			$tpl->set("{player_nick}", $player_nick);
			$tpl->set("{ban_reason}", $ban_reason);
			$tpl->set("{server}", $server);
			$tpl->set("{time}", $time);
			$tpl->set("{address}", $address);
			$tpl->set("{admin_nick}", $admin_nick);
			$tpl->set("{type}", $type);
			$tpl->set("{player_ip}", isNeedHidePlayerId() ? hidePlayerId($row->player_ip) : $row->player_ip);
			$tpl->set("{player_id}", isNeedHidePlayerId() ? hidePlayerId($row->player_id) : $row->player_id);
			$tpl->set("{ban_length}", $ban_length2);
			$tpl->set("{server_name}", $server_name);
			$tpl->set("{color}", $color);
			$tpl->compile( 'content' );
			$tpl->clear();
		}
	}
	if ($i == 0){
		$tpl->result['content'] = "<tr><td colspan='10'>Бан не найден</td></tr>";
	}
	exit(json_encode(array('status' => '1', 'data' => $tpl->result['content'])));
}
/* Уведомления
=========================================*/
if (isset($_POST['dell_notification'])){
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit ();
	}
	$pdo->exec("DELETE FROM notifications WHERE id='$id' and user_id='$_SESSION[id]' LIMIT 1");
	exit ();
}
if (isset($_POST['close_notification'])){
	$id = checkJs($_POST['id'],"int");
	if (empty($id)) {
		exit ();
	}
	$STH = $pdo->prepare("UPDATE notifications SET status=:status WHERE id='$id' and user_id='$_SESSION[id]' LIMIT 1");
	$STH->execute(array( 'status' => '1' ));
	exit ();
}
if (isset($_POST['close_notifications'])){
	$STH = $pdo->prepare("UPDATE notifications SET status=:status WHERE status='0' and user_id='$_SESSION[id]'");
	$STH->execute(array( 'status' => '1' ));
	exit ();
}
if (isset($_POST['dell_notifications'])){
	$STH = $pdo->prepare("DELETE FROM `notifications` WHERE `user_id`=:user_id");
	$STH->execute(array( ':user_id' => $_SESSION['id'] ));
	exit ();
}
if (isset($_POST['on_im'])) {
	$val = checkJs($_POST['val'], "int");
	if ($val != 1 and $val != 2) {
		exit(json_encode(array('status' => '2')));
	}
	$STH = $pdo->prepare("UPDATE users SET im=:val WHERE id='$_SESSION[id]' LIMIT 1");
	if ($STH->execute(array( 'val' => $val )) == '1') {
		exit(json_encode(array('status' => '1')));
	}
}
if (isset($_POST['on_ip_protect'])) {
	$val = checkJs($_POST['val'], "int");
	if ($val != 1 and $val != 2) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("UPDATE users SET protect=:val WHERE id='$_SESSION[id]' LIMIT 1");
	if ($STH->execute(array( 'val' => $val )) == '1') {
		if($val == 1) {
			$SC->ip = get_ip();
		} else {
			$SC->ip = 0;
		}

		$STH = $pdo->prepare("SELECT `password` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $_SESSION['id'] ));
		$row = $STH->fetch();

		$_SESSION['cache'] = $SC->get_cache($row->password);
		$SC->set_cookie("cache", $_SESSION['cache']);

		exit(json_encode(array('status' => '1')));
	}
}
if (isset($_POST['on_email_notice'])) {
	$val = checkJs($_POST['val'], "int");
	if ($val != 1 and $val != 2) {
		exit(json_encode(array('status' => '2')));
	}
	$STH = $pdo->prepare("UPDATE users SET email_notice=:val WHERE id='$_SESSION[id]' LIMIT 1");
	if ($STH->execute(array( 'val' => $val )) == '1') {
		exit(json_encode(array('status' => '1')));
	}
}
if (isset($_POST['get_referrals'])) {
	$STH = $pdo->prepare("SELECT `id`, `login`, `avatar`, `regdate` FROM `users` WHERE `invited`=:invited"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':invited' => $_SESSION['id'] ));
	$i = 0;
	while($row = $STH->fetch()) {
		$i++;
		$shilings = 0;
		$STH2 = $pdo->prepare("SELECT `shilings` FROM `money__actions` WHERE `gave_out`=:gave_out"); $STH2->setFetchMode(PDO::FETCH_OBJ);
		$STH2->execute(array( ':gave_out' => $row->id ));
		while($row2 = $STH2->fetch()) {
			$shilings += $row2->shilings;
		}
		?>
		<tr>
			<td><?php echo $i; ?></td>
			<td>
				<a target="_blank" href="../profile?id=<?php echo $row->id; ?>">
					<img src="../<?php echo $row->avatar; ?>" alt="<?php echo $row->login; ?>" class="small_us_av"> <?php echo $row->login; ?>
				</a>
			</td>
			<td><?php echo expand_date($row->regdate, 7); ?></td>
			<td><?php echo $shilings; ?></td>
		</tr>
		<?php
	}
	if($i == 0) {
		?>
		<tr>
			<td colspan="10">
				Рефералы не найдены
			</td>
		</tr>
		<?php
	}
	exit();
}
if (isset($_POST['get_ref_profit'])) {
	$STH = $pdo->prepare("SELECT `users`.`id`, `users`.`login`, `users`.`avatar`, `money__actions`.`date`, `money__actions`.`shilings` FROM `money__actions` 
		LEFT JOIN `users` ON `money__actions`.`gave_out` = `users`.`id` WHERE `money__actions`.`author`=:author AND `money__actions`.`type` = '11'"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':author' => $_SESSION['id'] ));
	$i = 0;
	while($row = $STH->fetch()) {
		$i++;
		?>
		<tr>
			<td><?php echo $i; ?></td>
			<td>
				<a target="_blank" href="../profile?id=<?php echo $row->id; ?>">
					<img src="../<?php echo $row->avatar; ?>" alt="user" class="small_us_av"> <?php echo $row->login; ?>
				</a>
			</td>
			<td><?php echo $row->shilings; ?></td>
			<td><?php echo expand_date($row->date, 7); ?></td>
		</tr>
		<?php
	}
	if($i == 0) {
		?>
		<tr>
			<td colspan="10">
				Зачислений не производилось
			</td>
		</tr>
		<?php
	}
	exit();
}

if(isset($_POST['edit_user_prefix'])) {
	$user_prefix = check($_POST['user_prefix'], null);

	if(mb_strlen($user_prefix, 'UTF-8') > 16) {
		exit('<span class="glyphicon glyphicon-remove"></span> Префикс должен состоять не более чем из 16 символов.');
	}

	if(!empty($user_prefix)) {
		$STH = $pdo->query("SELECT id, login FROM users WHERE prefix='$user_prefix' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(!empty($row->id) && $row->id != $_SESSION['id']) {
			exit('<p class="text-danger">Введеный Вами префикс занят пользователем <a href="../profile?id=' . $row->id . '" target="_blank">' . $row->login . '</a></p>');
		}

		$date = time() - 24 * 60 * 60 * 1;
		$pdo->exec("DELETE FROM last_actions WHERE date<'$date' and user_id='$_SESSION[id]' and action_type = '5' LIMIT 1");
		$STH = $pdo->query("SELECT id,date FROM last_actions WHERE user_id = '$_SESSION[id]' and action_type = '5'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(!empty($row->id)) {
			$delta = time() - $row->date;
			if($delta < (24 * 60 * 60 * 1)) {
				exit('<p class="text-danger">Префикс разрешено менять раз в сутки</p>');
			}
		}
	}

	$STH = $pdo->prepare("UPDATE users SET prefix=:user_prefix WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute([':user_prefix' => $user_prefix]);

	$STH = $pdo->prepare("INSERT INTO last_actions (user_id,action_type,date) VALUES (:user_id, :action_type, :date)");
	$STH->execute(['user_id' => $_SESSION['id'], 'action_type' => '5', 'date' => time()]);

	write_log("Префикс изменен на " . $user_prefix . " | actions_a.php");
	exit('<span class="glyphicon glyphicon-ok"></span> Ваш префикс изменен!');
}

if (isset($_POST['addToBlackList'])) {
	$userId = checkJs($_POST['userId'], "int");

	if(Users::isUserExists($pdo, $userId) && !isOnMyBlacklist($pdo, $userId)) {
		$STH = $pdo->prepare("INSERT INTO users__black_list (who, whom) VALUES (:who, :whom)");
		$STH->execute(['who' => $_SESSION['id'], 'whom' => $userId]);

		$STH = $pdo->prepare("DELETE FROM users__friends WHERE (id_sender=:friend_id AND id_taker=:my_id) OR (id_sender=:my_id AND id_taker=:friend_id) LIMIT 1");
		$STH->execute(array( ':friend_id' => $userId, ':my_id' => $_SESSION['id'] ));

		exit(json_encode(['status' => '1', 'message' => 'Пользователь добавлен в черный список']));
	} else {
		exit(json_encode(['status' => '2', 'message' => 'Пользователь уже в черном списке']));
	}
}

if (isset($_POST['removeFromBlackList'])) {
	$userId = checkJs($_POST['userId'], "int");

	if($blockId = isOnMyBlacklist($pdo, $userId)) {
		$STH = $pdo->prepare("DELETE FROM users__black_list WHERE id=:id LIMIT 1");
		$STH->execute([':id' => $blockId]);

		exit(json_encode(['status' => '1', 'message' => 'Пользователь удален из черного списка']));
	} else {
		exit(json_encode(['status' => '2', 'message' => 'Пользователь уже удален']));
	}
}

if (isset($_POST['getBlackList'])) {
	$STH = $pdo->prepare("SELECT 
							    users.id, 
							    users.login, 
							    users.avatar 
							FROM 
							    users__black_list 
							        LEFT JOIN 
							        users 
							            ON  
							                users__black_list.whom = users.id
							WHERE users__black_list.who=:who"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':who' => $_SESSION['id']]);

	$i = 0;
	while($row = $STH->fetch()) {
		$i++;
		?>
		<tr>
			<td class="va-m"><?php echo $i; ?></td>
			<td>
				<a target="_blank" href="../profile?id=<?php echo $row->id; ?>">
					<img src="../<?php echo $row->avatar; ?>" alt="<?php echo $row->login; ?>" class="small_us_av"> <?php echo $row->login; ?>
				</a>
			</td>
			<td>
				<button
						type="button"
						class="btn btn-default btn-sm"
						onclick="removeFromBlackList(<?php echo $row->id; ?>, getBlackList());">
					Разблокировать
				</button>
			</td>
		</tr>
		<?php
	}
	if($i == 0) {
		?>
		<tr>
			<td colspan="10">
				Черный список пуст
			</td>
		</tr>
		<?php
	}
	exit();
}

/* Жалобы
=========================================*/
if (isset($_POST['findTheAccused'])) {
	$serverId = checkJs($_POST['server_id'], 'int');
	$accused = check($_POST['accused'], null);

	if (empty($serverId)){
		exit('<tr><td colspan="10">Пусто</td></tr>');
	}

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$tpl->result['content'] = '';
	$admins = (new Complaints($pdo, $tpl))->findAccused($serverId, $accused);

	foreach($admins as $admin) {
		$tpl->load_template('elements/complaint_find_result.tpl');
		foreach($admin as $key => $value) {
			$tpl->set('{' . $key . '}', $value);
		}
		$tpl->compile('content');
		$tpl->clear();
	}

	if ($tpl->result['content'] == '') {
		$tpl->result['content'] = '<tr><td colspan="10">Нет результатов</td></tr>';
	}

	$tpl->show($tpl->result['content']);
	exit();
}

if(isset($_POST['setTheAccused'])) {
	$adminId = check($_POST['adminId'], 'int');

	if(empty($adminId)) {
		exit('Ошибка');
	}

	$tpl                    = new Template;
	$tpl->dir               = '../templates/' . $conf->template . '/tpl/';
	$tpl->result['content'] = '';
	$admin                  = (new Complaints($pdo, $tpl))->getAccusedById($adminId);

	if(!empty($admin)) {
		$tpl->load_template('elements/complaint_find_result_card.tpl');
		$tpl->set('{id}', $admin['id']);
		$tpl->set('{user_id}', $admin['user_id']);
		$tpl->set('{avatar}', $admin['avatar']);
		$tpl->set('{login}', $admin['login']);
		$tpl->set('{gp_name}', $admin['gp_name']);
		$tpl->set('{gp_color}', $admin['gp_color']);
		$tpl->set('{name}', $admin['name']);
		$tpl->set('{services}', $admin['services']);
		$tpl->compile('content');
		$tpl->clear();
		$tpl->show($tpl->result['content']);
		exit();
	} else {
		exit('Ошибка');
	}
}

if(isset($_POST['save_user_status'])):
	if(isset($_POST['message'])):
		$message = strip_tags($_POST['message']);
	else:
		$message = 'none';
	endif;

	if(pdo()->query("UPDATE `users` SET `status_message`='{$message}' WHERE `id`='{$_SESSION['id']}'")):
		exit(json_encode(['alert' => 'success', 'message' => $message]));
	endif;
endif;

if (isset($_POST['addComplaint'])) {
	$accusedId = check($_POST['accusedId'], 'int');
	$screens   = check($_POST['screens'], null);
	$demo      = check($_POST['demo'], null);

	$description = HTMLPurifier()->purify($_POST['description']);
	$description = find_img_mp3($description, $_SESSION['id'], 1);

	if(empty($screens)) {
		$screens = 0;
	}

	if(empty($demo)) {
		$demo = 0;
	} else {
		if(!filter_var($demo, FILTER_VALIDATE_URL)) {
			exit (json_encode(['status' => 2, 'input' => 'demo', 'reply' => 'Введите корректную ссылку!']));
		}
	}

	$accused = empty($accusedId) ? [] : (new Complaints($pdo))->getAccusedById($accusedId);

	if(empty($accused)) {
		exit (json_encode(['status' => 2, 'input' => 'accused', 'reply' => 'Выберите!']));
	}

	if(empty($description)) {
		exit (json_encode(['status' => 2, 'input' => 'description', 'reply' => 'Заполните!']));
	}

	if (mb_strlen($demo, 'UTF-8') > 250) {
		exit (json_encode(array('status' => 2, 'input' => 'demo', 'reply' => 'Не более 250 символов!')));
	}

	$STH = $pdo->prepare("SELECT date FROM complaints WHERE author_id=:author_id ORDER BY date DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':author_id' => $_SESSION['id']]);
	$row = $STH->fetch();

	if(isset($row->date)) {
		if((time() - strtotime($row->date)) < (24 * 60 * 60)) {
			exit (json_encode(['status' => 2, 'input' => 'none', 'reply' => 'Жалобу можно создавать раз в 24 часа']));
		}
	}

	$STH  = $pdo->prepare(
		"INSERT INTO complaints (author_id, accused_admin_server_id, accused_admin_id, accused_admin_nick, screens, date, description, demo, accused_profile_id) values (:author_id, :accused_admin_server_id, :accused_admin_id, :accused_admin_nick, :screens, :date, :description, :demo, :accused_profile_id)"
	);
	$STH->execute(
		[
			'author_id'               => $_SESSION['id'],
			'accused_admin_server_id' => $accused['server_id'],
			'accused_admin_id'        => $accused['id'],
			'accused_admin_nick'      => $accused['name_original'],
			'screens'                 => $screens,
			'date'                    => date('Y-m-d H:i:s'),
			'description'             => $description,
			'demo'                    => $demo,
			'accused_profile_id'      => $accused['user_id']
		]
	);

	$id = get_ai($pdo, 'complaints') - 1;

	incNotifications();

	$letter = letter_of_new_complaint($conf->name);
	sendmail('none', $letter['subject'], $letter['message'], $pdo, 1);

	$STH = $pdo->prepare("SELECT email, email_notice FROM users WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $accused['user_id']]);
	$row = $STH->fetch();

	if(!empty($row->email)) {
		$noty = noty_of_new_complaint_to_accused($id);
		send_noty($pdo, $noty, $accused['user_id'], 1);

		if($row->email_notice == 1) {
			$letter = letter_of_new_complaint_to_accused($conf->name, $id);
			sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
		}
	}

	exit (json_encode(['status' => 1, 'id' => $id]));
}

if(isset($_POST['sendComplaintComment'])) {
	$id = checkJs($_POST['id'], "int");

	if(empty($id)) {
		exit(json_encode(['status' => 2]));
	}

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);

	if(empty($text)) {
		exit (json_encode(['status' => 2, 'input' => 'text', 'reply' => 'Заполните!']));
	}

	if(mb_strlen($text, 'UTF-8') > 10000) {
		exit (json_encode(['status' => 2, 'input'  => 'text', 'reply'  => 'Слишком длинный контент.']));
	}

	$STH = $pdo->prepare(
		"SELECT 
    				complaints.sentence, 
    				author.id as author_id, 
				    author.email as author_email, 
				    author.email_notice as author_email_notice,
   					accused.id as accused_id, 
      				accused.email as accused_email, 
				    accused.email_notice as accused_email_notice
				FROM 
				    complaints 
				        LEFT JOIN users author ON author.id=complaints.author_id
						LEFT JOIN users accused ON accused.id=complaints.accused_profile_id
				WHERE complaints.id = :id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();

	if($row->sentence != 0) {
		exit(json_encode(['status' => 2]));
	}

	$STH = $pdo->prepare(
		"INSERT INTO complaints__comments (user_id, complaint_id, text, date) values (:user_id, :ban_id, :text, :date)"
	);
	$STH->execute(
		[
			'user_id' => $_SESSION['id'],
			'ban_id'  => $id,
			'text'    => $text,
			'date'    => date("Y-m-d H:i:s")
		]
	);

	incNotifications();

	$noty = noty_of_complaint_answer($id);
	$letter = letter_of_complaint_answer($id, $full_site_host);

	if($row->author_id != $_SESSION['id']) {
		send_noty($pdo, $noty, $row->author_id, 1);

		if($row->author_email_notice == 1) {
			sendmail($row->author_email, $letter['subject'], $letter['message'], $pdo);
		}
	}

	if(!empty($row->accused_id)) {
		if($row->accused_id != $_SESSION['id']) {
			send_noty($pdo, $noty, $row->accused_id, 1);

			if($row->accused_email_notice == 1) {
				sendmail($row->accused_email, $letter['subject'], $letter['message'], $pdo);
			}
		}
	}

	$STH = $pdo->prepare("UPDATE complaints SET have_answer=:have_answer WHERE id = :id LIMIT 1");
	$STH->execute(['have_answer' => 1, 'id' => $id]);

	exit (json_encode(['status' => 1]));
}

if(isset($_POST['loadImages'])) {
	$folder = check($_POST['folder'], null);
	$counter = empty($_POST['counter']) ? 0 : check($_POST['counter'], 'int');

	$folders = [
		'complaints' => 'complaints_imgs',
		'unbans' => 'bans_imgs',
	];

	if(!array_key_exists($folder, $folders)) {
		exit(json_encode(['status' => 2, 'content' => 'Ошибка']));
	}

	if ($counter >= 5){
		exit(json_encode(['status' => 2, 'content' => 'Загружено максимальное количество изображений']));
	}

	if (empty($_FILES['image']['name'])) {
		exit(json_encode(['status' => 2, 'content' => 'Выберите изображение!']));
	} else {
		$path = 'files/' . $folders[$folder] . '/';
		$name = time() . rand(0, 9);

		if (if_img($_FILES['image']['name'])) {
			$image = $path . $name . '.jpg';
			if (!move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image)) {
				exit(json_encode(['status' => 2, 'content' => 'Ошибка загрузки файла!']));
			}
		} else {
			exit(json_encode(['status' => 2, 'content' => 'Изображение должено быть в формате JPG,GIF,BMP или PNG']));
		}

		exit(json_encode(['status' => 1, 'image' => $image]));
	}
}

exit(json_encode(['status' => 2]));