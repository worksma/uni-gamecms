<?php
if(!is_auth()){
	show_error_page('not_auth');
}

$STH = $pdo->prepare("UPDATE notifications SET status=:status WHERE status='0' and user_id='$_SESSION[id]'");
$STH->execute(array( 'status' => '1' ));

if(isset($_GET['page'])) {
	$number = clean($_GET['page'],"int");
}
$limit = 10;

if(isset($number)){
	$start = ($number - 1) * $limit;
}else{
	$number = 0;
	$start = 0;
}

$STH = $pdo->query("SELECT COUNT(*) as count FROM notifications WHERE user_id='$_SESSION[id]'");
$STH->setFetchMode(PDO::FETCH_ASSOC);
$row = $STH->fetch();
$count = $row['count'];
$stages = 3;
$page_name = "../notifications?";

if(($number*$limit - $count) > $limit){
	header('Location: ../index');
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
	$PI->to_nav('notifications', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$tpl->load_template('/home/notifications.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template); 
$tpl->set("{page}", $number);
$tpl->set("{start}", $start); 
$tpl->set("{limit}", $limit); 
$tpl->set("{pagination}", $tpl->get_paginator($number,$count,$limit,$stages,$page_name)); 
$tpl->compile( 'content' );
$tpl->clear();
?>