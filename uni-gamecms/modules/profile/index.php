<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if(array_key_exists('id', $_GET)) {
	$id = clean($_GET['id'], "int");
} elseif(is_auth()) {
	$id = $_SESSION['id'];
	header("Location: ../profile?id=$id");
	exit();
} else {
	show_error_page('not_settings');
}

if(!$profile = Users::getUserData($pdo, $id)) {
	show_error_page();
}

if(!empty($profile->last_topic)) {
	$STH = $pdo->prepare("SELECT `name` FROM `forums__topics` WHERE `id`=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $profile->last_topic));
	$row = $STH->fetch();
	if(isset($row->name)) {
		$profile->topic_name = $row->name;
	} else {
		$profile->topic_name = '';
		$profile->last_topic = 0;
	}
} else {
	$profile->last_topic = 0;
	$profile->topic_name = '';
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $PI->compile_str($page->title, $profile->login));
$tpl->set("{name}", $conf->name);
$tpl->compile('title');
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $PI->compile_img_str($profile->avatar));
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $PI->compile_str($page->description, $profile->login));
$tpl->set("{keywords}", $PI->compile_str($page->keywords, $profile->login));
$tpl->set("{url}", $page->full_url);
$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile('content');
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array($PI->to_nav('main', 0, 0),
			 $PI->to_nav('users', 0, 0),
			 $PI->to_nav('profile', 1, 0, $profile->login));
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(is_auth()) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

if(is_auth()) {
	$STH = $pdo->query("SELECT id FROM users__friends WHERE ((id_sender = '$id' AND id_taker = '$_SESSION[id]') OR (id_sender = '$_SESSION[id]' AND id_taker = '$id')) AND accept = '1' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		if($_SESSION['id'] != $profile->id) {
			$checker = '1';
		} else {
			$checker = '2';
		}
	} else {
		$checker = '2';
	}
} else {
	$checker = '1';
}

$STH = $pdo->query("SELECT id FROM users__online WHERE user_id='$id' LIMIT 1");
$STH->setFetchMode(PDO::FETCH_OBJ);
$row = $STH->fetch();
if(isset($row->id)) {
	$last_activity = $messages['Online'];
} else {
	if($profile->last_activity == '0000-00-00 00:00:00') {
		$last_activity = $messages['Was_online'].$messages['Bc'];
	} else {
		$last_activity = expand_date($profile->last_activity, 7);
		$last_activity = $messages['Was_online'].$last_activity;
	}
}

$tpl->result['friends'] = '';

$STH = $pdo->query("SELECT users__friends.id_taker, users.id, users.login, users.avatar, users.rights FROM users__friends LEFT JOIN users on users__friends.id_taker = users.id WHERE (users__friends.id_sender='$profile->id') AND users__friends.accept='1' UNION SELECT users__friends.id_sender, users.id, users.login, users.avatar, users.rights FROM users__friends LEFT JOIN users ON users__friends.id_sender = users.id WHERE (users__friends.id_taker='$profile->id') AND users__friends.accept='1'");
$STH->setFetchMode(PDO::FETCH_OBJ);
while($row = $STH->fetch()) {
	$friend_group = $users_groups[$row->rights];
	$tpl->load_template('elements/mini_friend.tpl');
	$tpl->set("{id}", $row->id);
	$tpl->set("{avatar}", $row->avatar);
	$tpl->set("{login}", $row->login);
	$tpl->set("{gp_color}", $friend_group['color']);
	$tpl->set("{gp_name}", $friend_group['name']);
	$tpl->compile('friends');
	$tpl->clear();
}
if($tpl->result['friends'] == '') {
	$tpl->result['friends'] = '<span class="empty-element">'.$messages['There_are_no_friends'].'</span>';
}

if(empty($profile->fb)) {
	$profile->fb = 0;
}
if(empty($profile->fb_api)) {
	$profile->fb_api = 0;
}

$isFriend = 'false';
$issetFriendRequestFromMe = 'false';
$issetFriendRequestFromHim = 'false';

if(is_auth()) {
	$STH = $pdo->prepare("SELECT id, id_sender, id_taker, accept FROM users__friends WHERE (id_sender=:friend_id AND id_taker=:my_id) OR (id_sender=:my_id AND id_taker=:friend_id) LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':my_id' => $_SESSION['id'], ':friend_id' => $profile->id]);
	$row = $STH->fetch();
}

if(isset($row->id) && ($row->accept == 1)) {
	$isFriend = 'true';
}
if(isset($row->id) && ($row->accept == 0)) {
	if($row->id_sender == $_SESSION['id']) {
		$issetFriendRequestFromMe = 'true';
	}
	if($row->id_taker == $_SESSION['id']) {
		$issetFriendRequestFromHim = 'true';
	}
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/home/profile.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{site_host}", $site_host);
$tpl->set("{last_activity}", $last_activity);
$tpl->set("{template}", $conf->template);
$tpl->set("{profile_id}", $profile->id);
$tpl->set("{login}", $profile->login);
$tpl->set("{avatar}", $profile->avatar);
$tpl->set("{group}", $users_groups[$profile->rights]['name']);
$tpl->set("{group_color}", $users_groups[$profile->rights]['color']);
$tpl->set("{regdate}", expand_date($profile->regdate, 1));
$tpl->set("{name}", $profile->name);
$tpl->set("{answers}", $profile->answers);
$tpl->set("{thanks}", $profile->thanks);
$tpl->set("{telegram}", $profile->telegram);
$tpl->set("{nick}", $profile->nick);
$tpl->set("{skype}", $profile->skype);
$tpl->set("{discord}", $profile->discord);
$tpl->set("{reit}", $profile->reit);
$tpl->set("{topic_id}", $profile->last_topic);
$tpl->set("{topic_name}", $profile->topic_name);
$tpl->set("{checker}", $checker);
$tpl->set("{vk}", $profile->vk);
$tpl->set("{vk_api}", $profile->vk_api);
$tpl->set("{fb}", $profile->fb);
$tpl->set("{fb_api}", $profile->fb_api);
$tpl->set("{steam_api}", $profile->steam_api);
$tpl->set("{steam_id}", $profile->steam_id);
$tpl->set("{birth}", expand_date($profile->birth, 2));
$tpl->set("{dell}", $profile->dell);
$tpl->set("{friends}", $tpl->result['friends']);
$tpl->set("{isFriend}", $isFriend);
$tpl->set("{issetFriendRequestFromMe}", $issetFriendRequestFromMe);
$tpl->set("{issetFriendRequestFromHim}", $issetFriendRequestFromHim);
$tpl->set("{shilings}", $profile->shilings);
$tpl->set("{proc}", $profile->proc);
$tpl->compile('content');
$tpl->clear();
?>