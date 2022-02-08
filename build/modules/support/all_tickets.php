<?php
if(!is_auth()){
	show_error_page('not_auth');
}
if(!is_worthy("p")) {
	show_error_page('not_allowed');
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
$tpl->set("{other}", '<link rel="stylesheet" href="{site_host}templates/{template}/css/tabs.css">');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('support', 0, 0),
	$PI->to_nav('support_all_tickets', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$STH = $pdo->query("SELECT id FROM tickets WHERE status='1'");
$STH->execute();
$row = $STH->fetchAll();
$count1 = count($row);

$STH = $pdo->query("SELECT id FROM tickets WHERE status='2'");
$STH->execute();
$row = $STH->fetchAll();
$count2 = count($row);

$tpl->load_template('/support/all_tickets.tpl');
$tpl->set("{count1}", $count1);
$tpl->set("{count2}", $count2);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->compile( 'content' );
$tpl->clear();
?>