<?php
include_once '../inc/start_lite.php';

if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов chat_data.php"); 
	exit('Ошибка: [Прямой вызов инклуда]');
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'],null))) {
	log_error("Неверный токен"); 
	exit('2');
}

$very = new Verification($pdo);

if(isset($_POST['get_messages'])) {
	$last_mess = check($_POST['last_mess'], "int");
	if(empty($last_mess)){
		exit('2');
	}
	$users_groups = get_groups($pdo);

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->prepare("SELECT MAX(id) as max FROM chat");
	$STH->execute();
	$row = $STH->fetch(PDO::FETCH_ASSOC);
	if($last_mess < $row['max']){
		$last_mess_new = $row['max'];
		$STH = $pdo->query("SELECT chat.*, users.login, users.avatar, users.rights FROM chat LEFT JOIN users ON chat.user_id = users.id WHERE chat.id > '$last_mess' ORDER BY chat.id DESC");
		$STH->execute();
		$row = $STH->fetchAll();
		$count = count($row);
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
			$tpl->compile('chat');
			$tpl->clear();
		}
		$tpl->show($tpl->result['chat']);
		$tpl->global_clear();
		exit('<script>$("#last_mess").val('.$last_mess_new.');</script>');
	} else {
		exit('2');
	}
}
if(isset($_POST['get_smiles'])) {
	if(empty($_SESSION['id'])){
		exit('<script>reset_page();</script>');
	}
	if(empty($_POST['type'])) {
		$type = null;
	} else {
		$type = checkJs($_POST['type'],null);
	}

	if($_SESSION['stickers'] == 1){
		$price = '';
		$load_stickers = 1;
	} else {
		$STH = $pdo->query("SELECT price4 FROM config__prices LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$price = $row->price4;
		$load_stickers = 0;
	}

	$stickers = '';
	$stickers_panel = '';
	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$tpl->load_template('/elements/smiles.tpl');
	$tpl->set("{price}", $price);
	$tpl->set("{site_host}", $site_host);
	if($type == 1) {
		$tpl->set("{only_smiles}", 1);
	} else {
		$tpl->set("{only_smiles}", '');
		if($load_stickers == 1) {
			$STH = $pdo->query("SELECT * FROM `stickers`");
			$STH->execute();
			$row = $STH->fetchAll();
			$count = count($row);
			$stickers_panel .= '<div class="btn-group smiles_panel" role="group">
			<a onclick="open_sticker(\'gcms_smiles\');" class="smilebtn"><img src="../files/smiles/smile.png"></a>';

			for ($i=0; $i < $count; $i++) {
				$name_translit = translit($row[$i]['name']);
				$stickers .= '<div class="smiles_box disp-n" id="'.$name_translit.'">';
				$files = scandir('../files/stickers/'.$name_translit, 1);
				$count2 = count($files);
				if($count2 > 2) {
					$stickers_panel .= '<a onclick="open_sticker(\''.$name_translit.'\');" class="smilebtn"><img src="../files/stickers/'.$name_translit.'/'.$files[$count2-3].'"></a>';
				}
				for ($j=$count2-1; $j > -1; $j--) { 
					$exp = explode(".", $files[$j]);
					$exp = end($exp);
					if(strnatcasecmp($exp,'png') == 0 or strnatcasecmp($exp,'jpg') == 0) {
						$stickers .= '<img onclick="set_sticker(this);" class="sticker" src="'.$site_host.'files/stickers/'.$name_translit.'/'.$files[$j].'">';
					}
				}
				$stickers .= '</div>';
			}
			$stickers_panel .= '</div>';
		}
	}
	$tpl->set("{stickers}", $stickers);
	$tpl->set("{stickers_panel}", $stickers_panel);
	$tpl->compile( 'smiles' );
	$tpl->clear();
	$tpl->show($tpl->result['smiles']);
	$tpl->global_clear();
	exit();
}
?>