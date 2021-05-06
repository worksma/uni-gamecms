<?php
include_once '../inc/start.php';
if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов actions_b.php"); 
	echo 'Ошибка: [Прямой вызов инклуда]';
	exit(json_encode(array('status' => '2')));
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'],null))) {
	log_error("Неверный токен"); 
	echo 'Ошибка: [Неверный токен]';
	exit(json_encode(array('status' => '2')));
}

/* Удалить сообщение из чата
=========================================*/
if (isset($_POST['dell_chat_message']) and is_worthy("d")) {
	$id = check($_POST['id'],"int");
	$pdo->exec("DELETE FROM `chat` WHERE `id`='$id' LIMIT 1");
}
if (isset($_POST['save_chat_message']) and is_worthy("d")) {
	$id = check($_POST['id'],"int");
	if(empty($_POST['text'])){
		exit();
	}

	$text = magic_quotes($_POST['text']);
	$text = htmlspecialchars_decode($text);

	include_once '../inc/classes/HTMLPurifier/HTMLPurifier.auto.php';
	$text = $Purifier->purify($text);
	$text = clean_from_php($text);

	$STH = $pdo->prepare("UPDATE `chat` SET `message_text`=:message_text WHERE `id`=:id LIMIT 1");
	if ($STH->execute(array( ':message_text' => $text, ':id' => $id )) == '1') {
		exit(json_encode(array('status' => '1', 'text' => $text)));
	}
	exit();
}

/* Добавление новости
=========================================*/
if (isset($_POST['add_new_img']) and is_worthy("b")) {
	if (empty($_FILES['new_img']['name'])) {
		exit('<script>show_input_error("new_img", "", null);setTimeout(show_error, 500);</script>');
	} else {
		$path = 'files/news_imgs/';
		$date = time();

		if (if_img($_FILES['new_img']['name'])) {
			$filename = $_FILES['new_img']['name'];
			$source = $_FILES['new_img']['tmp_name'];
			$new_img = $path . $date . ".jpg";
			$target = '../'. $new_img;
			move_uploaded_file($source, $target);

		} else {
			exit('<p class="text-danger">Изображение должено быть в формате JPG,GIF или PNG</p><script>show_input_error("new_img", "", null);setTimeout(show_error, 500);</script>');
		}
		echo '
		<script>$("#img").attr("src","../'.$new_img.'");setTimeout(show_ok, 500);</script>
		<input value="'.$new_img.'" type="hidden" id="input_img" maxlength="255" autocomplete="off">
		';
	}
	exit();
}
if (isset($_POST['add_new']) and is_worthy("b")) {
	$img = checkJs($_POST['img'],null);
	$class = check($_POST['class'],"int");
	$name = check($_POST['name'],null);
	$short_text = check($_POST['short_text'],null);
	$date = check($_POST['date'],null);
	$date = date( 'Y-m-d H:i:s', strtotime($date));

	include_once '../inc/classes/HTMLPurifier/HTMLPurifier.auto.php';
	$text = $Purifier->purify($_POST['text']);
	$text = find_img_mp3($text, $_SESSION['id'], 1);

	if (empty($img)){
		$img = "files/news_imgs/none.jpg";
	}

	if (empty($class)) {
		$result = array('status' => '2', 'input' => 'class', 'reply' => '');
		exit (json_encode($result));
	}
	if (empty($name)) {
		$result = array('status' => '2', 'input' => 'name', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}
	if (empty($short_text)) {
		$result = array('status' => '2', 'input' => 'short_text', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}
	if (empty($text)) {
		$result = array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if (mb_strlen($name, 'UTF-8') > 250) {
		$result = array('status' => '2', 'input' => 'name', 'reply' => 'Не более 250 символов!');
		exit (json_encode($result));
	}
	if (mb_strlen($short_text, 'UTF-8') > 250) {
		$result = array('status' => '2', 'input' => 'short_text', 'reply' => 'Не более 250 символов!');
		exit (json_encode($result));
	}

	$STH = $pdo->prepare("INSERT INTO news (img,class,new_name,short_text,text,date,author,views) values (:img, :class, :name, :short_text, :text, :date, :author, :views)");
	if ($STH->execute(array( 'img' => $img, 'class' => $class, 'name' => $name, 'short_text' => $short_text, 'text' => $text, 'date' => $date, 'author' => $_SESSION['id'], 'views' => '1' )) == '1') {
		$id = get_ai($pdo, "news");
		$id--;

		if(strtotime($date) <= time()) {
			$ES = new EventsRibbon($pdo);
			$ES->new_new($id, $name);
		}

		write_sitemap($full_site_host."news/new?id=".$id);
		exit (json_encode(array('status' => '1', 'id' => $id)));
	}
}
if (isset($_POST['change_new']) and is_worthy("q")) {
	$id = checkJs($_POST['id'],"int");
	$img = checkJs($_POST['img'],null);
	$class = check($_POST['class'],"int");
	$name = check($_POST['name'],null);
	$short_text = check($_POST['short_text'],null);
	$date = check($_POST['date'],null);
	$date = date( 'Y-m-d H:i:s', strtotime($date));

	include_once '../inc/classes/HTMLPurifier/HTMLPurifier.auto.php';
	$text = $Purifier->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);

	if (empty($img)){
		$img = "files/news_imgs/none.jpg";
	}

	if (empty($class)) {
		exit (json_encode(array('status' => '2', 'input' => 'class', 'reply' => '')));
	}
	if (empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Заполните!')));
	}
	if (empty($short_text)) {
		exit (json_encode(array('status' => '2', 'input' => 'short_text', 'reply' => 'Заполните!')));
	}
	if (empty($text)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!')));
	}

	if (mb_strlen($name, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Не более 250 символов!')));
	}
	if (mb_strlen($short_text, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'short_text', 'reply' => 'Не более 250 символов!')));
	}

	$STH = $pdo->prepare("UPDATE news SET img=:img,class=:class,new_name=:name,short_text=:short_text,text=:text,date=:date WHERE id='$id' LIMIT 1");
	if ($STH->execute(array( 'img' => $img, 'class' => $class, 'name' => $name, 'short_text' => $short_text, 'text' => $text, 'date' => $date )) == '1') {
		exit (json_encode(array('status' => '1', 'id' => $id)));
	}
}
if (isset($_POST['dell_new_comment']) and is_worthy("q")) {
	$id = check($_POST['id'],"int");
	if (empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("DELETE FROM `events` WHERE `data_id`=:data_id AND `type` = '2' LIMIT 1");
	$STH->execute(array( ':data_id' => $id ));

	$pdo->exec("DELETE FROM `news__comments` WHERE `id`='$id' LIMIT 1");
	exit();
}
/* Форум
=========================================*/
if (isset($_POST['edit_topic'])) {
	$img = checkJs($_POST['img'],null);
	$id = checkJs($_POST['id'],"int");
	$name = check($_POST['name'],null);

	include_once '../inc/classes/HTMLPurifier/HTMLPurifier.auto.php';
	$text = $Purifier->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);

	if (empty($img)){
		$img = "files/forums_imgs/none.jpg";
	}
	if (empty($id)) {
		exit(json_encode(array('status' => '2')));
	}
	if (empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Заполните!')));
	}
	if (empty($text)) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Заполните!')));
	}
	if (mb_strlen($name, 'UTF-8') > 250) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Не более 250 символов!')));
	}

	$Forum = new Forum($pdo);
	if($Forum->edit_topic($id, $name, $text, $img)) {
		exit (json_encode(array('status' => '1')));
	} else {
		exit (json_encode(array('status' => '2')));
	}
}
if (isset($_POST['edit_message'])) {
	$id = checkJs($_POST['id'],"int");

	include_once '../inc/classes/HTMLPurifier/HTMLPurifier.auto.php';
	$text = $Purifier->purify($_POST['text']);
	$text = find_img_mp3($text, $id, 1);

	if (empty($text)) {
		exit (json_encode(array('status' => '3', 'input' => 'text', 'reply' => 'Заполните!')));
	}

	$Forum = new Forum($pdo);
	if($Forum->edit_answer($id, $text)) {
		exit (json_encode(array('status' => '1')));
	} else {
		exit (json_encode(array('status' => '2')));
	}
}
if (isset($_POST['load_forums_list'])) {
	$id = checkJs($_POST['id'],"int");
	if (empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	$Forum->get_forums_list($id);

	exit();
}
if (isset($_POST['edit_topic_forum']) and is_worthy("e")) {
	$id = checkJs($_POST['id'],"int");
	$forum = check($_POST['forum'],"int");

	if (empty($id) or empty($forum)){
		exit(json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	if($Forum->edit_topic_forum($id, $forum)) {
		exit (json_encode(array('status' => '1')));
	} else {
		exit (json_encode(array('status' => '2')));
	}
}
if (isset($_POST['edit_topic_status']) and is_worthy("e")) {
	$id = checkJs($_POST['id'],"int");
	$status = check($_POST['status'],"int");

	if (empty($id) or empty($status)){
		exit(json_encode(array('status' => '2')));
	}
	if ($status!=1 and $status!=2 and $status!=3 and $status!=4){
		exit(json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	if($Forum->edit_topic_status($id, $status)) {
		exit (json_encode(array('status' => '1')));
	} else {
		exit (json_encode(array('status' => '2')));
	}
}
if (isset($_POST['dell_answer']) and is_worthy("r")) {
	$id = check($_POST['id'],"int");
	if (empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	$Forum = new Forum($pdo);
	if($Forum->dell_answer($id, 1)) {
		exit(json_encode(array('status' => '1')));
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
/* Баны
=========================================*/
if (isset($_POST['dell_ban_comment']) and is_worthy("u")) {
	$id = check($_POST['id'],"int");

	if (empty($id)){
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("SELECT `bans`.`server` FROM `bans__comments` 
		LEFT JOIN `bans` ON `bans__comments`.`ban_id` = `bans`.`id` 
		WHERE `bans__comments`.`id` = :id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$row = $STH->fetch();

	if (!is_worthy_specifically("u", $row->server)) {
		exit(json_encode(array('status' => '2')));
	}

	$pdo->exec("DELETE FROM `bans__comments` WHERE `id`='$id' LIMIT 1");
	exit(json_encode(array('status' => '1')));
}
/* Удаление уведомлений
=========================================*/
if (isset($_POST['dell_event']) and is_worthy("d")) {
	$id = check($_POST['id'],"int");

	$pdo->exec("DELETE FROM `events` WHERE `id`='$id' LIMIT 1");
	exit(json_encode(array('status' => '1')));
}

exit(json_encode(array('status' => '2')));
?>