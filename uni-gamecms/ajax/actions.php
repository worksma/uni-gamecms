<?php
include_once "../inc/start.php";
include_once "../inc/protect.php";

if(empty($_POST['phpaction'])) {
	log_error("Прямой вызов actions.php");
	exit('Ошибка: [Прямой вызов инклуда]');
}

if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'], null))) {
	log_error("Неверный токен");
	exit('Ошибка: [Неверный токен]');
}

/* Авторизация админа
=========================================*/
if(isset($_POST['admin_login'])) {
	if(!validate_captcha($conf->captcha, $_POST["captcha"])) {
		exit('<p class="text-danger">Неверно введена капча!</p>');
	}
	
	$login    = check($_POST['login'], null);
	$password = check($_POST['password'], null);

	if(empty($login) or empty($password)) {
		exit('<p class="text-danger">Вы ввели не всю информацию!</p>');
	}

	$U = new Users($pdo);

	$ip = get_ip();

	$invalid_auths = $U->check_to_invalid_auth($ip);
	if($invalid_auths > 2) {
		log_error("Блокировка за неправильный ввод паролей (Сайт)");
		exit('<p class="text-danger">Вы заблокированы на 15 минут. Попробуйте позже.</p>');
	}

	$password = $U->convert_password($password, $conf->salt);

	$STH = $pdo->prepare("SELECT `id`, `rights`, `active`, `password`, `login`, `protect`, `protect`, `multi_account` FROM `users` WHERE `password`=:password AND `login`=:login LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':password' => $password, ':login' => $login));
	$user = $STH->fetch();

	if(empty($user->id)) {
		$invalid_auths = $U->up_invalid_auths($ip);

		log_error("Попытка: ".$invalid_auths."/3. Введеные данные неверны");
		exit('<p class="text-danger">Попытка: '.$invalid_auths.'/3. Введеные данные неверны.</p>');
	} else {
		if($invalid_auths) {
			$U->dell_invalid_auths($ip);
		}

		if($user->active != 1) {
			exit('<p class="text-danger">Пожалуйста, активируйте аккаунт, инструкция выслана на Ваш E-mail!</p>');
		}

		$U->auth_user($SC, $user->protect, $user->password, $user->login, $user->id, $user->rights, $user->multi_account);
		
		if(!is_worthy("h")) {
			log_error("Попытка авторизации пользователя без доступа.");
			exit('<p class="text-danger">
				У Вас нет доступа.
			</p>');
		}
		
		if(is_worthy("z")) {
			log_error("Попытка авторизации забаненного аккаунта");
			$SC->unset_user_session();

			exit('<p class="text-danger">Вы заблокированы на 15 минут. Попробуйте позже.</p>');
		}
		if(is_worthy("x")) {
			log_error("Попытка авторизации забаненного аккаунта (ip+cookies)");
			$SC->unset_user_session();

			$STH = $pdo->prepare("INSERT INTO `users__blocked` (`ip`) VALUES (:ip)");
			$STH->execute(array('ip' => $ip));
			$SC->set_cookie("point", "1");
			exit('<p class="text-danger">Вы заблокированы.</p>');
		}
		
		$_SESSION['admin']       = "yes";
		$_SESSION['admin_cache'] = $SC->get_admin_cache($password);
		$SC->set_user_cookie();
		
		write_log("Успешная авторизация в Админ Центре");
		exit("<script>reset_page();</script>");
	}
}

/* Авторизация пользователя
=========================================*/
if(isset($_POST['user_login'])) {
	$login    = check($_POST['login'], null);
	$password = check($_POST['password'], null);

	if(empty($login) or empty($password)) {
		exit('<p class="text-danger">Вы ввели не всю информацию!</p>');
	}

	$U = new Users($pdo);

	$ip = get_ip();

	$invalid_auths = $U->check_to_invalid_auth($ip);
	if($invalid_auths > 2) {
		log_error("Блокировка за неправильный ввод паролей (Сайт)");
		exit('<p class="text-danger">Вы заблокированы на 15 минут. Попробуйте позже.</p>');
	}

	$password = $U->convert_password($password, $conf->salt);

	$STH = $pdo->prepare("SELECT `id`, `rights`, `active`, `password`, `login`, `protect`, `protect`, `multi_account` FROM `users` WHERE `password`=:password AND `login`=:login LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':password' => $password, ':login' => $login));
	$user = $STH->fetch();

	if(empty($user->id)) {
		$invalid_auths = $U->up_invalid_auths($ip);

		log_error("Попытка: ".$invalid_auths."/3. Введеные данные неверны");
		exit('<p class="text-danger">Попытка: '.$invalid_auths.'/3. Введеные данные неверны.</p>');
	} else {
		if($invalid_auths) {
			$U->dell_invalid_auths($ip);
		}

		if($user->active != 1) {
			exit('<p class="text-danger">Пожалуйста, активируйте аккаунт, инструкция выслана на Ваш E-mail!</p>');
		}

		$U->auth_user($SC, $user->protect, $user->password, $user->login, $user->id, $user->rights, $user->multi_account);

		if(is_worthy("z")) {
			log_error("Попытка авторизации забаненного аккаунта");
			$SC->unset_user_session();

			exit('<p class="text-danger">Вы заблокированы на 15 минут. Попробуйте позже.</p>');
		}
		if(is_worthy("x")) {
			log_error("Попытка авторизации забаненного аккаунта (ip+cookies)");
			$SC->unset_user_session();

			$STH = $pdo->prepare("INSERT INTO `users__blocked` (`ip`) VALUES (:ip)");
			$STH->execute(array('ip' => $ip));
			$SC->set_cookie("point", "1");
			exit('<p class="text-danger">Вы заблокированы.</p>');
		}

		$SC->set_user_cookie();

		write_log("Успешная авторизация на сайте");
		exit("<script>reset_page();</script>");
	}
}

/* Регистрация нового пользователя
=========================================*/
if(isset($_POST['registration'])) {
	if(isset($config_additional['off_standart_registration'])) {
		exit();
	}

	if(!validate_captcha($conf->captcha, $_POST["captcha"])) {
		exit('<p class="text-danger">Неверно введена капча!</p>');
	}

	$login     = check($_POST['login'], null);
	$password  = check($_POST['password'], null);
	$password2 = check($_POST['password2'], null);
	$email     = check($_POST['email'], null);

	if(empty($login) or empty($password) or empty($password2) or empty($email)) {
		exit('<p class="text-danger">Вы ввели не всю информацию. Заполните все поля!</p>');
	}

	$U = new Users($pdo);

	if(!$U->check_to_flood($conf->captcha)) {
		exit('<p class="text-danger">Вы слишком часто регистрируете аккаунты!</p>');
	}
	if(!$U->check_login_lenght($login)) {
		exit('<p class="text-danger">Логин должен состоять не менее чем из 3 символов и не более чем из 30.</p>');
	}
	if(!$U->check_login_composition($login)) {
		exit('<p class="text-danger">В логине разрешается использовать только буквы и цифры.</p>');
	}
	if(!$U->check_login_busyness($login)) {
		exit('<p class="text-danger">Введеный Вами логин уже зарегистрирован!</p>');
	}

	if(!$U->check_password_lenght($password)) {
		exit('<p class="text-danger">Пароль должен состоять не менее чем из 6 символов и не более чем из 15.</p>');
	}
	if($password != $password2) {
		exit('<p class="text-danger">Введеные пароли не совпадают</p>');
	}
	$password = $U->convert_password($password, $conf->salt);

	if(!$U->check_email($email)) {
		exit('<p class="text-danger">Неверно введен E-mail!</p>');
	}
	if(!$U->check_email_busyness($email)) {
		exit('<p class="text-danger">Введеный Вами E-mail уже зарегистрирован!</p>');
	}

	if($U->entry_user($login, $password, $email, $conf->conf_us)) {
		$answer = $U->after_registration_actions($SC, $conf->salt, $conf->name, $login, $full_site_host);

		if($answer['message'] != 'error') {
			echo '<p class="text-success">'.$answer['message'].'</p>';
			sendmail($email, $answer['letter']['subject'], $answer['letter']['message'], $pdo);
		}

		exit();
	} else {
		exit('<p class="text-danger">Ошибка! Вы не зарегистрированы</p>');
	}
}

/* Восстановление пароля
=========================================*/
if(isset($_POST['send_new_pass'])) {
	if(!validate_captcha($conf->captcha, $_POST["captcha"])) {
		exit('<p class="text-danger">Неверно введена капча!</p>');
	}

	$email = check($_POST['email'], null);
	if(empty($email)) {
		exit('<p class="text-danger">Укажите E-mail!</p>');
	}

	$U = new Users($pdo);

	if(!$U->check_email($email)) {
		exit('<p class="text-danger">Неверно введен е-mail!</p>');
	}
	if($U->check_email_busyness($email)) {
		exit('<p class="text-danger">Введеный Вами E-mail не зарегистрирован!</p>');
	}

	$STH = $pdo->query("SELECT `id`, `email`, `login`, `password` FROM `users` WHERE email='$email' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$STH = $pdo->query("SELECT `url` FROM `pages` WHERE `name`='recovery' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$page_url = $STH->fetch();

	$link = $full_site_host.$page_url->url.'?a='.$row->id.'&data='.md5($row->id.$conf->salt.$row->password.$row->email.date("Y-m-d"));

	include_once "../inc/notifications.php";
	$letter = recovery_check_letter($conf->name, $row->login, $link);
	sendmail($row->email, $letter['subject'], $letter['message'], $pdo);

	write_log("Высслано письмо для восстановления пароля: ID".$row->id);
	exit('<p class="text-success">Мы выслали на Вашу почту('.$row->email.') ссылку для восстановления пароля, она будет действительна в течение текущих суток.</p>');
}
/* Сервера
=========================================*/
if(isset($_POST['get_servers'])) {
	$type = check($_POST['type'], "int");
	update_monitoring($pdo);
	$i = 0;

	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	if($type == 1) {
		$STH = $pdo->query("SELECT `monitoring`.*, `servers`.`rcon` FROM `monitoring` LEFT JOIN `servers` ON `monitoring`.`sid`=`servers`.`id` ORDER BY `monitoring`.`id`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
	} else {
		$STH = $pdo->query("SELECT * FROM `monitoring` ORDER BY `id`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
	}

	while($row = $STH->fetch()) {
		if($row->players_now > $row->players_max) {
			$row->players_now = $row->players_max;
		}
		if($row->players_max != 0) {
			$percentage = $row->players_now / $row->players_max * 100;
		} else {
			$percentage = 0;
		}
		if($percentage <= 25) {
			$color = 'info';
		} elseif($percentage <= 50) {
			$color = 'success';
		} elseif($percentage <= 75) {
			$color = 'warning';
		} elseif($percentage <= 100) {
			$color = 'danger';
		}
		if(($row->map != '0') and file_exists('../files/maps_imgs/'.$row->map.'.jpg')) {
			$map = '/files/maps_imgs/'.$row->map.'.jpg';
		} else {
			$map = '/files/maps_imgs/none.jpg';
		}
		if($row->map == '0') {
			$row->map = "Не определено";
		}
		if($row->name == '0') {
			$row->name = "Не определено";
		}

		if($row->type > 1) {
			$disp1 = 'disp-b';
			$disp2 = 'disp-n';
		} else {
			$disp1 = 'disp-n';
			$disp2 = 'disp-b';
		}
		$i++;
		if($type == 1) {
			$tpl->load_template('elements/server.tpl');
			$tpl->set("{rcon}", $row->rcon);
		} else {
			$tpl->load_template('elements/server_not_auth.tpl');
		}
		$tpl->set("{name}", $row->name);
		$tpl->set("{map_img}", $map);
		$tpl->set("{map_name}", $row->map);
		$tpl->set("{percentage}", $percentage);
		$tpl->set("{color}", $color);
		$tpl->set("{max}", $row->players_max);
		$tpl->set("{now}", $row->players_now);
		$tpl->set("{address}", $row->address);
		$tpl->set("{ip}", $row->ip);
		$tpl->set("{port}", $row->port);
		$tpl->set("{id}", $row->sid);
		$tpl->set("{disp1}", $disp1);
		$tpl->set("{disp2}", $disp2);
		$tpl->set("{site_host}", $site_host);
		$tpl->set("{template}", $conf->template);
		$tpl->set("{game}", $row->game);
		$tpl->set("{i}", $i);
		$tpl->compile('content');
		$tpl->clear();
	}
	$tpl->show($tpl->result['content']);
	$tpl->global_clear();

	if($type == 1) {
		if($i == 0) {
			exit('<span class="empty-element">Серверов нет</span>');
		}
	} else {
		if($i == 0) {
			exit('<tr><td colspan="10">Серверов нет</td></tr>');
		}
	}
	exit();
}
if(isset($_POST['get_md5'])) {
	exit(json_encode(array('answer' => md5($_POST['val']))));
}
if(isset($_POST['get_players'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}

	$STH = $pdo->query("SELECT `id`, `ip`, `port`, `rcon` FROM `servers` WHERE `id`='$id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$STH = $pdo->query("SELECT `mon_api`, `mon_key` FROM `config__secondary` LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$conf2 = $STH->fetch();
	if($conf2->mon_api == 1) {
		$players = @file_get_contents(
			getMonitoringUrl() . 'players-info.php?key=' . $conf2->mon_key
				. '&ip=' . $row->ip
				. '&port=' . $row->port
				. '&version=2'
		);
		if(isset($players) and ($players != '403')) {
			$players = unserialize($players);
		} else {
			$players = 0;
		}
	} else {
		try {
			$SQ = new SourceQuery;
			$SQ->Connect($row->ip, $row->port);
			$players = $SQ->GetPlayers();
			$SQ->Disconnect();
		} catch(Exception $e) {
			$players = 0;
		}
	}
	$i= 0;
	if ($players){
		$GD = new GetData($pdo);

		foreach($players as $player) {
			$i++;

			$name = htmlspecialchars($player['Name'], ENT_QUOTES);
			$player_name = $name;


			if($player_profile = $GD->get_gamer_profile($player['Name'], '', 1)) {
				$player_name = $player_profile;
			}

			if ($row->rcon == 1 && isset($_SESSION['id']) && is_worthy_specifically("s", $row->id)) {
				$player_id = $row->id;
				$operations = "
				<td>
					<button type='button' class='btn btn-default btn-sm' onclick='abort_player(1, \"$name\", $player_id);'>Кик</button>
					<button type='button' class='btn btn-default btn-sm' onclick='abort_player(2, \"$name\", $player_id);'>Бан</button>
				</td>";
			} else {
				$operations = '';
			}

			echo "
			<tr>
				<td>".$i."</td>
				<td>".$player_name."</td>
				<td>".intval($player['Frags'])."</td>
				<td>".expand_seconds2($player['Time'])."</td>
				".$operations."
			</tr>";
		}
	} else {
		exit('<tr><td colspan="10">Игроков нет</td></tr>');
	}
	exit();
}
/* Услуги пользователя
=========================================*/
if(isset($_POST['get_admin_info'])) {
	$id = check($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}

	$i        = 0;
	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$STH = $pdo->prepare("SELECT `admins__services`.`id`, `services`.`name`, `admins__services`.`service`, `admins__services`.`bought_date`, `admins__services`.`ending_date`
		FROM `admins__services` LEFT JOIN `services` ON `admins__services`.`service` = `services`.`id` WHERE `admins__services`.`admin_id` = :admin_id");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':admin_id' => $id));
	while($row = $STH->fetch()) {
		$i++;
		if(!empty($row->service)) {
			$name = $row->name;
		} else {
			$name = 'Неизвестно';
		}
		if($row->ending_date == '0000-00-00 00:00:00') {
			$left        = "Вечность";
			$color       = "success";
			$ending_date = 'Никогда';
		} else {
			$left = strtotime($row->ending_date) - time();
			if($left > 60 * 60 * 24 * 5) {
				$color = "success";
			} elseif($left > 60 * 60 * 24) {
				$color = "warning";
			} else {
				$color = "danger";
			}
			$left        = expand_seconds2($left, 2);
			$ending_date = expand_date($row->ending_date, 1);
		}
		if($row->bought_date != '0000-00-00 00:00:00') {
			$bought_date = expand_date($row->bought_date, 1);
		} else {
			$bought_date = 'Неизвестно';
		}
		$tpl->load_template('elements/admin_info.tpl');
		$tpl->set("{i}", $i);
		$tpl->set("{name}", $name);
		$tpl->set("{bought_date}", $bought_date);
		$tpl->set("{ending_date}", $ending_date);
		$tpl->set("{left}", $left);
		$tpl->set("{color}", $color);
		$tpl->compile('content');
		$tpl->clear();
	}
	$tpl->show($tpl->result['content']);
	$tpl->global_clear();
	exit();
}
/* Новости
=========================================*/
if(isset($_POST['load_new_comments'])) {
	$id = checkJs($_POST['id'], "int");
	$i  = 0;

	$tpl                    = new Template;
	$tpl->dir               = '../templates/'.$conf->template.'/tpl/';
	$tpl->result['content'] = '';
	$STH                    = $pdo->query("SELECT news__comments.*, users.login, users.avatar, users.rights FROM news__comments LEFT JOIN users ON news__comments.user_id = users.id WHERE new_id = '$id' ORDER BY id DESC");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$date = expand_date($row->date, 8);

		if(is_worthy("q")) {
			$dell = '<span onclick="dell_new_comment('.$row->id.');" tooltip="yes" data-placement="left" title="Удалить" class="m-icon icon-trash dell_message"></span>';
		} else {
			$dell = '';
		}
		$i++;

		$gp = $users_groups[$row->rights];
		$tpl->load_template('elements/comment.tpl');
		$tpl->set("{id}", $row->id);
		$tpl->set("{user_id}", $row->user_id);
		$tpl->set("{login}", $row->login);
		$tpl->set("{avatar}", $row->avatar);
		$tpl->set("{text}", $row->text);
		$tpl->set("{dell}", $dell);
		$tpl->set("{date_full}", $date['full']);
		$tpl->set("{date_short}", $date['short']);
		$tpl->set("{gp_color}", $gp['color']);
		$tpl->set("{gp_name}", $gp['name']);
		$tpl->compile('content');
		$tpl->clear();
	}
	if($i == 0) {
		echo '<span class="empty-element">Комментариев нет</span>';
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
/* Стена
=========================================*/
if(isset($_POST['load_users_comments'])) {
	$id       = checkJs($_POST['id'], "int");
	$load_val = checkJs($_POST['load_val'], "int");
	if(empty($load_val)) {
		$load_val = 1;
	}

	$tpl                    = new Template;
	$tpl->dir               = '../templates/'.$conf->template.'/tpl/';
	$start                  = ($load_val - 1) * 20;
	$end                    = 20;
	$i                      = $start;
	$i2                     = 0;
	$tpl->result['content'] = '';
	$STH                    = $pdo->query("SELECT users__comments.*, users.login, users.avatar, users.rights FROM users__comments LEFT JOIN users ON users__comments.author = users.id WHERE user_id = '$id' ORDER BY id DESC LIMIT ".$start.", ".$end);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$date = expand_date($row->date, 8);
		if((isset($_SESSION['id']) and $_SESSION['id'] == $id) or (is_worthy("y"))) {
			$dell = '<span onclick="dell_user_comment('.$row->id.');" tooltip="yes" data-placement="left" title="Удалить" class="m-icon icon-trash dell_message"></span>';
		} else {
			$dell = '';
		}
		$i++;
		$i2++;
		$gp = $users_groups[$row->rights];

		$tpl->load_template('elements/comment.tpl');
		$tpl->set("{gp_color}", $gp['color']);
		$tpl->set("{gp_name}", $gp['name']);
		$tpl->set("{id}", $row->id);
		$tpl->set("{user_id}", $row->author);
		$tpl->set("{login}", $row->login);
		$tpl->set("{avatar}", $row->avatar);
		$tpl->set("{text}", $row->text);
		$tpl->set("{dell}", $dell);
		$tpl->set("{date_full}", $date['full']);
		$tpl->set("{date_short}", $date['short']);
		$tpl->compile('content');
		$tpl->clear();
	}

	$tpl->show($tpl->result['content']);
	$tpl->global_clear();

	if(($load_val > 0) and ($i2 > 19)) {
		$load_val++;
		exit ('<div id="loader'.$load_val.'"><span class="empty-element" onclick="load_users_comments(\''.$id.'\',\''.$load_val.'\');">Подгрузить записи</span></div>');
	}
	if($start == 0 and $i2 == 0) {
		exit ('<div id="loader'.$load_val.'"><span class="empty-element">Сообщений не найдено</span></div>');
	}
	if(($load_val > 0) and ($i2 < 20)) {
		exit ();
	}
	exit();
}
/* Пользователи
=========================================*/
if(isset($_POST['search_login'])) {
	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$GD = new GetData($pdo, $tpl);

	if(empty($_POST['login'])) {
		$tpl->show($GD->users($_POST['start'], $_POST['group']));
	} else {
		$tpl->show($GD->search_login($_POST['login'], $_POST['group']));
	}
	$tpl->global_clear();
	exit();
}
/* Баны
=========================================*/
if(isset($_POST['load_ban_comments'])) {
	$id = checkJs($_POST['id'], "int");
	$i  = 0;

	$tpl                    = new Template;
	$tpl->dir               = '../templates/'.$conf->template.'/tpl';
	$tpl->result['content'] = '';
	$STH                    = $pdo->query("SELECT `bans__comments`.*, `bans`.`server`, `users`.`login`, `users`.`avatar`, `users`.`rights` FROM `bans__comments` 
		LEFT JOIN `users` ON `bans__comments`.`user_id` = `users`.`id` 
		LEFT JOIN `bans` ON `bans__comments`.`ban_id` = `bans`.`id` 
		WHERE `bans__comments`.`ban_id` = '$id' ORDER BY `bans__comments`.`id` DESC");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$tpl->load_template('/elements/comment.tpl');

		$gp   = $users_groups[$row->rights];
		$date = expand_date($row->date, 8);
		if(is_worthy_specifically("u", $row->server)) {
			$dell = '<span onclick="dell_ban_comment('.$row->id.');" tooltip="yes" data-placement="left" title="Удалить" class="m-icon icon-trash dell_message"></span>';
		} else {
			$dell = '';
		}
		$i++;

		$tpl->set("{id}", $row->id);
		$tpl->set("{user_id}", $row->user_id);
		$tpl->set("{login}", $row->login);
		$tpl->set("{avatar}", $row->avatar);
		$tpl->set("{text}", $row->text);
		$tpl->set("{dell}", $dell);
		$tpl->set("{date_full}", $date['full']);
		$tpl->set("{date_short}", $date['short']);
		$tpl->set("{gp_color}", $gp['color']);
		$tpl->set("{gp_name}", $gp['name']);
		$tpl->compile('content');
		$tpl->clear();
	}
	if($i == 0) {
		echo '<span class="empty-element">Комментариев нет</span>';
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
if(isset($_POST['search_ban'])) {
	$bid    = $_POST['ban'];
	$server = checkJs($_POST['server'], null);
	if(empty($bid) or empty($server)) {
		exit();
	}

	$STH = $pdo->query("SELECT id,ip,port,name,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE type!=0 and type!=1 and id='$server'");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$STH = $pdo->query("SELECT `price1`, `price2`, `price3` FROM `config__prices`");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$bans_conf = $STH->fetch();

	$db_host     = $row->db_host;
	$db_user     = $row->db_user;
	$db_pass     = $row->db_pass;
	$db_db       = $row->db_db;
	$db_prefix   = $row->db_prefix;
	$address     = $row->ip.':'.$row->port;
	$ip          = $row->ip;
	$port        = $row->port;
	$type        = $row->type;
	$server_name = $row->name;
	if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
		exit('<p>'.$massages['Unable_connect_to_db'].'</p>');
	}
	set_names($pdo2, $row->db_code);

	if($type == '2' || $type == '3' || $type == '5') {
		$table = set_prefix($db_prefix, 'bans');
		$STH = $pdo2->prepare("SELECT * FROM $table WHERE server_ip = '$address' and bid=:bid LIMIT 1");
		$STH->execute(array(':bid' => $bid));
	} else {
		$table = set_prefix($db_prefix, 'servers');
		$STH   = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row    = $STH->fetch();
		$sid    = $row->sid;
		$table1 = set_prefix($db_prefix, 'bans');
		$table2 = set_prefix($db_prefix, 'admins');
		$STH    = $pdo2->prepare("SELECT $table1.bid,$table1.ip AS player_ip,$table1.RemoveType AS expired,$table1.authid AS player_id,$table1.name AS player_nick,$table1.created AS ban_created,$table1.length AS ban_length,$table1.reason AS ban_reason,$table1.adminip AS admin_ip,$table2.user AS admin_nick,$table2.nick AS admin_nick2,$table2.authid AS admin_id FROM $table1 LEFT JOIN $table2 ON $table1.aid = $table2.aid WHERE ($table1.sid = '$sid' OR $table1.sid = '0') and $table1.bid=:bid LIMIT 1");
		$STH->execute(array(':bid' => $bid));
	}
	$result = $STH->fetchAll();

	$disp  = "";
	$price = 0;
	if(!isset($result['0']['admin_nick2'])) {
		$result['0']['admin_nick2'] = null;
	}
	$admin_nick = get_ban_admin_nick($result['0']['admin_nick'], $result['0']['admin_nick2'], $server_name, $type);

	$player_nick = check($result['0']['player_nick'], null);
	$ban_reason  = check($result['0']['ban_reason'], null);

	if($type == '2' || $type == '3' || $type == '5') {
		$ban_length = $result['0']['ban_length'] * 60;
	} else {
		$ban_length = $result['0']['ban_length'];
	}
	$ban_length2 = expand_seconds2($ban_length);
	$ban_created = $result['0']['ban_created'];
	if($result['0']['expired'] == 1 or $result['0']['expired'] == "E" or $result['0']['expired'] == "U") {
		$color = "success";
		$time  = expand_date(date("Y-m-d H:i:s", ($ban_created + $ban_length)), 1);
	} else {
		if($ban_length == 0) {
			$time  = "Никогда";
			$color = "danger";
			$price = $bans_conf->price3;
		} else {
			$now  = time();
			$time = expand_date(date("Y-m-d H:i:s", ($ban_created + $ban_length)), 1);
			if(($ban_created + $ban_length) < $now) {
				$color = "success";
			} else {
				$color = "";
				$date  = diff_date(date("Y-m-d H:i:s", ($ban_created + $ban_length)), date("Y-m-d H:i:s"));
				if($date['2'] < '7' and $date['1'] == '0' and $date['0'] == '0') {
					$price = $bans_conf->price1;
				} else {
					$price = $bans_conf->price2;
				}
			}
		}
	}

	if(empty($result['0']['bid'])) {
		exit('<p>Бан не найден</p>');
	} else {
		$tpl      = new Template;
		$tpl->dir = '../templates/'.$conf->template.'/tpl/';

		$tpl->load_template('elements/search_ban.tpl');
		$tpl->set("{bid}", $result['0']['bid']);
		$tpl->set("{player_ip}", $result['0']['player_ip']);
		$tpl->set("{player_id}", $result['0']['player_id']);
		$tpl->set("{player_nick}", $player_nick);
		$tpl->set("{admin_ip}", $result['0']['admin_ip']);
		$tpl->set("{admin_id}", $result['0']['admin_id']);
		$tpl->set("{admin_nick}", $admin_nick);
		$tpl->set("{ban_reason}", $result['0']['ban_reason']);
		$tpl->set("{color}", $color);
		$tpl->set("{time}", $time);
		$tpl->set("{ban_length}", $ban_length2);
		$tpl->set("{address}", $address);
		$tpl->set("{server_name}", $server_name);
		$tpl->compile('content');
		$tpl->clear();

		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
if(isset($_POST['load_stats'])) {
	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$GD = new GetData($pdo, $tpl);
	if(empty($_POST['name'])) {
		$tpl->show($GD->stats($_POST['start'], $_POST['server']));
	} else {
		$tpl->show($GD->stats($_POST['start'], $_POST['server'], 0, $_POST['name']));
	}

	$tpl->global_clear();
	exit();
}
if(isset($_POST['load_wstats'])) {
	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$GD = new GetData($pdo, $tpl);
	$tpl->show($GD->weapon_stats($_POST['server'], $_POST['authid']));
	$tpl->global_clear();
	exit();
}
if(isset($_POST['load_mstats'])) {
	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$GD = new GetData($pdo, $tpl);
	$tpl->show($GD->map_stats($_POST['server'], $_POST['authid']));
	$tpl->global_clear();
	exit();
}
if(isset($_POST['load_banlist'])) {
	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$GD = new GetData($pdo, $tpl);
	if(empty($_POST['name'])) {
		$tpl->show($GD->banlist($_POST['start'], $_POST['server']));
	} else {
		$tpl->show($GD->banlist($_POST['start'], $_POST['server'], 0, $_POST['name']));
	}

	$tpl->global_clear();
	exit();
}
if(isset($_POST['load_muts'])) {
	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$GD = new GetData($pdo, $tpl);
	if(empty($_POST['name'])) {
		$tpl->show($GD->mutlist($_POST['start'], $_POST['server']));
	} else {
		$tpl->show($GD->mutlist($_POST['start'], $_POST['server'], 0, $_POST['name']));
	}

	$tpl->global_clear();
	exit();
}
if(isset($_POST['get_services'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}
	$i    = 0;
	$data = '';
	$service = 0;

	$STH  = $pdo->query("SELECT id,name,rights,sale FROM services WHERE server = '$id' ORDER BY trim");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if($row->sale != 2) {
			if($i == 0) {
				$service = $row->id;
				$i++;
			}
			$data .= '<option value="'.$row->id.'">'.$row->name.'</option>';
		}
	}

	exit(json_encode(array('status' => '1', 'data' => $data, 'service' => $service)));
}
if(isset($_POST['get_tarifs'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}

	$STH = $pdo->query("SELECT `services`.`text`, `services`.`discount` AS `service_dicount`,`servers`.`discount` FROM `services` LEFT JOIN `servers` ON `services`.`server`=`servers`.`id` WHERE `services`.`id` = '$id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row  = $STH->fetch();
	$text = $row->text;

	$service_discount = $row->service_dicount;
	$server_discount  = $row->discount;

	$STH = $pdo->query("SELECT discount FROM config__prices LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$disc     = $STH->fetch();
	$discount = $disc->discount;

	$data = '';
	$STH  = $pdo->query("SELECT id,pirce,time,discount FROM services__tarifs WHERE service = '$id' ORDER BY pirce");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if($row->time == 0) {
			$time = 'Навсегда';
		} else {
			$time = $row->time.' дня(ей)';
		}

		if(isset($user->proc)) {
			$user_proc = $user->proc;
		} else {
			$user_proc = 0;
		}

		$proc  = calculate_discount($server_discount, $discount, $user_proc, $service_discount, $row->discount);
		$pirce = calculate_pirce($row->pirce, $proc);

		if($pirce != $row->pirce) {
			$data .= '<option value="'.$row->id.'">'.$time.' - '.$pirce.' '.$messages['RUB'].' (с учетом скидки в '.$proc.'%)</option>';
		} else {
			$data .= '<option value="'.$row->id.'">'.$time.' - '.$pirce.' '.$messages['RUB'].'</option>';
		}
	}
	exit(json_encode(array('status' => '1', 'data' => $data, 'text' => $text)));
}
if(isset($_POST['get_server_store'])) {
	$id   = checkJs($_POST['id'], "int");
	$type = checkJs($_POST['type'], "int");

	if(empty($id)) {
		exit ();
	}
	if(empty($type)) {
		$type = 0;
	}

	$STH = $pdo->query("SELECT `monitoring`.*, `servers`.`rcon`,`servers`.`binds`,`servers`.`type` FROM `monitoring` LEFT JOIN `servers` ON `monitoring`.`sid`=`servers`.`id` WHERE `monitoring`.`sid`='$id'");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		$STH = $pdo->prepare("SELECT `id`,`ip`,`port`,`name`,`address`,`rcon`,`game`,`binds`,`type` FROM `servers` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $id));
		$row              = $STH->fetch();
		$row->map         = 0;
		$row->name        = '0';
		$row->players_max = 0;
		$row->players_now = 0;
		$row->sid         = $row->id;
	}

	if($row->type == 0 || $row->type == 1) {
		$disp = 'disp-n';
	} else {
		$disp = '';
	}

	if(($row->map != '0') and file_exists('../files/maps_imgs/'.$row->map.'.jpg')) {
		$map = '/files/maps_imgs/'.$row->map.'.jpg';
	} else {
		$map = '/files/maps_imgs/none.jpg';
	}
	if($row->map == '0') {
		$row->map = "Не определено";
	}
	if($row->name == '0') {
		$row->name = "Не определено";
	}

	$tpl      = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$tpl->load_template('elements/server_min.tpl');
	$tpl->set("{name}", $row->name);
	$tpl->set("{map_img}", $map);
	$tpl->set("{map_name}", $row->map);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{max}", $row->players_max);
	$tpl->set("{now}", $row->players_now);
	$tpl->set("{ip}", $row->ip);
	$tpl->set("{port}", $row->port);
	$tpl->set("{id}", $row->sid);
	$tpl->set("{disp}", $disp);
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{address}", $row->address);
	$tpl->set("{rcon}", $row->rcon);
	$tpl->compile('content');
	$tpl->clear();

	if($type == 1) {
		$binds = explode(';', $row->binds);

		$binds_data = '';
		if($binds[0]) {
			$binds_data .= "$('#store_type option[value=\"1\"]').removeAttr('disabled');";
			$binds_data .= "$('#store_type option[value=\"1\"]').attr('class', 'disp-b');";
		} else {
			$binds_data .= "$('#store_type option[value=\"1\"]').attr('disabled', '');";
			$binds_data .= "$('#store_type option[value=\"1\"]').attr('class', 'disp-n');";
		}
		if($binds[1]) {
			$binds_data .= "$('#store_type option[value=\"2\"]').removeAttr('disabled');";
			$binds_data .= "$('#store_type option[value=\"2\"]').attr('class', 'disp-b');";
		} else {
			$binds_data .= "$('#store_type option[value=\"2\"]').attr('disabled', '');";
			$binds_data .= "$('#store_type option[value=\"2\"]').attr('class', 'disp-n');";
		}
		if($binds[2]) {
			$binds_data .= "$('#store_type option[value=\"3\"]').removeAttr('disabled');";
			$binds_data .= "$('#store_type option[value=\"3\"]').attr('class', 'disp-b');";
		} else {
			$binds_data .= "$('#store_type option[value=\"3\"]').attr('disabled', '');";
			$binds_data .= "$('#store_type option[value=\"3\"]').attr('class', 'disp-n');";
		}

		$tpl->result['content'] .= "<script>change_store_bind_type(0);".$binds_data."</script>";
	}

	$tpl->show($tpl->result['content']);
	$tpl->global_clear();
}