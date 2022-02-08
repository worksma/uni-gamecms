<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

$STH = $pdo->prepare("SELECT * FROM `pages__content` WHERE `page_id`=:id LIMIT 1");
$STH->setFetchMode(PDO::FETCH_OBJ);
$STH->execute(array(':id' => $page->id));
$row = $STH->fetch();

if(empty($row->page_id)) {
	show_error_page();
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile('title');
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
$tpl->compile('content');
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array($PI->to_nav('main', 0, 0),
             $PI->to_nav($page->name, 1, 0));
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(is_auth() || $page->privacy == 1) {
	include_once "inc/authorized.php";
	$tpl->load_template("/home/page.tpl");
} else {
	include_once "inc/not_authorized.php";
	$tpl->load_template("/index/page.tpl");
}

$tpl->set("{content}", $tpl->replace_preg($row->content));
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile("content");
$tpl->clear();
?>