<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
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
	$PI->to_nav('store', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$servers = '';
$STH = $pdo->query("SELECT 
								    id, 
								    name 
								FROM 
								    servers 
								WHERE 
								    type != '0' 
								  AND united = '0' 
								  AND EXISTS (SELECT 1 FROM services WHERE services.server = servers.id AND services.sale = 1) 
								ORDER BY trim");
$STH->setFetchMode(PDO::FETCH_OBJ);
while($row = $STH->fetch()) { 
	$servers .= '<option value="'.$row->id.'">'.$row->name.'</option>';
}
if($servers == '') {
	$servers = '<option value="0">Серверов нет</option>';
}

$STH = $pdo->query("SELECT discount FROM config__prices LIMIT 1");
$STH->setFetchMode(PDO::FETCH_OBJ);
$disc = $STH->fetch();

if(empty($user->nick) or $user->nick == '---') {
	$nick = '';
} else {
	$nick = $user->nick;
}
if(empty($user->steam_id) or $user->steam_id == '---') {
	$steam_id = '';
} else {
	$steam_id = $user->steam_id;
}

$tpl->load_template('/home/store.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{discount}", $disc->discount);
$tpl->set("{nick}", $nick);
$tpl->set("{steam_id}", $steam_id);
$tpl->set("{servers}", $servers);
$tpl->compile( 'content' );
$tpl->clear();
?>