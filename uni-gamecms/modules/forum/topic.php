<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if(isset($_GET['page'])) {
	$number = $_GET['page'];
} else {
	$number = 1;
}

if(isset($_GET['id'])) {
	$id = clean($_GET['id'], "int");
} else {
	show_error_page('not_settings');
}

$STH = $pdo->query("SELECT COUNT(*) as count FROM `forums__messages` WHERE `topic` = '$id'");
$STH->setFetchMode(PDO::FETCH_ASSOC);
$row       = $STH->fetch();
$count     = $row['count'];
$limit     = 10;
$stages    = 3;
$page_name = "topic?id=".$id."&";

if($number != 'last') {
	$number = clean($number, "int");
	if(isset($number)) {
		$start = ($number - 1) * $limit;
	} else {
		$number = 1;
		$start  = 0;
	}
	$script = '';
} else {
	$number = ceil($count / $limit);
	if($number == 0) {
		$number = 1;
		$start  = 0;
	} else {
		$start = ($number - 1) * $limit;
	}
	$script = 1;
}

if(($number * $limit - $count) > $limit) {
	header('Location: ../index');
	exit();
}

$STH = $pdo->query("SELECT `forums__topics`.*,`users`.`login`,`users`.`reit`,`users`.`avatar`,`users`.`signature`,`users`.`answers` AS 'user_answers',`users`.`thanks`,`users`.`rights` FROM `forums__topics` LEFT JOIN `users` ON `forums__topics`.`author` = `users`.`id` WHERE `forums__topics`.`id` = '$id' LIMIT 1");
$STH->setFetchMode(PDO::FETCH_OBJ);
$topic = $STH->fetch();
if(empty($topic->id)) {
	show_error_page();
}

$i = 0;
if(isset($_SESSION['topics']) and $_SESSION['topics'] != '') {
	$data = explode(";", $_SESSION['topics']);
	for($j = 0; $j < count($data); $j++) {
		if($data[$j] == $id) {
			$i++;
		}
	}
}
if($i == 0) {
	$_SESSION['topics'] = $_SESSION['topics'].$id.';';
	$STH                = $pdo->prepare("UPDATE `forums__topics` SET `views`=:views WHERE `id`='$id' LIMIT 1");
	$STH->execute(array(':views' => $topic->views + 1));
}
if(isset($_SESSION['id'])) {
	$STH = $pdo->prepare("UPDATE `users` SET `last_topic`=:last_topic WHERE `id`='$_SESSION[id]' LIMIT 1");
	$STH->execute(array(':last_topic' => $topic->id));
}

$STH = $pdo->query("SELECT `forums`.`name`, `forums`.`id`, `forums__section`.`access` FROM `forums` LEFT JOIN `forums__section` ON `forums__section`.`id` = `forums`.`section_id` WHERE `forums`.`id`='$topic->forum_id'");
$STH->setFetchMode(PDO::FETCH_OBJ);
$forum = $STH->fetch();
if(empty($forum->name)) {
	show_error_page();
}

$Forum = new Forum();
if(!$Forum->have_rights($forum->access)) {
	show_error_page('not_allowed');
}
unset($Forum);

$link  = $site_host.'forum/topic?id='.$id.'&page='.$number;
$link2 = $site_host.'forum/topic?id='.$id;

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $topic->name);
$tpl->set("{name}", $conf->name);
$tpl->compile('title');
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $topic->name);
$tpl->set("{keywords}", $PI->compile_keywords($topic->name));
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
             $PI->to_nav('forum', 0, 0),
             $PI->to_nav('forum_forum', 0, $forum->id, $forum->name),
             $PI->to_nav('forum_topic', 1, 0, $topic->name),);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$STH = $pdo->query("SELECT `thanks`.*,`users`.`login` FROM `thanks` LEFT JOIN `users` ON `thanks`.`author` = `users`.`id` WHERE `thanks`.`topic` = '1' and `thanks`.`mes_id` = '$id'");
$STH->execute();
$thanks       = $STH->fetchAll();
$count_thanks = count($thanks);
$thanks_str   = '';
if($count_thanks > 0) {
	$thanks_str = '<div id="thanks_0" class="thank_str">'.$messages['Thanks'].': ';
	for($i = 0; $i < $count_thanks; $i++) {
		if(($count_thanks - $i) == 1) {
			$thanks_str .= '<a href="../profile?id='.$thanks[$i]['author'].'">'.$thanks[$i]['login'].'</a> ';
		} else {
			$thanks_str .= '<a href="../profile?id='.$thanks[$i]['author'].'">'.$thanks[$i]['login'].'</a>, ';
		}
	}
	$thanks_str .= '</div>';
} else {
	$thanks_str = '<div id="thanks_0" class="disp-n thank_str"></div>';
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/forum/topic.tpl');
$group = $users_groups[$topic->rights];
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{id}", $topic->id);
$tpl->set("{start}", $start);
$tpl->set("{limit}", $limit);
$tpl->set("{script}", $script);
$tpl->set("{link}", $link);
$tpl->set("{id2}", $forum->id);
$tpl->set("{name}", $topic->name);
$tpl->set("{date}", expand_date($topic->old_date, 7));
$tpl->set("{text}", $topic->text);
$tpl->set("{status}", $topic->status);
$tpl->set("{author_id}", $topic->author);
if(isset($_SESSION['id'])) {
	$tpl->set("{my_id}", $_SESSION['id']);
}
$tpl->set("{author_login}", $topic->login);
$tpl->set("{author_avatar}", $topic->avatar);
$tpl->set("{views}", $topic->views);
$tpl->set("{signature}", str_replace("'", "&#8216;", $topic->signature));
$tpl->set("{thanks}", $topic->thanks);
$tpl->set("{thanks_str}", $thanks_str);
$tpl->set("{answers}", $topic->user_answers);
$tpl->set("{reit}", $topic->reit);
$tpl->set("{group_name}", $group['name']);
$tpl->set("{group_color}", $group['color']);
$tpl->set("{link}", $link2);
$tpl->set("{page}", $number);
$tpl->set("{site_host}", $site_host);
$tpl->set("{pagination}", $tpl->get_paginator($number, $count, $limit, $stages, $page_name));
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
if($topic->edited_by != 0) {
	$STH = $pdo->prepare("SELECT `id`,`login` FROM `users` WHERE `id`=:edited_by LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':edited_by' => $topic->edited_by));
	$edited_by = $STH->fetch();
	$tpl->set("{edited_by_id}", $edited_by->id);
	$tpl->set("{edited_by_login}", $edited_by->login);
	$tpl->set("{edited_time}", expand_date($topic->edited_time, 7));
} else {
	$tpl->set("{edited_by_id}", "");
	$tpl->set("{edited_by_login}", "");
	$tpl->set("{edited_time}", "");
}
$tpl->compile('content');
$tpl->clear();
?>