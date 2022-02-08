<?php
if(!is_auth()){
	show_error_page('not_auth');
}

if(!is_worthy("b")) {
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
$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>
<link rel="stylesheet" type="text/css" href="{site_host}templates/admin/css/timepicker.css" />');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('news', 0, 0),
	$PI->to_nav('news_add_new', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$STH = $pdo->query("SELECT `id`, `name` from `news__classes`");
$STH->execute();
$row2 = $STH->fetchAll();
$count_row2 = count($row2);
$classes = ''; 
for($i_row2 = 0; $i_row2 < $count_row2; $i_row2++){
	$classes .= '<option value="'.$row2[$i_row2]['id'].'">'.$row2[$i_row2]['name'].'</option>';
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/news/add_new.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{token}", $token);
$tpl->set("{classes}", $classes);
$tpl->set("{date}", date( 'd.m.Y H:i'));
$tpl->compile( 'content' );
$tpl->clear();
?>