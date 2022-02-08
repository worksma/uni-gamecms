<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if(isset($_GET['page'])) {
	$number = clean($_GET['page'],"int");
}

$STH = $pdo->query("SELECT news_lim FROM config__secondary LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$row = $STH->fetch();
$limit = $row->news_lim;

if(isset($number)){
	$start = ($number - 1) * $limit;
} else {
	$number = 0;
	$start = 0;
}
if(isset($_GET['class'])){
	$class = clean($_GET['class'],"int");
	$STH = $pdo->query("SELECT COUNT(*) as count FROM news WHERE class = '$class'");
	$page_name = "../news/index?class=".$class."&";
} else {
	$STH = $pdo->query("SELECT COUNT(*) as count FROM news");
	$page_name = "../news/index?";
}

$STH->setFetchMode(PDO::FETCH_ASSOC);
$row = $STH->fetch();
$count = $row['count'];
$stages = 3;

if(($number*$limit - $count) > $limit){
	header('Location: ../index');
	exit();
}

if(empty($class)){
	$class = 0;
	$classes = '<li class="active"><a href="../news/index">'.$messages['All'].'</a></li>';
} else {
	$classes = '<li><a href="../news/index">'.$messages['All'].'</a></li>';
}
$i = 0;
$STH = $pdo->query("SELECT `id`, `name` FROM `news__classes`"); $STH->setFetchMode(PDO::FETCH_OBJ);
while($row = $STH->fetch()) {
	if($row->id == $class){
		$class_name = $row->name;
		$classes .= '<li class="active"><a href="../news/index?class='.$row->id.'">'.$row->name.'</a></li>';
	} else {
		$classes .= '<li><a href="../news/index?class='.$row->id.'">'.$row->name.'</a></li>';
	}

	$i++;
}
if($i == 0){
	$classes = $messages['No_categories'];
}

if(isset($class_name)) {
	$page->title = $page->title.' - '.$class_name;
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
	$PI->to_nav('news', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$tpl->load_template('/news/index.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{page}", $number);
$tpl->set("{start}", $start);
$tpl->set("{class}", $class);
$tpl->set("{limit}", $limit);
$tpl->set("{classes}", $classes);
$tpl->set("{pagination}", $tpl->get_paginator($number,$count,$limit,$stages,$page_name));
$tpl->compile( 'content' );
$tpl->clear();
?>