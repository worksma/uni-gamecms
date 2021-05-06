<?php
if(!is_auth()){
	show_error_page('not_auth');
}

if (isset($_GET['id'])) {
	$id = clean($_GET['id'],"int");
} else {
	show_error_page('not_settings');
}

$STH = $pdo->query("SELECT * FROM tickets WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$ticket = $STH->fetch();
if(empty($ticket->id)){
	show_error_page();
}

if((($ticket->author != $_SESSION['id'])) and !is_worthy("p")){
	show_error_page('not_allowed');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $ticket->name);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $ticket->name);
$tpl->set("{keywords}", $PI->compile_keywords($ticket->name));
$tpl->set("{url}", $page->full_url);
$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>');
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
	$PI->to_nav('support_ticket', 1, 0, $ticket->name)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$STH = $pdo->query("SELECT `login`,`id` FROM `users` WHERE `id`='$ticket->author' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$author = $STH->fetch();

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/support/ticket.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{id}", $ticket->id);
$tpl->set("{name}", $ticket->name);
$tpl->set("{date}", expand_date($ticket->date,7));
$tpl->set("{text}", $ticket->text);
$tpl->set("{author_id}", $author->id);
$tpl->set("{author_login}", $author->login);
$tpl->set("{status}", $ticket->status);
$tpl->set("{files}", $ticket->files);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->compile( 'content' );
$tpl->clear();
?>