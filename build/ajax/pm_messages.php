<?php
include_once '../inc/start_lite.php';

if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов pm_messages.php"); 
	exit('Ошибка: [Прямой вызов инклуда]');
}
if(empty($_SESSION['id'])){
	exit('Ошибка: [Доступно только авторизованным]');
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'],null))) {
	log_error("Неверный токен"); 
	exit('2');
}

if(isset($_POST['get_messages'])) {
	$last_mess = check($_POST['last_mess'],"int");
	$id = check($_POST['id'],"int");
	if(empty($last_mess) or empty($id)){
		exit('2');
	}

	$tpl = new Template;
	$tpl->dir = '../templates/'.$conf->template.'/tpl/';
	$STH = $pdo->prepare("SELECT MAX(id) as max FROM pm__messages WHERE dialog_id = '$id'");
	$STH->execute();
	$row = $STH->fetch(PDO::FETCH_ASSOC);
	if($last_mess < $row['max']){
		$last_mess_new = $row['max'];
		$STH = $pdo->query("SELECT pm__messages.id,pm__messages.user_id1, pm__messages.text, pm__messages.date, users.login, users.avatar FROM pm__messages 
			LEFT JOIN users ON (pm__messages.user_id1 = users.id)
			WHERE (pm__messages.dialog_id = '$id') and (pm__messages.id > '$last_mess') and (pm__messages.user_id1 = '$_SESSION[id]' or pm__messages.user_id2 = '$_SESSION[id]')
			ORDER BY pm__messages.id DESC");
		$STH->execute();
		$row = $STH->fetchAll();
		$count = count($row);
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

		$status = 'r'.$_SESSION['id'];
		$STH = $pdo->prepare("UPDATE pm__dialogs SET new=:new WHERE id = '$id' and (new = '$_SESSION[id]' or new = '$status')");
		$STH->execute(array( ':new' => '0' ));

		exit('<script>$("#last_mess").val('.$last_mess_new.');</script>');
	} else {
		exit('2');
	}
}
?>