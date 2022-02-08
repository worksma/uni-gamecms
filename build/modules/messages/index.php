<?php
if(!is_auth()){
	show_error_page('not_auth');
}

if (isset($_GET['id']) and $_GET['id']!="") {
	$id = clean($_GET['id'],"int");
	$load = 'open_dialog("'.$id.'");';
} elseif (isset($_GET['create_id']) and $_GET['create_id']!="") {
	$id = clean($_GET['create_id'],"int");
	$load = 'create_dialog("'.$id.'");';
} else {
	$load = 'load_dialogs();';
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
$tpl->set("{other}", '<script src="{site_host}ajax/sound/jquery.jplayer.min.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('users', 0, 0),
	$PI->to_nav('profile', 0, $user->id, $user->login),
	$PI->to_nav('messages', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$tpl->load_template('/home/messages.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{load_dialogs}", $load);
$tpl->compile( 'content' );
$tpl->clear();
?>