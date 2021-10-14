<?php
include_once '../inc/start.php';
if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов actions_c.php"); 
	echo 'Ошибка: [Прямой вызов инклуда]';
	exit(json_encode(array('status' => '2')));
}
$token = clean($_POST['token'],null);
if($conf->token == 1 && ($_SESSION['token'] != $token)) {
	log_error("Неверный токен"); 
	echo 'Ошибка: [Неверный токен]';
	exit(json_encode(array('status' => '2')));
}

/* Удалить новость
=========================================*/
if (isset($_POST['dell_new']) and (is_worthy("q") or is_admin())) {
	$id = check($_POST['id'],"int");
	$pdo->exec("DELETE FROM `news` WHERE id='$id' LIMIT 1");

	$STH = $pdo->prepare("SELECT `id` FROM `news__comments` WHERE `new_id`=:new_id"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':new_id' => $id ));
	while($row = $STH->fetch()) { 
		$STH2 = $pdo->prepare("DELETE FROM `events` WHERE `data_id`=:data_id AND `type` = '2' LIMIT 1");
		$STH2->execute(array( ':data_id' => $row->id ));
	}

	$pdo->exec("DELETE FROM `news__comments` WHERE `new_id`='$id'");

	$STH = $pdo->prepare("DELETE FROM `events` WHERE `data_id`=:data_id AND `type` = '1' LIMIT 1");
	$STH->execute(array( ':data_id' => $id ));

	exit (json_encode(array('status' => '1')));
}

/* Форум
=========================================*/
if (isset($_POST['load_sections']) and is_worthy("t")) {
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$Forum = new Forum($pdo, $tpl);
	echo $Forum->get_sections_admin($conf->template, $users_groups);
	exit();
}
if (isset($_POST['load_forums']) and is_worthy("t")) {
	$id = check($_POST['id'],"int");
	if (empty($id)) {
		exit();
	}

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';

	$Forum = new Forum($pdo, $tpl);
	echo $Forum->get_forums_admin($id, $conf->template, $token);
	exit();
}
if (isset($_POST['load_sections_list']) and is_worthy("t")) {
	$Forum = new Forum($pdo);
	$Forum->get_sections_list_admin();

	exit();
}
if (isset($_POST['load_forum_img']) and is_worthy("t")) {
	if(isset($_POST['id'])) {
		$id = check($_POST['id'],"int");
	}

	if (empty($_FILES['img']['name'])) {
		exit('<p class="text-danger">Выберите изображение!</p>');
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
			exit('<p class="text-danger">Изображение должно быть в формате JPG,GIF или PNG</p>');
		}

		if(!empty($id)) {
			$STH = $pdo->query("SELECT `img` FROM `forums` WHERE id='$id'"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$tmp = $STH->fetch(); 
			if ($tmp->img != 'files/forums_imgs/none.jpg') {
				unlink('../'.$tmp->img);
			}
			$STH = $pdo->prepare("UPDATE `forums` SET `img`=:img WHERE `id`='$id' LIMIT 1");
			$STH->execute(array(':img' => $img));
			exit('<script>document.getElementById("image'.$id.'").src = "../'.$img.'" </script>');
		} else {
			exit('<script>document.getElementById("image").src = "../'.$img.'" </script><input type="hidden" id="forum_img" value="'.$img.'">');
		}
	}
}
if (isset($_POST['add_forum']) and is_worthy("t")) {
	$section = check($_POST['section'],"int");
	$img = checkJs($_POST['img'],null);
	$name = check($_POST['name'],null);
	$description = check($_POST['description'],null);

	if (empty($img)){
		$img = "files/forums_imgs/none.jpg";
	}
	if (empty($section)) {
		exit (json_encode(array('status' => '2', 'input' => 'sections_list', 'reply' => '')));
	}
	if (empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'forum_name', 'reply' => 'Заполните!')));
	}
	if (empty($description)) {
		$description = '';
	}
	if (mb_strlen($name, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'forum_name', 'reply' => 'Не более 255 символов!')));
	}
	if (mb_strlen($description, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'forum_description', 'reply' => 'Не более 255 символов!')));
	}

	$Forum = new Forum($pdo);
	$Forum->add_forum($section, $name, $description, $img);

	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['edit_forum']) and is_worthy("t")) {
	$name = check($_POST['name'],null);
	$description = check($_POST['description'],null);
	$id = check($_POST['id'],"int");

	if (empty($id)) {
		exit ();
	}
	if (empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'forum_name', 'reply' => 'Заполните!')));
	}
	if (empty($description)) {
		$description = '';
	}

	if (mb_strlen($name, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'forum_name', 'reply' => 'Не более 255 символов!')));
	}
	if (mb_strlen($description, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'forum_description', 'reply' => 'Не более 255 символов!')));
	}

	$Forum = new Forum($pdo);
	$Forum->edit_forum($id, $name, $description);

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['dell_forum']) and is_worthy("t")) {
	$id = check($_POST['id'],"int");

	if (empty($id)) {
		exit (json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	$Forum->dell_forum($id, 2);

	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['dell_topic']) and is_worthy("e")) {
	$id = check($_POST['id'],"int");

	if (empty($id)) {
		exit (json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	$Forum->dell_topic($id, 2);

	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['up_forum']) and is_worthy("t")) {
	$id = check($_POST['id'],"int");
	$section_id = check($_POST['id2'],"int");

	if (empty($id) or empty($section_id)) {
		exit (json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	$Forum->up_forum($id, $section_id);

	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['down_forum']) and is_worthy("t")) {
	$id = check($_POST['id'],"int");
	$section_id = check($_POST['id2'],"int");

	if (empty($id) or empty($section_id)) {
		exit (json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	$Forum->down_forum($id, $section_id);

	exit (json_encode(array('status' => '1')));
}

/* Тикеты
=========================================*/
if (isset($_POST['load_open_tickets']) and is_worthy("p")){
	$i=0;
	$STH = $pdo->query("SELECT tickets.id,tickets.have_answer,tickets.name,tickets.date,tickets.last_answer,tickets.author,users.login FROM tickets LEFT JOIN users ON tickets.author = users.id WHERE tickets.status='1' ORDER BY tickets.last_answer DESC"); $STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		?>
			<tr>
				<td><a href="ticket?id=<?php echo $row->id ?>"><?php echo $row->name ?></a></td>
				<td><a href="../profile?id=<?php echo $row->author ?>"><?php echo $row->login ?></a></td>
				<td>
					<?php 
						echo expand_date($row->last_answer,7);
						if($row->have_answer == 1) {
							echo '<span class="text-info"> от администрации</span>';
						} else {
							echo '<span class="text-warning"> от пользователя</span>';
						}
					?>		
				</td>
				<td><?php echo expand_date($row->date,7) ?></td>
			</tr>
		<?php
	}

	if ($i == 0){
		exit ('<tr><td colspan="10">Открытых тикетов нет</td></tr>');
	}
	exit();
}
if (isset($_POST['load_close_tickets']) and is_worthy("p")){
	$load_val = checkJs($_POST['load_val'],"int");
	if (empty($load_val)){
		$load_val = 1;
	}

	$start = ($load_val-1)*20;
	$end = 20;
	$i=$start;
	$i2=0;
	$STH = $pdo->query("SELECT tickets.id,tickets.name,tickets.date,tickets.last_answer,tickets.closed,users.login FROM tickets LEFT JOIN users ON tickets.closed = users.id WHERE tickets.status='2' ORDER BY tickets.last_answer DESC LIMIT ".$start.", ".$end); $STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		$i2++;
		?>
			<tr>
				<td><a href="ticket?id=<?php echo $row->id ?>"><?php echo $row->name ?></a></td>
				<td><a href="../profile?id=<?php echo $row->closed ?>"><?php echo $row->login ?></a></td>
				<td><?php echo expand_date($row->last_answer,7) ?></td>
				<td><?php echo expand_date($row->date,7) ?></td>
			</tr>
		<?php
	}

	if (($load_val > 0) and ($i2 > 19)){
		$load_val++;
		exit ('<tr id="loader_'.$load_val.'"><td colspan="10"><span class="empty-element" onclick="load_close_tickets(\''.$load_val.'\');">Подгрузить тикеты</span></td></tr>');
	}
	if (($load_val > 0) and ($i2 < 20)){
		exit ('<tr><td colspan="10">Все тикеты выгружены</td></tr>');
	}
	if ($i == 0){
		exit ('<tr><td colspan="10">Закрытых тикетов нет</td></tr>');
	}
	exit();
}
if (isset($_POST['dell_ticket']) and is_worthy("l")) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)){
		$result = array('status' => '2');
		exit (json_encode($result));
	}
	$STH = $pdo->query("SELECT files FROM tickets WHERE id='$id '"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(isset($row->files) and $row->files != 'none') {
		unlink('../'.$row->files);
	}

	$pdo->exec("DELETE FROM tickets WHERE id='$id' LIMIT 1");
	$pdo->exec("DELETE FROM tickets__answers WHERE ticket='$id'");
	exit (json_encode(array('status' => '1')));
}
/* Баны
=========================================*/
if (isset($_POST['dell_ban']) and is_worthy("o")) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT `id`, `server`, `img` FROM `bans` WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->id)){
		exit(json_encode(array('status' => '2')));
	}
	if(!is_worthy_specifically("o", $row->server)) {
		exit(json_encode(array('status' => '2')));
	}

	$data = explode(";", $row->img);
	for ($i=0; $i < count($data); $i++) {
		if (!empty($data[$i])){
			unlink('../'.$data[$i]);
		}
	}
	$pdo->exec("DELETE FROM bans WHERE id='$id' LIMIT 1");
	$pdo->exec("DELETE FROM bans__comments WHERE ban_id='$id'");
	exit (json_encode(array('status' => '1')));
}
if (isset($_POST['close_ban']) and is_worthy("i")) {
	$id = checkJs($_POST['id'],"int");
	$action_type = checkJs($_POST['type'],"int");
	$bid = checkJs($_POST['bid'],"int");

	if ($action_type != '1' and $action_type != '2'){
		exit(json_encode(array('status' => '2')));
	}
	if (empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT `server` FROM `bans` WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if (empty($row->server)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("i", $row->server)) {
		exit(json_encode(array('status' => '2')));
	}

	if (!empty($bid)){
		$STH = $pdo->query("SELECT id,server FROM bans WHERE bid='$bid' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if (empty($row->id)){
			exit(json_encode(array('status' => '2')));
		}

		$server = $row->server;
		$STH = $pdo->query("SELECT id,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE id='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$serv_info = $STH->fetch();
		if (empty($serv_info->id)){
			exit(json_encode(array('status' => '2')));
		}
		if (!in_array($serv_info->type, array(2,3,4,5,6))){
			exit(json_encode(array('status' => '2')));
		}

		$db_host = $serv_info->db_host;
		$db_user = $serv_info->db_user;
		$db_pass = $serv_info->db_pass;
		$db_db = $serv_info->db_db;
		$db_prefix = $serv_info->db_prefix;
		$type = $serv_info->type;
		if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			exit(json_encode(array('status' => '2')));
		}
		
		set_names($pdo2, $serv_info->db_code);
		
		if ($action_type == '1'){
			$table = set_prefix($db_prefix, 'bans');
			if ($type == '2' || $type == '3' || $type == '5') {
				$STH = $pdo2->prepare("UPDATE `$table` SET `expired`=:expired, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1");
				$STH->execute(array( ':expired' => '1', ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':id' => $bid ));
			} elseif ($type == '4') {
				$STH = $pdo2->prepare("UPDATE `$table` SET `RemovedBy`=:RemovedBy,`RemoveType`=:RemoveType, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1");
				$STH->execute(array( ':RemovedBy' => '0', ':RemoveType' => 'U', ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':id' => $bid ));
			}
		}
	}

	$STH = $pdo->prepare("UPDATE `bans` SET `status`=:status, `closed`=:closed, `have_answer`=:have_answer WHERE `id`='$id' LIMIT 1");
	if ($STH->execute(array( 'status' => $action_type, 'closed' => $_SESSION['id'], 'have_answer' => '1' )) == '1') {
		$action_type++;
		$STH = $pdo->query("SELECT `bans`.`author`, `users`.`email`, `users`.`email_notice` FROM `bans` LEFT JOIN `users` ON `users`.`id`=`bans`.`author` WHERE `bans`.`id`='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		incNotifications();
		$noty = close_ban_noty($id);
		send_noty($pdo, $noty, $row->author, $action_type);

		if($row->email_notice == 1) {
			$letter = close_ban_letter($id, $full_site_host);
			sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
		}

		exit (json_encode(array('status' => '1', 'closed' => $_SESSION['id'], 'closed_a' => $_SESSION['login'])));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if (isset($_POST['close_ban2']) and is_worthy("s")) {
	$server = checkJs($_POST['server'],"int");
	$bid = checkJs($_POST['bid'],"int");
	if (empty($bid) or empty($server)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("s", $server)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT id,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE id='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$serv_info = $STH->fetch();
	if (empty($serv_info->id)){
		exit(json_encode(array('status' => '2')));
	}
	if (!in_array($serv_info->type, array(2,3,4,5,6))){
		exit(json_encode(array('status' => '2')));
	}

	$db_host = $serv_info->db_host;
	$db_user = $serv_info->db_user;
	$db_pass = $serv_info->db_pass;
	$db_db = $serv_info->db_db;
	$db_prefix = $serv_info->db_prefix;
	$type = $serv_info->type;
	if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
		exit(json_encode(array('status' => '2')));
	}
	
	set_names($pdo2, $serv_info->db_code);
	
	$table = set_prefix($db_prefix, 'bans');
	if ($type == '2' || $type == '3' || $type == '5') {
		$STH = $pdo2->prepare("UPDATE `$table` SET `expired`=:expired, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1");
		$STH->execute(array( ':expired' => '1', ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':id' => $bid ));
	} else {
		$STH = $pdo2->prepare("UPDATE `$table` SET `RemovedBy`=:RemovedBy,`RemoveType`=:RemoveType, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1");
		$STH->execute(array( ':RemovedBy' => '0', ':RemoveType' => 'U', ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':id' => $bid ));
	}
	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['close_mute']) and is_worthy("s")) {
	$server = checkJs($_POST['server'],"int");
	$bid = checkJs($_POST['bid'],"int");

	if (empty($bid) or empty($server)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("s", $server)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT id,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE id='$server' and type!=0 LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$serv_info = $STH->fetch();
	if (empty($serv_info->id)){
		exit(json_encode(array('status' => '2')));
	}
	
	set_names($pdo2, $serv_info->db_code);
	
	$db_host = $serv_info->db_host;
	$db_user = $serv_info->db_user;
	$db_pass = $serv_info->db_pass;
	$db_db = $serv_info->db_db;
	$db_prefix = $serv_info->db_prefix;
	$type = $serv_info->type;

	if ($type == '1' || $type == '2' || $type == '3' || $type == '5') {
		if(check_table('comms', $pdo)) {
			$table = 'comms';
		} else {
			exit(json_encode(array('status' => '2')));
		}

		$STH = $pdo->prepare("UPDATE $table SET expired=:expired, modified_by=:modified_by WHERE `bid`=:id LIMIT 1");
		$STH->execute(array( ':expired' => '-1', ':modified_by' => $_SESSION['id'], ':id' => $bid ));
	} else {
		if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			exit(json_encode(array('status' => '2')));
		}
		$table = set_prefix($db_prefix, 'comms');
		$STH = $pdo2->prepare("UPDATE $table SET RemovedBy=:RemovedBy, RemoveType=:RemoveType, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1");
		$STH->execute(array( ':RemovedBy' => '0', ':RemoveType' => 'U', ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':id' => $bid ));
	}

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['change_ban_end']) and is_worthy("s")) {
	$server = checkJs($_POST['server'], "int");
	$bid = checkJs($_POST['bid'], "int");
	$date = checkJs($_POST['date'], null);

	if (empty($bid) or empty($server) or empty($date)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("s", $server)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("SELECT `id`, `db_host`, `db_user`, `db_pass`, `db_db`, `db_prefix`, `type`, `db_code` FROM `servers` WHERE `id`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':server' => $server ));
	$server = $STH->fetch();
	if (empty($server->id)){
		exit(json_encode(array('status' => '2')));
	}
	$db_prefix = $server->db_prefix;
	$type = $server->type;

	if (!in_array($type, array(2,3,4,5,6))){
		exit(json_encode(array('status' => '2')));
	}

	if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
		exit(json_encode(array('status' => '2')));
	}
	
	set_names($pdo2, $server->db_code);

	if($_POST['date'] == '00.00.0000 00:00') {
		$date = 0;
	} else {
		$date = strtotime($date);
	}
	
	$table = set_prefix($db_prefix, 'bans');
	if ($type == '2' || $type == '3' || $type == '5') {
		$STH = $pdo2->prepare("SELECT `ban_created` FROM `$table` WHERE `bid`=:bid LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':bid' => $bid ));
		$row = $STH->fetch();

		if($date == 0) {
			$ban_length = 0;
			$ban_ends = $row->ban_created;
		} else {
			$ban_length = round(($date - $row->ban_created) / 60);
			$ban_ends = ($ban_length+$row->ban_created) * 60;
		}

		if($date > time() || $ban_length == 0) {
			$disp = "1";
			if($ban_length == 0) {
				$time =  'Никогда';
				$class = "danger";
			} else {
				$time = expand_date($ban_ends, 1);
				$class = "";
			}
			$data = array( ':expired' => '0', ':ban_length' => $ban_length, ':unban_type' => NULL, ':ban_closed' => NULL, ':bid' => $bid );
		} else {
			$time = expand_date($ban_ends, 1);
			$class = "success";
			$disp = "1";
			$data = array( ':expired' => '1', ':ban_length' => $ban_length, ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':bid' => $bid );
		}

		$STH = $pdo2->prepare("UPDATE `$table` SET `expired`=:expired, `ban_length`=:ban_length, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:bid LIMIT 1");
		$STH->execute($data);

		$ban_length = $ban_length * 60;
	} else {
		$STH = $pdo2->prepare("SELECT `created` FROM `$table` WHERE `bid`=:bid LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':bid' => $bid ));
		$row = $STH->fetch();

		if($date == 0) {
			$ban_length = 0;
			$ban_ends = $row->created;
		} else {
			$ban_length = round($date - $row->created);
			$ban_ends = $row->created + $ban_length;
		}

		if($date > time() || $ban_length == 0) {
			$disp = "1";
			if($ban_length == 0) {
				$time =  'Никогда';
				$class = "danger";
			} else {
				$time = expand_date($ban_ends, 1);
				$class = "";
			}
			$data = array( ':ends' => $ban_ends, ':length' => $ban_length, ':RemovedBy' => NULL, ':RemoveType' => NULL, ':unban_type' => NULL, ':ban_closed' => NULL, ':bid' => $bid );
		} else {
			$time = expand_date($ban_ends, 1);
			$class = "success";
			$disp = "1";
			$data = array( ':ends' => '0', ':length' => '0', ':RemovedBy' => '0', ':RemoveType' => 'U', ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':bid' => $bid );
		}

		$STH = $pdo2->prepare("UPDATE `$table` SET `ends`=:ends,`length`=:length,`RemovedBy`=:RemovedBy,`RemoveType`=:RemoveType, `unban_type`=:unban_type,`ban_closed`=:ban_closed WHERE `bid`=:bid LIMIT 1");
		$STH->execute($data);
	}

	exit(json_encode(array('status' => '1', 'length' => expand_seconds2($ban_length), 'ends' => $time, 'class' => $class, 'disp' => $disp)));
}
if (isset($_POST['change_mute_end']) and is_worthy("s")) {
	$server = checkJs($_POST['server'], "int");
	$bid = checkJs($_POST['bid'], "int");
	$date = checkJs($_POST['date'], null);

	if (empty($bid) or empty($server) or empty($date)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("s", $server)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("SELECT `id`, `db_host`, `db_user`, `db_pass`, `db_db`, `db_prefix`, `type`, `db_code` FROM `servers` WHERE `id`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':server' => $server ));
	$server = $STH->fetch();
	if (empty($server->id)){
		exit(json_encode(array('status' => '2')));
	}
	$db_prefix = $server->db_prefix;
	$type = $server->type;

	if (!in_array($type, array(2,3,4,5,6))){
		exit(json_encode(array('status' => '2')));
	}

	if($_POST['date'] == '00.00.0000 00:00') {
		$date = 0;
	} else {
		$date = strtotime($date);
	}

	if ($type == '2' || $type == '3' || $type == '5') {
		if(check_table('comms', $pdo)) {
			$table = 'comms';
		} else {
			exit(json_encode(array('status' => '2')));
		}

		$STH = $pdo->prepare("SELECT `created` FROM `$table` WHERE `bid`=:bid LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':bid' => $bid ));
		$row = $STH->fetch();

		if($date == 0) {
			$ban_length = 0;
			$ban_ends = $row->created;
		} else {
			$ban_length = round(($date - $row->created) / 60);
			$ban_ends = $ban_length+$row->created;
		}

		if($date > time() || $ban_length == 0) {
			$disp = "1";
			if($ban_length == 0) {
				$time =  'Никогда';
				$class = "danger";
			} else {
				$time = expand_date($ban_ends, 1);
				$class = "";
			}
			$data = array( ':expired' => $ban_ends, ':length' => $ban_length, ':type' => 0, ':modified_by' => '', ':bid' => $bid );
		} else {
			$time = expand_date($ban_ends, 1);
			$class = "success";
			$disp = "1";
			$data = array( ':expired' => $ban_ends, ':length' => $ban_length, ':type' => '-1', ':modified_by' => $_SESSION['id'], ':bid' => $bid );
		}

		$STH = $pdo->prepare("UPDATE `$table` SET `expired`=:expired, `length`=:length, `type`=:type, `modified_by`=:modified_by WHERE `bid`=:bid LIMIT 1");
		$STH->execute($data);

		$ban_length = $ban_length * 60;
		$ban_ends = ($ban_length+$row->created) * 60;
	} else {
		if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
			exit(json_encode(array('status' => '2')));
		}
		
		set_names($pdo2, $server->db_code);
		$table = set_prefix($db_prefix, 'comms');

		$STH = $pdo2->prepare("SELECT `created` FROM `$table` WHERE `bid`=:bid LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':bid' => $bid ));
		$row = $STH->fetch();

		if($date == 0) {
			$ban_length = 0;
			$ban_ends = $row->created;
		} else {
			$ban_length = round($date - $row->created);
			$ban_ends = $row->created + $ban_length;
		}

		if($date > time() || $ban_length == 0) {
			$disp = "1";
			if($ban_length == 0) {
				$time =  'Никогда';
				$class = "danger";
			} else {
				$time = expand_date($ban_ends, 1);
				$class = "";
			}
			$data = array( ':ends' => $ban_ends, ':length' => $ban_length, ':RemovedBy' => NULL, ':RemoveType' => NULL, ':unban_type' => NULL, ':ban_closed' => NULL, ':bid' => $bid );
		} else {
			$time = expand_date($ban_ends, 1);
			$class = "success";
			$disp = "1";
			$data = array( ':ends' => '0', ':length' => '0', ':RemovedBy' => '0', ':RemoveType' => 'U', ':unban_type' => '-1', ':ban_closed' => $_SESSION['id'], ':bid' => $bid );
		}

		$STH = $pdo2->prepare("UPDATE `$table` SET `ends`=:ends,`length`=:length,`RemovedBy`=:RemovedBy,`RemoveType`=:RemoveType, `unban_type`=:unban_type,`ban_closed`=:ban_closed WHERE `bid`=:bid LIMIT 1");
		$STH->execute($data);
	}

	exit(json_encode(array('status' => '1', 'length' => expand_seconds2($ban_length), 'ends' => $time, 'class' => $class, 'disp' => $disp)));
}
if (isset($_POST['dell_user_stats']) and is_worthy("h")) {
	$server = checkJs($_POST['server'], "int");
	$id = checkJs($_POST['id'], "int");

	if (empty($id) or empty($server)){
		exit(json_encode(array('status' => '2')));
	}

	if(!is_worthy_specifically("h", $server)) {
		exit (json_encode(array('status' => '2')));
	}	

	$STH = $pdo->query("SELECT `id`, `st_db_host`, `st_db_user`, `st_db_pass`, `st_db_db`, `st_type`, `st_db_code`, `st_db_table`, `st_db_code` FROM `servers` WHERE `st_type`!=0 and `id`='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if(empty($row->id)) {
		exit(json_encode(array('status' => '2')));
	}

	if(!$pdo2 = db_connect($row->st_db_host, $row->st_db_db, $row->st_db_user, $row->st_db_pass)) {
		exit(json_encode(array('status' => '2')));
	}
	
	set_names($pdo2, $row->st_db_code);
	
	$type = $row->st_type;
	if($type == 1 or $type == 2) {
		$table = 'csstats_players';
		$id_name = 'id';
	} elseif($type == 3) {
		$table = 'csstats';
		$id_name = 'id';
	} elseif($type == 4) {
		$table = 'hlstats_Players';
		$id_name = 'playerId';
	} elseif($type == 5) {
		$table = $row->st_db_table;
		$id_name = 'id';
	} elseif($type == 6) {
		$table = $row->st_db_table;
		$id_name = 'steam';
		$id = preg_replace('/[^0-9]+/', '', (string) $_POST['id']);
		$id = 'STEAM_'.$id[1].':'.$id[2].':'.substr($id, 3);
	}

	$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `$id_name`=:id LIMIT 1");
	$STH->execute(array( ':id' => $id ));

	exit(json_encode(array('status' => '1')));
}
if (isset($_POST['ban_player']) and is_worthy("s")) {
	$nick = checkJs($_POST['nick'], null);
	$ip = checkJs($_POST['ip'], null);
	$steam_id = checkJs($_POST['steam_id'], null);

	exit(json_encode(array('status' => '1')));
}

if(isset($_POST['removeComplaint']) && is_worthy("u")) {
	$id = checkJs($_POST['id'], "int");

	if(empty($id)) {
		exit(json_encode(['status' => 2]));
	}

	$STH = $pdo->prepare(
		"SELECT id, accused_admin_server_id, screens FROM complaints WHERE id = :id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();

	if(empty($row->id)) {
		exit(json_encode(['status' => 2]));
	}

	if(!is_worthy_specifically("o", $row->accused_admin_server_id)) {
		exit(json_encode(['status' => 2]));
	}

	$screens = explode(";", $row->screens);
	foreach($screens as $screen) {
		if(!empty($screen)) {
			unlink('../' . $screen);
		}
	}

	$STH = $pdo->prepare("DELETE FROM complaints WHERE id=:id LIMIT 1");
	$STH->execute([':id' => $id]);

	$STH = $pdo->prepare("DELETE FROM complaints__comments WHERE complaint_id=:id LIMIT 1");
	$STH->execute([':id' => $id]);

	exit (json_encode(['status' => 1]));
}

if(isset($_POST['closeComplaint']) && is_worthy("k")) {
	$id       = checkJs($_POST['id'], "int");
	$sentence = checkJs($_POST['sentence'], "int");

	if(!in_array($sentence, [1, 2, 3, 4])) {
		exit(json_encode(['status' => 2]));
	}

	if(empty($id)) {
		exit(json_encode(['status' => 2]));
	}

	$STH = $pdo->prepare(
		"SELECT 
    				complaints.sentence, 
    				complaints.accused_admin_server_id, 
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

	if(empty($row->accused_admin_server_id)) {
		exit(json_encode(['status' => 2]));
	}

	if(!is_worthy_specifically("k", $row->accused_admin_server_id)) {
		exit(json_encode(['status' => 2]));
	}

	$STH = $pdo->prepare("UPDATE complaints SET sentence=:sentence, judge_id=:judge_id, have_answer=:have_answer WHERE id = :id LIMIT 1");
	$STH->execute(['sentence' => $sentence, 'judge_id' => $_SESSION['id'], 'have_answer' => 1, 'id' => $id]);

	incNotifications();

	$noty = close_complaint_noty($id);
	$letter = close_complaint_letter($id, $full_site_host);

	send_noty($pdo, $noty, $row->author_id, 1);
	if($row->author_email_notice == 1) {
		sendmail($row->author_email, $letter['subject'], $letter['message'], $pdo);
	}

	if(!empty($row->accused_id)) {
		send_noty($pdo, $noty, $row->accused_id, 1);
		if($row->accused_email_notice == 1) {
			sendmail($row->accused_email, $letter['subject'], $letter['message'], $pdo);
		}
	}

	exit (json_encode(['status' => 1, 'answer' => Complaints::getComplaintSentenceText($sentence)]));
}

exit(json_encode(['status' => 2]));