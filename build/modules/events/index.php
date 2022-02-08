<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

$ES = new EventsRibbon;

if(isset($_GET['page'])) {
	$number = clean($_GET['page'],"int");
}
$limit = 20;

if(isset($number)){
	$start = ($number - 1) * $limit;
} else {
	$number = 0;
	$start = 0;
}
if(isset($_GET['class']) && $_GET['class'] != 0){
	$class = clean($_GET['class'],"int");
	$STH = $pdo->query("SELECT 
    								COUNT(*) as count 
								FROM 
								    `events`  
									    LEFT JOIN 
									        `users` 
									            ON 
									                `events`.`author` = `users`.`id` 
										LEFT JOIN 
									        users__groups 
									            ON 
									                users.rights = users__groups.id 
								WHERE 
									users__groups.name IS NOT NULL 
								  		AND `type` = '$class'");
	$page_name = "../events?class=".$class."&";
	$class_name = $ES->get_category_name($class);
} else {
	$STH = $pdo->query("SELECT 
    								COUNT(*) as count 
								FROM 
								    `events`  
									    LEFT JOIN 
									        `users` 
									            ON 
									                `events`.`author` = `users`.`id` 
										LEFT JOIN 
									        users__groups 
									            ON 
									                users.rights = users__groups.id 
								WHERE 
									users__groups.name IS NOT NULL");
	$page_name = "../events?";
	$class_name = null;
	$class = 0;
}
$STH->setFetchMode(PDO::FETCH_ASSOC);
$row = $STH->fetch();
$count = $row['count'];
$stages = 3;

if(($number*$limit - $count) > $limit){
	header('Location: ../events');
	exit();
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
	$PI->to_nav('events', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$tpl->load_template('/home/events.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{categories}", $ES->get_categories($class));
$tpl->set("{page}", $number);
$tpl->set("{start}", $start);
$tpl->set("{class}", $class);
$tpl->set("{limit}", $limit);
$tpl->set("{pagination}", $tpl->get_paginator($number,$count,$limit,$stages,$page_name));
$tpl->compile( 'content' );
$tpl->clear();
?>