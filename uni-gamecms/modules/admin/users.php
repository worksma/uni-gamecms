<?php
if(!is_admin()){
	show_error_page('not_adm');
}

if(isset($_GET['exportUsersXlsx'])) {
	$users = $pdo->query(
		"SELECT 
					    id, 
					    login, 
					    email, 
					    regdate, 
    	   				CASE
					        WHEN name = '---' THEN ''
					        ELSE name
					    END AS name,
					    CASE
					        WHEN steam_api = '0' THEN ''
					        ELSE concat('https://steamcommunity.com/profiles/', steam_api) 
					    END AS steam_api,
    					CASE
					        WHEN vk_api = 0 THEN ''
					        ELSE concat('https://vk.com/id', vk_api)
					    END AS vk_api,
    					CASE
					        WHEN telegram = '' THEN ''
					        ELSE concat('tg://resolve?domain=', telegram)
					    END AS telegram
					FROM 
					    users"
	)->fetchAll(PDO::FETCH_ASSOC);

	array_unshift($users, ['ID', 'Логин', 'E-mail', 'Дата регистрации', 'Имя', 'Профиль STEAM', 'Профиль VK', 'Профиль Telegram']);

	$xlsx = SimpleXLSXGen::fromArray($users);
	$xlsx->downloadAs('users.xlsx');
	die;
}

if(isset($_GET['page'])) {
	$number = clean($_GET['page'], "int");
}
if(isset($_GET['group']) && $_GET['group'] != '0') {
	if($_GET['group'] === 'multi_accounts') {
		$group = 'multi_accounts';
	} else {
		$group = clean($_GET['group'], "int");
	}
} else {
	$group = 0;
}
$limit = 30;

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
		$STH = $pdo->query("SELECT COUNT(*) as count FROM users WHERE multi_account != '0'");
	} else {
		$STH = $pdo->query("SELECT COUNT(*) as count FROM users WHERE rights = '$group'");
	}
}
$count = $STH->fetchColumn();
$stages = 3;
$page_name = "users?group=".$group."&";

if(($number*$limit - $count) > $limit){
	header('Location: ../admin/users');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{image}", $page->image);
$tpl->set("{other}", '');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('top.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{site_name}", $conf->name);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('menu.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$nav = array(
	$PI->to_nav('admin', 0, 0),
	$PI->to_nav('admin_users', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$groups = "<option value='0'>Все</option>";
$STH = $pdo->query("SELECT `id`, `name` FROM `users__groups`"); $STH->setFetchMode(PDO::FETCH_OBJ);
while($row = $STH->fetch()) { 
	if($group == $row->id) {
		$groups .= "<option value='".$row->id."' selected>".$row->name."</option>";
	} else {
		$groups .= "<option value='".$row->id."'>".$row->name."</option>";
	}
}
if($group === 'multi_accounts') {
	$groups .= "<option value='multi_accounts' selected>Мульти-аккаунты</option>";
} else {
	$groups .= "<option value='multi_accounts'>Мульти-аккаунты</option>";
}

$tpl->load_template('users.tpl');
$tpl->set("{pagination}", $tpl->get_paginator($number,$count,$limit,$stages,$page_name));
$tpl->set("{token}", $token);
$tpl->set("{site_host}", $site_host);
if($number == 0) {
	$number = 1;
}
$tpl->set("{page}", $number);
$tpl->set("{start}", $start); 
$tpl->set("{group}", $group);
$tpl->set("{groups}", $groups);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>