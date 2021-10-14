<?php
include_once '../inc/start_lite.php';

if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов pm_actions.php"); 
	exit('Ошибка: [Прямой вызов инклуда]');
}
if(empty($_SESSION['id'])){
	exit('Ошибка: [Доступно только авторизованным]');
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'],null))) {
	log_error("Неверный токен"); 
	exit();
}

function check_to_friend($pdo, $id) {
	$STH = $pdo->query("SELECT `admins_ids` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	$admins = explode(",", $row->admins_ids);
	for ($i=0; $i < count($admins); $i++) {
		$admins[$i] = trim($admins[$i]);
	}
	if(!in_array($_SESSION['id'], $admins)) {
		$STH = $pdo->query("SELECT `im` FROM `users` WHERE `id`='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if($row->im == 2) {
			$STH = $pdo->query("SELECT `id` FROM `users__friends` WHERE (((id_sender = '$_SESSION[id]' and `id_taker` = '$id') or (`id_taker` = '$_SESSION[id]' and `id_sender` = '$id')) and (`accept` = '1')) LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(empty($row->id)) {
				return false;
			}
		}
	}
	return true;
}

const ERROR_MESSAGE_1 = 'Данному пользователю могут писать только друзья. &nbsp&nbsp&nbsp';
const ERROR_MESSAGE_2 = 'Вы заблокировали данного пользователя. &nbsp&nbsp&nbsp';
const ERROR_MESSAGE_3 = 'Пользователь Вас заблокировал. &nbsp&nbsp&nbsp';

if (isset($_POST['load_companions'])) {
	$i = 0;
	$data = '';
	$users_groups = get_groups($pdo);
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->query("SELECT users__friends.id_taker, users.id, users.login, users.avatar, users.rights FROM users__friends LEFT JOIN users ON users__friends.id_taker = users.id WHERE (id_sender='$_SESSION[id]') AND accept='1' UNION SELECT users__friends.id_sender, users.id, users.login, users.avatar, users.rights FROM users__friends LEFT JOIN users ON users__friends.id_sender = users.id WHERE (id_taker='$_SESSION[id]') AND accept='1'"); $STH->setFetchMode(PDO::FETCH_OBJ);  
	while($row = $STH->fetch()) {
		$tpl->load_template('elements/companion.tpl');
		$gp = $users_groups[$row->rights];
		$tpl->set("{login}", $row->login);
		$tpl->set("{avatar}", $row->avatar);
		$tpl->set("{id}", $row->id);
		$tpl->set("{gp_color}", $gp['color']);
		$tpl->set("{gp_name}", $gp['name']);
		$tpl->compile( 'content' );
		$tpl->clear();
		$i++;
	}
	if ($i == 0){
		echo '<span class="empty-element">Друзей нет</span>';
	} else {
		$tpl->show($tpl->result['content']);
		$tpl->global_clear();
	}
	exit();
}
if(isset($_POST['create_dialog'])){
	$id = checkJs($_POST['id'],"int");

	if(empty($id)){
		exit();
	}

	if($id == $_SESSION['id']) {
		exit(json_encode(['status' => 1, 'dialogId' => 0]));
	}

	include_once "../inc/protect.php";

	$message = '';

	if(!check_to_friend($pdo, $id)) {
		$message = ERROR_MESSAGE_1;
	}

	if(isOnMyBlacklist($pdo, $id)) {
		$message = ERROR_MESSAGE_2;
	}

	if(isOnHisBlacklist($pdo, $id)) {
		$message = ERROR_MESSAGE_3;
	}

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->query("SELECT id FROM pm__dialogs WHERE (user_id1 = '$_SESSION[id]' and user_id2 = '$id') or (user_id1 = '$id' and user_id2 = '$_SESSION[id]') LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if(empty($row->id)){
		$tpl->load_template('elements/messages_place.tpl');
		$tpl->set("{id}", $id);
		$tpl->set("{messages}", '');
		$tpl->set("{last_mess}", '');
		$tpl->set("{func}", 'send_first_message');
		$tpl->set("{site_host}", $site_host);
		$tpl->set("{template}", $conf->template);
		$tpl->compile( 'content' );
		$tpl->clear();

		ob_start();
		eval('?>'.$tpl->result['content'].'<?php ');
		$tpl->result['content'] = ob_get_clean();

		exit(json_encode(['status' => 3, 'data' => $tpl->result['content'], 'message' => $message]));
	} else {
		exit(json_encode(['status' => 1, 'dialogId' => $row->id, 'message' => $message]));
	}
}
if (isset($_POST['send_first_message'])) {
	$id = checkJs($_POST['id'],"int");
	$text = check($_POST['message_text'],null);
	if(empty($id) or empty($text)){
		exit();
	}

	include_once "../inc/protect.php";

	if(!check_to_friend($pdo, $id)) {
		exit(json_encode(['status' => '2', 'message' => ERROR_MESSAGE_1]));
	}

	if(isOnMyBlacklist($pdo, $id)) {
		exit(json_encode(['status' => '2', 'message' => ERROR_MESSAGE_2]));
	}

	if(isOnHisBlacklist($pdo, $id)) {
		exit(json_encode(['status' => '2', 'message' => ERROR_MESSAGE_3]));
	}

	$text = find_img_mp3($text, $_SESSION['id']);

	$STH = $pdo->query("SELECT id FROM pm__dialogs WHERE (user_id1 = '$_SESSION[id]' and user_id2 = '$id') or (user_id1 = '$id' and user_id2 = '$_SESSION[id]') LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)){
		$date = date("Y-m-d H:i:s");

		$STH = $pdo->prepare("INSERT INTO pm__dialogs (user_id1,user_id2,date,new) values (:user_id1, :user_id2, :date, :new)"); 
		$STH->execute(array('user_id1' => $_SESSION['id'], 'user_id2' => $id, 'date' => $date, 'new' => $id));
		$STH = $pdo->query("SELECT id FROM pm__dialogs WHERE user_id1 = '$_SESSION[id]' and user_id2 = '$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		$STH = $pdo->prepare("INSERT INTO pm__messages (user_id1,user_id2,text,date,dialog_id) values (:user_id1, :user_id2, :text, :date, :dialog_id)"); 
		$STH->execute(array('user_id1' => $_SESSION['id'], 'user_id2' => $id, 'text' => $text, 'date' => $date, 'dialog_id' => $row->id));
	} else {
		$date = date("Y-m-d H:i:s");
		$STH = $pdo->prepare("INSERT INTO pm__messages (user_id1,user_id2,text,date,dialog_id) values (:user_id1, :user_id2, :text, :date, :dialog_id)"); 
		$STH->execute(array('user_id1' => $_SESSION['id'], 'user_id2' => $id, 'text' => $text, 'date' => $date, 'dialog_id' => $row->id));

		$STH = $pdo->prepare("UPDATE pm__dialogs SET dell_1=:dell_1,dell_2=:dell_2,date=:date,new=:new WHERE id='$row->id' LIMIT 1");
		$STH->execute(array( 'dell_1' => '0','dell_2' => '0','date' => $date, 'new' => $id ));
	}
	exit(json_encode(array('status' => '1', 'id' => $row->id)));
}
if(isset($_POST['open_dialog'])){
	$id = checkJs($_POST['id'],"int");
	if(empty($id)){
		exit();
	}

	$i=0;
	$last_mess=0;
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$tpl->result['messages'] = '';
	$STH = $pdo->query("SELECT pm__messages.id,pm__messages.user_id1,pm__messages.user_id2, pm__messages.text, pm__messages.date, users.login, users.avatar FROM pm__messages 
						LEFT JOIN users ON (pm__messages.user_id1 = users.id)
						WHERE (pm__messages.dialog_id = '$id') and (pm__messages.user_id1 = '$_SESSION[id]' or pm__messages.user_id2 = '$_SESSION[id]')
						ORDER BY pm__messages.id DESC LIMIT 50");
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	if ($count == 0){
		$tpl->result['messages'] = '<p class="t-c">Сообщений нет</p>';
	} else {
		if(($row[0]['user_id1'] != $_SESSION['id'] ) and ($row[0]['user_id2'] != $_SESSION['id'])){
			exit();
		}
		$last_mess = $row['0']['id'];
		for ($i=$count-1; $i >= 0; $i--) { 
			$date = expand_date($row[$i]['date'],8);
			$tpl->load_template('elements/message.tpl');
			$tpl->set("{text}", nl2br($row[$i]['text']));
			$tpl->set("{id}", $row[$i]['id']);
			$tpl->set("{avatar}", $row[$i]['avatar']);
			$tpl->set("{login}", $row[$i]['login']);
			$tpl->set("{user_id1}", $row[$i]['user_id1']);
			$tpl->set("{date_full}", $date['full']);
			$tpl->set("{date_short}", $date['short']);
			$tpl->compile( 'messages' );
			$tpl->clear();
		}
		$tpl->result['messages'].'<script>$("#last_mess").val('.$row['0']['id'].');</script>';

		$status = 'r'.$_SESSION['id'];
		$STH = $pdo->prepare("UPDATE pm__dialogs SET new=:new WHERE id = '$id' and (new = '$_SESSION[id]' or new = '$status')");
		$STH->execute(array( ':new' => '0' ));
	}

	$tpl->load_template('elements/messages_place.tpl');
	$tpl->set("{messages}", $tpl->result['messages']);
	$tpl->set("{last_mess}", $last_mess);
	$tpl->set("{id}", $id);
	$tpl->set("{func}", 'send_message');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->show($tpl->result['content']);
	$tpl->global_clear();
	exit();
}
if (isset($_POST['send_message'])) {
	$id = checkJs($_POST['id'],"int");
	$text = check($_POST['message_text'],null);

	if(empty($id) or empty($text)){
		exit(json_encode(array('status' => '2')));
	}

	include_once "../inc/protect.php";

	$STH = $pdo->query("SELECT user_id1, user_id2 FROM pm__dialogs WHERE id = '$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if($row->user_id1 == $_SESSION['id']){
		$user_id = $row->user_id2;
	} elseif($row->user_id2 == $_SESSION['id']) {
		$user_id = $row->user_id1;
	} else {
		exit(json_encode(array('status' => '2')));
	}

	if(!check_to_friend($pdo, $user_id)) {
		exit(json_encode(['status' => '2', 'message' => ERROR_MESSAGE_1]));
	}

	if(isOnMyBlacklist($pdo, $user_id)) {
		exit(json_encode(['status' => '2', 'message' => ERROR_MESSAGE_2]));
	}

	if(isOnHisBlacklist($pdo, $user_id)) {
		exit(json_encode(['status' => '2', 'message' => ERROR_MESSAGE_3]));
	}

	$text = find_img_mp3($text, $_SESSION['id']);
	$date = date("Y-m-d H:i:s");

	$STH = $pdo->prepare("INSERT INTO pm__messages (user_id1,user_id2,text,date,dialog_id) values (:user_id1, :user_id2, :text, :date, :dialog_id)"); 
	$STH->execute(array('user_id1' => $_SESSION['id'], 'user_id2' => $user_id, 'text' => $text, 'date' => $date, 'dialog_id' => $id));

	$STH = $pdo->prepare("UPDATE pm__dialogs SET dell_1=:dell_1,dell_2=:dell_2,date=:date,new=:new WHERE id='$id' LIMIT 1");
	$STH->execute(array( 'dell_1' => '0','dell_2' => '0','date' => $date,'new' => $user_id ));

	up_online($pdo);

	exit(json_encode(array('status' => 1)));
}
if (isset($_POST['load_messages'])) {
	$id = check($_POST['id'],"int");
	$load_val = check($_POST['load_val'],"int");
	if (empty($id) or empty($load_val)){
		exit('2');
	}

	$start = ($load_val)*50;
	$end = 50;
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->query("SELECT pm__messages.id,pm__messages.user_id1, pm__messages.text, pm__messages.date, users.login, users.avatar FROM pm__messages 
						LEFT JOIN users ON (pm__messages.user_id1 = users.id)
						WHERE pm__messages.dialog_id = '$id' and (pm__messages.user_id1 = '$_SESSION[id]' or pm__messages.user_id2 = '$_SESSION[id]')
						ORDER BY pm__messages.id DESC LIMIT ".$start.", ".$end);
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	if ($count > 0){
		for ($i=$count-1; $i >= 0; $i--) { 
			$date = expand_date($row[$i]['date'],8);
			$tpl->load_template('elements/message.tpl');
			$tpl->set("{text}", nl2br($row[$i]['text']));
			$tpl->set("{id}", $row[$i]['id']);
			$tpl->set("{avatar}", $row[$i]['avatar']);
			$tpl->set("{login}", $row[$i]['login']);
			$tpl->set("{user_id1}", $row[$i]['user_id1']);
			$tpl->set("{date_full}", $date['full']);
			$tpl->set("{date_short}", $date['short']);
			$tpl->compile( 'messages' );
			$tpl->clear();
		}
		$tpl->show($tpl->result['messages']);
		$tpl->global_clear();
	}
	if($count != 50) {
		exit('<script>$("#load_val").val(0);</script>');
	} else {
		exit();
	}
}
if(isset($_POST['load_dialogs'])){
	$tpl = new Template;
	$tpl->result['content'] = '';
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->query("(SELECT pm__dialogs.*, users.login, users.avatar, pm__dialogs.date AS dt FROM pm__dialogs
						LEFT JOIN users ON (pm__dialogs.user_id1 = users.id) 
						WHERE pm__dialogs.user_id2 = '$_SESSION[id]')
						UNION 
						(SELECT pm__dialogs.*, users.login, users.avatar, pm__dialogs.date AS dt FROM pm__dialogs 
						LEFT JOIN users ON (pm__dialogs.user_id2 = users.id)
						WHERE pm__dialogs.user_id1 = '$_SESSION[id]') ORDER BY dt DESC"); $STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if($row->user_id1 == $_SESSION['id']){
			$user_id = $row->user_id2;
		} else {
			$user_id = $row->user_id1;
		}
		if($row->new == '0'){ 
			$new = 0;
		} elseif ($row->new == $_SESSION['id'] or $row->new == 'r'.$_SESSION['id']) {
			$new = 1;
		} else {
			$new = 2;
		}

		if(($row->user_id1 == $_SESSION['id'] and $row->dell_1 != 1) or ($row->user_id2 == $_SESSION['id'] and $row->dell_2 != 1)){
			$tpl->load_template('elements/dialog.tpl');
			$tpl->set("{date}", expand_date($row->date, 7));
			$tpl->set("{login}", $row->login);
			$tpl->set("{avatar}", $row->avatar);
			$tpl->set("{id}", $row->id);
			$tpl->set("{new}", $new);
			$tpl->compile( 'content' );
			$tpl->clear();
		}
	}
	if($tpl->result['content'] == ''){
		$tpl->result['content'] = '<p class="mb-0">Диалогов нет</p>';
	}

	$tpl->result['content'] .= '
	<script>
		clearInterval(pm_interval);
		clearInterval(check_mess);
		var check_mess = setInterval("check_messages(\'none\', 1)", 10000);
	</script>';

	$tpl->show($tpl->result['content']);
	$tpl->global_clear();
	exit();
}
if(isset($_POST['dell_dialog'])){
	$id = checkJs($_POST['id'],"int");
	if(empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT id,user_id1,user_id2,dell_1,dell_2 FROM pm__dialogs WHERE id='$id' AND (user_id1 = '$_SESSION[id]' or user_id2 = '$_SESSION[id]') LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if(isset($row->id)){
		$user_id1 = $row->user_id1;
		$user_id2 = $row->user_id2;
		$dell_1 = $row->dell_1;
		$dell_2 = $row->dell_2;

		$STH = $pdo->prepare("UPDATE pm__dialogs SET new=:new WHERE id = '$id'");
		$STH->execute(array( ':new' => '0' ));

		if($user_id1 == $_SESSION['id']){
			if($dell_2 == 1){
				$pdo->exec("DELETE FROM pm__dialogs WHERE id='$id' LIMIT 1");
				$pdo->exec("DELETE FROM pm__messages WHERE dialog_id='$id'");
			} else {
				$STH = $pdo->prepare("UPDATE pm__dialogs SET dell_1=:dell_1 WHERE id='$id' LIMIT 1");
				$STH->execute(array( 'dell_1' => '1' ));
			}
		} elseif ($user_id2 == $_SESSION['id']) {
			if($dell_1 == 1){
				$pdo->exec("DELETE FROM pm__dialogs WHERE id='$id' LIMIT 1");
				$pdo->exec("DELETE FROM pm__messages WHERE dialog_id='$id'");
			} else {
				$STH = $pdo->prepare("UPDATE pm__dialogs SET dell_2=:dell_2 WHERE id='$id' LIMIT 1");
				$STH->execute(array( 'dell_2' => '1' ));
			}
		} else {
			exit(json_encode(array('status' => '2')));
		}
	} else {
		exit(json_encode(array('status' => '2')));
	}

	exit(json_encode(array('status' => '1')));
}
if(isset($_POST['drop_img'])) {
	$id = checkJs($_POST['id'],"int");
	if(empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	include_once "../inc/protect.php";

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

	$STH = $pdo->query("SELECT `user_id1`, `user_id2` FROM `pm__dialogs` WHERE `id` = '$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if($row->user_id1 == $_SESSION['id']){
		$user_id = $row->user_id2;
	} elseif($row->user_id2 == $_SESSION['id']) {
		$user_id = $row->user_id1;
	} else {
		exit();
	}

	if(!check_to_friend($pdo, $user_id)) {
		exit(json_encode(array('status' => '2', 'data' => 'Данному пользователю сообщения могут писать только друзья!')));
	}

	$date = date("Y-m-d H:i:s");

	$STH = $pdo->prepare("INSERT INTO pm__messages (user_id1,user_id2,text,date,dialog_id) values (:user_id1, :user_id2, :text, :date, :dialog_id)"); 
	$STH->execute(array('user_id1' => $_SESSION['id'], 'user_id2' => $user_id, 'text' => $message_text, 'date' => $date, 'dialog_id' => $id));

	$STH = $pdo->prepare("UPDATE pm__dialogs SET dell_1=:dell_1,dell_2=:dell_2,date=:date,new=:new WHERE id='$id' LIMIT 1");
	$STH->execute(array( 'dell_1' => '0','dell_2' => '0','date' => $date,'new' => $user_id ));

	up_online($pdo);

	exit(json_encode(array('status' => '1')));
}