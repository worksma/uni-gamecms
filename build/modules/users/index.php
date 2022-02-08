<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if(isset($_GET['page'])) {
	$number = clean($_GET['page'], "int");
}
if(isset($_GET['group']) && $_GET['group'] != '0') {
	if($_GET['group'] === 'multi_accounts') {
		if(is_worthy("f") || is_worthy("g")) {
			$group = 'multi_accounts';
		} else {
			$group = 0;
		}
	} else {
		$group = clean($_GET['group'], "int");
	}
} else {
	$group = 0;
}

$STH = $pdo->query("SELECT users_lim FROM config__secondary LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$row = $STH->fetch();
$limit = $row->users_lim;

if(isset($number)){
	$start = ($number - 1) * $limit;
} else {
	$number = 0;
	$start = 0;
}
if($group === 0) {
	$STH = $pdo->query("SELECT COUNT(*) as count FROM users");
} else {
	if($group === 'multi_accounts') {
		$STH = $pdo->query("SELECT COUNT(*) as count FROM users WHERE multi_account != '0' AND active='1'");
	} else {
		$STH = $pdo->query("SELECT COUNT(*) as count FROM users WHERE rights = '$group' AND active='1'");
	}
}
$count = $STH->fetchColumn();
$stages = 3;
$page_name = "../users?group=".$group."&";

if(($number*$limit - $count) > $limit){
	header('Location: ../users');
	exit();
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $page->description);
$tpl->set("{keywords}", $page->keywords);
$tpl->set("{url}", $page->full_url);
$tpl->set("{other}", '');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('users', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$groups = "<option value='0'>".$messages['All']."</option>";
$STH = $pdo->query("SELECT `id`, `name` FROM `users__groups`"); $STH->setFetchMode(PDO::FETCH_OBJ);
while($row = $STH->fetch()) { 
	if($group == $row->id) {
		$groups .= "<option value='".$row->id."' selected>".$row->name."</option>";
	} else {
		$groups .= "<option value='".$row->id."'>".$row->name."</option>";
	}
}
if(is_worthy("f") || is_worthy("g")) {
	if($group === 'multi_accounts') {
		$groups .= "<option value='multi_accounts' selected>Мульти-аккаунты</option>";
	} else {
		$groups .= "<option value='multi_accounts'>Мульти-аккаунты</option>";
	}	
}

$tpl->load_template('/home/users.tpl');
$tpl->set("{pagination}", $tpl->get_paginator($number,$count,$limit,$stages,$page_name));
$tpl->set("{token}", $token);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
if($number == 0) {
	$number = 1;
}
$tpl->set("{page}", $number);
$tpl->set("{start}", $start);
$tpl->set("{limit}", $limit);
$tpl->set("{group}", $group);
$tpl->set("{groups}", $groups);
$tpl->compile( 'content' );
$tpl->clear();
?>