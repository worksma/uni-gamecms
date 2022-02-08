<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if(isset($_GET['page'])) {
	$number = clean($_GET['page'],"int");
} else {
	$number = 0;
}
$limit = 20;

if($number){
	$start = ($number - 1) * $limit;
} else {
	$number = 0;
	$start = 0;
}
if(isset($_GET['id'])){
	$id = clean($_GET['id'],"int");
	$STH = $pdo->query("SELECT COUNT(*) as count FROM `forums__topics` WHERE `forum_id` = '$id'");
	$number_name = "forum?id=".$id."&";
} else {
	show_error_page('not_settings');
}

$STH->setFetchMode(PDO::FETCH_ASSOC);
$row = $STH->fetch();
$count = $row['count'];
$stages = 3;

if(($number*$limit - $count) > $limit){
	header('Location: ../index');
	exit();
}

$STH = $pdo->query("SELECT `forums`.*, `forums__section`.`access` FROM `forums` LEFT JOIN `forums__section` ON `forums__section`.`id` = `forums`.`section_id` WHERE `forums`.`id`='$id'"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$row = $STH->fetch();
if(empty($row->id)){
	show_error_page();
}

$Forum = new Forum();
if(!$Forum->have_rights($row->access)) {
	show_error_page('not_allowed');
}
unset($Forum);

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $row->name);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $row->description);
$tpl->set("{keywords}", $PI->compile_keywords($row->description));
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
	$PI->to_nav('forum', 0, 0),
	$PI->to_nav('forum_forum', 1, 0, $row->name),
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$tpl->load_template('/forum/forum.tpl');
$tpl->set("{id}", $id);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template); 
$tpl->set("{token}", $token);
$tpl->set("{page}", $number);
$tpl->set("{start}", $start);
$tpl->set("{limit}", $limit);
$tpl->set("{name}", $row->name);
$tpl->set("{pagination}", $tpl->get_paginator($number,$count,$limit,$stages,$number_name));
$tpl->compile( 'content' );
$tpl->clear();
?>