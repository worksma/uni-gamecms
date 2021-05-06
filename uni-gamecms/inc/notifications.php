<?php
function reg_letter($site_name, $login) {
	$letter['subject'] = "Регистрация на ".$site_name;
	$letter['message'] = "Здравствуйте, ".$login.". Спасибо за регистрацию на ".$site_name." \nВаш логин: ".$login."\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}
function reg_letter_with_key($site_name, $login, $link) {
	$letter['subject'] = "Регистрация на ".$site_name;
	$letter['message'] = "Здравствуйте, ".$login.". Спасибо за регистрацию на ".$site_name." \nВаш логин: ".$login."\nПожалуйста, активируйте свой аккаунт, перейдя по ссылке: ".$link."\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function recovery_check_letter($site_name, $login, $link) {
	$letter['subject'] = "Восстановление пароля на ".$site_name;
	$letter['message'] = "Здравствуйте, " . $login . ". Мы сгенерировали для Вас ссылку, пройдя по которой, Вы можете восстановить пароль. \nСсылка: " . $link . "\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}
function recovery_letter($site_name, $login, $password) {
	$letter['subject'] = "Восстановление пароля на ".$site_name;
	$letter['message'] = "Здравствуйте, " . $login . ". Мы сгенерировали Вам новый пароль для авторизации. \nЛогин: " . $login . "\nПароль: " . $password . "\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function letter_byadmin($site_name, $text) {
	$letter = $text."\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function dell_user_letter($site_name, $login) {
	$letter['subject'] = "Ваш аккаунт удален с ".$site_name;
	$letter['message'] = "Здравствуйте, ".$login.". Ваш аккаунт удален за задержку с активацией\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function letter_of_ending_service($site_name, $left, $name, $service_name, $server_name) {
	$letter['subject'] = "Продлите услугу на ".$site_name;
	$letter['message'] = "До окончания срока действия Вашей услуги \"".$service_name."\" с идентификатором ".$name." на сервере ".$server_name." осталось ".$left."! Пожалуйста, продлите!\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function letter_of_dell_service($site_name, $name, $service_name, $server_name) {
	$letter['subject'] = "Ваша услуга удалена";
	$letter['message'] = "Ваша услуга \"".$service_name."\" с идентификатором ".$name." на сервере ".$server_name." удалена.\nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function letter_of_change_login($site_name, $login) {
	$letter['subject'] = "Ваш логин изменен";
	$letter['message'] = "Ваш логин изменен на: ".$login." \nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function letter_of_change_password($site_name, $password) {
	$letter['subject'] = "Ваш пароль изменен";
	$letter['message'] = "Ваш пароль изменен на: ".$password." \nС уважением,\nАдминистрация ".$site_name;
	return $letter;
}

function letter_of_new_ban($site_name) {
	$letter['subject'] = "Оставлена новая заявка на разбан";
	$letter['message'] = "На администрируемом Вами сайте \"".$site_name."\" оставлена новая заявка на разбан.";
	return $letter;
}

function letter_of_new_ticket($site_name) {
	$letter['subject'] = "Открыт новый тикет";
	$letter['message'] = "На администрируемом Вами сайте \"".$site_name."\" открыт новый тикет.";
	return $letter;
}

function letter_of_ban_answer($id, $full_site_host) {
	$letter['subject'] = "Оставлен ответ в заявке на разбан";
	$letter['message'] = "В Вашей заявке на разбан (".$full_site_host."bans/ban?id=".$id.") оставлен ответ";
	return $letter;
}

function close_ban_letter($id, $full_site_host) {
	$letter['subject'] = "Ваша заявка на разбан рассмотрена";
	$letter['message'] = "Ваша заявка на разбан (".$full_site_host."bans/ban?id=".$id.") рассмотрена";
	return $letter;
}

function letter_of_ticket_answer($id, $full_site_host) {
	$letter['subject'] = "Оставлен ответ в тикете";
	$letter['message'] = "В Вашем тикете (".$full_site_host."support/ticket?id=".$id.") оставлен ответ";
	return $letter;
}

function close_ticket_letter($id, $full_site_host) {
	$letter['subject'] = "Ваша тикет рассмотрен и закрыт";
	$letter['message'] = "Ваш тикет (".$full_site_host."support/ticket?id=".$id.") рассмотрен и закрыт";
	return $letter;
}

function send_noty($pdo, $message, $user_id, $type) {
	if($user_id == 0) {
		$STH = $pdo->query("SELECT `admins_ids` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$user_ids = explode(",", $row->admins_ids);
		$ids_count = count($user_ids);
			for ($i=0; $i < $ids_count; $i++) {
				$STH = $pdo->prepare("INSERT INTO notifications (message,date,user_id,type) values (:message, :date, :user_id, :type)");
				$STH->execute(array( 'message' => $message, 'date' => date("Y-m-d H:i:s"), 'user_id' => $user_ids[$i], 'type' => $type ));
			}
	} else {
		$STH = $pdo->prepare("INSERT INTO notifications (message,date,user_id,type) values (:message, :date, :user_id, :type)");
		$STH->execute(array( 'message' => $message, 'date' => date("Y-m-d H:i:s"), 'user_id' => $user_id, 'type' => $type ));
	}
}

function welcome_noty($pdo, $login, $user_id) {
	$message = "Здравствуйте, ".$login.", поздравляем Вас с успешной регистрацией!";
	send_noty($pdo, $message, $user_id, 2);
}

function noty_of_ending_service($left, $name, $service_name, $server_name) {
	$noty = "До окончания срока действия Вашей услуги <b>".$service_name."</b> с идентификатором <b>".$name."</b> на сервере <b>".$server_name."</b> осталось, <b>".$left."</b>! Пожалуйста, <a href='../my_stores'>продлите</a>!";
	return $noty;
}

function noty_of_dell_service($name, $service_name, $server_name) {
	$noty = "Ваша услуга <b>".$service_name."</b> с идентификатором <b>".$name."</b> на сервере <b>".$server_name."</b> удалена";
	return $noty;
}

function noty_of_ban_answer($id) {
	$noty = "В вашей заявке <a href='../bans/ban?id=".$id."'>#".$id."</a> оставлен ответ";
	return $noty;
}

function close_ban_noty($id) {
	$noty = "Ваша заявка <a href='../bans/ban?id=".$id."'>#".$id."</a> рассмотрена";
	return $noty;
}

function close_ticket_noty($id) {
	$noty = "Ваш тикет <a href='../support/ticket?id=".$id."'>#".$id."</a> закрыт";
	return $noty;
}

function noty_of_ticket_answer($id) {
	$noty = "Оставлен ответ в вашем тикете <a href='../support/ticket?id=".$id."'>#".$id."</a>";
	return $noty;
}

function success_buy_noty($name, $pass, $time, $date, $server_name, $address, $service_name, $pass_prifix) {
	$noty = "Поздравляем Вас с успешной покупкой прав!<br>";
	$noty .= "• Ваш идентификатор: <b>".$name."</b><br>";
	if (!empty($pass)) {
		$noty .= "• Ваш пароль: <b>".$pass."</b><br>";
		$noty .= "• Для входа на сервер введите в консоль: <b>setinfo ".$pass_prifix." \"".$pass."\"</b><br>";
	}
	if ($time != 0) {
		$date = expand_date($date, 2);
		$noty .= "• Ваша услуга заканчивается <b>".$date."</b><br>";
	} else {
		$noty .= "• Ваша услуга куплена на <b>неограниченное время</b><br>";
	}
	$noty .= "• Сервер: <b>".$server_name." - ".$address ."</b><br>";
	$noty .= "• Услуга: <b>".$service_name."</b><br>";
	$noty .= "• Продлевать и настраивать купленные права Вы можете в <a href='../my_stores'>разделе управления услугами</a>";
	return $noty;
}

function success_buy_noty_for_admin($id, $login, $time, $date, $server_name, $address, $service_name) {
	$noty = "Куплены права пользователем: <a href='../profile?id=".$id."'>".$login."</a><br>";
	if ($time != 0) {
		$date = expand_date($date, 2);
		$noty .= "Услуга заканчивается <b>".$date."</b><br>";
	} else {
		$noty .= "Услуга куплена на <b>неограниченное время</b><br>";
	}
	$noty .= "Сервер: <b>".$server_name." - ".$address ."</b><br>";
	$noty .= "Услуга: <b>".$service_name."</b><br>";
	return $noty;
}

function success_buy_unban_noty($ban_nick, $ban_ip, $ban_steamid, $ban_id) {
	$noty = "Поздравляем Вас с успешной покупкой разбана для:<br>";
	$noty .= "Nick: <b>".$ban_nick."</b><br>";
	$noty .= "IP: <b>".$ban_ip."</b><br>";
	$noty .= "STEAM ID: <b>".$ban_steamid."</b><br>";
	$noty .= "ID бана: <b>".$ban_id."</b><br>";
	return $noty;
}

function success_buy_unban_noty_for_admin($id, $login, $ban_nick, $ban_ip, $ban_steamid, $ban_id) {
	$noty = "Куплен разбан пользователем: <a href='../profile?id=".$id."'>".$login."</a><br>";
	$noty .= "Nick: <b>".$ban_nick."</b><br>";
	$noty .= "IP: <b>".$ban_ip."</b><br>";
	$noty .= "STEAM ID: <b>".$ban_steamid."</b><br>";
	$noty .= "ID бана: <b>".$ban_id."</b><br>";
	return $noty;
}

function success_buy_unmute_noty($ban_nick, $ban_steamid, $ban_id) {
	$noty = "Поздравляем Вас с успешной покупкой размута для:<br>";
	$noty .= "Nick: <b>".$ban_nick."</b><br>";
	$noty .= "STEAM ID: <b>".$ban_steamid."</b><br>";
	$noty .= "ID мута: <b>".$ban_id."</b><br>";
	return $noty;
}

function success_buy_unmute_noty_for_admin($id, $login, $ban_nick, $ban_steamid, $ban_id) {
	$noty = "Куплен размут пользователем: <a href='../profile?id=".$id."'>".$login."</a><br>";
	$noty .= "Nick: <b>".$ban_nick."</b><br>";
	$noty .= "STEAM ID: <b>".$ban_steamid."</b><br>";
	$noty .= "ID мута: <b>".$ban_id."</b><br>";
	return $noty;
}

function unlock_service_noty($adm_name, $server_name) {
	$noty = "Ваша услуга с идентификатором <b>".$adm_name."</b> на сервере <b>".$server_name."</b> разблокирована!";
	return $noty;
}

function unlock_service_noty_for_admin($id, $login, $adm_name, $server_name) {
	$noty = "Пользователь <a href='../profile?id=".$id."'>".$login."</a> разблокировал услугу с идентификатором <b>".$adm_name."</b> на сервере <b>".$server_name."</b>.";
	return $noty;
}

function change_days_noty($name, $service_name, $server_name, $date) {
	$noty['message'] = "Вашей услуге <b>".$service_name."</b> с идентификатором <b>".$name."</b> на сервере <b>".$server_name."</b> установили срок окончания: <b>".$date."</b>";
	$noty['type'] = 2;
	return $noty;
}

function buy_extend_noty($name, $service_name, $server_name, $date) {
	$noty['message'] = "Услуга <b>".$service_name."</b> с идентификатором <b>".$name."</b> на сервере <b>".$server_name."</b> успешно продлена до: <b>".$date."</b>";
	$noty['type'] = 2;
	return $noty;
}

function block_service_noty($adm_name, $server_name, $cause, $pirce, $link) {
	$noty = "Ваша услуга с идентификатором <b>".$adm_name."</b> на сервере <b>".$server_name."</b> заблокирована!<br>";
	$noty .= "Причина: <b>".$cause."</b><br>";
	$noty .= "Цена разблокировки: <a href='../my_stores'><b>".$pirce."р</b></a> <br>";
	if (!empty($link)) {
		$noty .= "Доказательства: <a href='".$link."'><b>ссылка</b></a><br>";
	}
	return $noty;
}

function give_service_noty($name, $pass, $time, $date, $pass_prifix) {
	$noty = "Вам выдана услуга!<br>";
	$noty .= "Ваш идентификатор: <b>".$name."</b><br>";
	if (!empty($pass)) {
		$noty .= "Ваш пароль: <b>".$pass."</b><br>";
		$noty .= "Для входа на сервер введите в консоль: <b>setinfo ".$pass_prifix." \"".$pass."\"</b><br>";
	}
	if ($time != 0) {
		$date = expand_date($date, 2);
		$noty .= "Ваша услуга заканчивается <b>".$date."</b><br>";
	} else {
		$noty .= "Ваша услуга выдана на <b>неограниченное время</b><br>";
	}
	$noty .= "Продлевать и настраивать права Вы можете в <a href='../my_stores'>разделе управления услугами</a><br>";
	return $noty;
}

function give_service_noty_for_admin($id, $login, $name, $time, $date, $server_name, $address, $service_name) {
	$noty = "Выданы права пользователю: <a href='../profile?id=".$id."'>".$login."</a><br>";
	$noty .= "Идентификатор: <b>".$name."</b><br>";
	$noty .= "Сервер: <b>".$server_name." - ".$address ."</b><br>";
	$noty .= "Услуга: <b>".$service_name."</b><br>";
	if ($time != 0) {
		$date = expand_date($date, 2);
		$noty .= "Услуга заканчивается <b>".$date."</b><br>";
	} else {
		$noty .= "Услуга выдана на <b>неограниченное время</b><br>";
	}

	return $noty;
}

function give_proc_noty($id, $login, $proc) {
	$noty = "Администратор <a href='../profile?id=".$id."'>".$login."</a> установил Вам скидку в размере <b>".$proc."%</b>";
	return $noty;
}

function change_group_noty($name, $rights) {
	$noty = "Ваша группа изменена!<br>";
	$noty .= "Название группы: <b>".$name."</b><br>";
	$noty .= "Флаги: <b>".$rights."</b><br>";
	return $noty;
}

function success_activete_voucher($sum) {
	$noty = "Поздравляем Вас с успешной активацией ваучера на сумму <b>".$sum."руб.</b>";
	return $noty;
}

function success_activete_voucher_for_admin($id, $login, $sum, $key) {
	$noty = "Активирован ваучер на сумму <b>".$sum."руб</b> (код: ".$key.") пользователем: <a href='../profile?id=".$id."'>".$login."</a>";
	return $noty;
}

function pause_service_noty($adm_name, $server_name) {
	$noty = "Ваша услуга с идентификатором <b>".$adm_name."</b> на сервере <b>".$server_name."</b> приостановлена!<br>";
	$noty .= "Обратитесь к главному администратору для ее активации, когда Вам это потребуется.";
	return $noty;
}

function resume_service_noty($adm_name, $server_name) {
	$noty = "Ваша услуга с идентификатором <b>".$adm_name."</b> на сервере <b>".$server_name."</b> активирована!";
	return $noty;
}

function new_referal($id, $login) {
	$noty = "По Вашей реферальной ссылке зарегистрирован новый пользователь: <a href='../profile?id=".$id."'>".$login."</a>";
	return $noty;
}

function referal_money($amount, $id, $login) {
	$noty = "На Ваш баланс начислено ".$amount." за пополнение счета рефералом <a href='../profile?id=".$id."'>".$login."</a>";
	return $noty;
}