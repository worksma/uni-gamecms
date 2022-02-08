<?php
if(!is_auth()){
	show_error_page('not_auth');
}

if(!is_worthy("q")) {
	show_error_page('not_allowed');
}

if (isset($_GET['id'])) {
	$id = clean($_GET['id'],"int");
} else {
	show_error_page('not_settings');
}

$STH = $pdo->query("SELECT * FROM news WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$new = $STH->fetch();
if(empty($new->id)){
	show_error_page();
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
	$PI->to_nav('news_new', 0, $new->id, $new->new_name),
	$PI->to_nav('news_change_new', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$STH = $pdo->query("SELECT id,name from news__classes");
$STH->execute();
$row2 = $STH->fetchAll();
$count_row2 = count($row2);

$classes = ''; 
for($i_row2 = 0; $i_row2 < $count_row2; $i_row2++){
	if($row2[$i_row2]['id'] == $new->class){
		$classes .= '<option value="'.$row2[$i_row2]['id'].'" selected>'.$row2[$i_row2]['name'].'</option>';
	} else {
		$classes .= '<option value="'.$row2[$i_row2]['id'].'">'.$row2[$i_row2]['name'].'</option>';
	}
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/news/change_new.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{token}", $token);
$tpl->set("{classes}", $classes);
$tpl->set("{id}", $new->id);
$tpl->set("{name}", $new->new_name);
$tpl->set("{img}", $new->img);
$tpl->set("{text}", $new->text);
$tpl->set("{short_text}", $new->short_text);
$tpl->set("{date}", date( 'd.m.Y H:i', strtotime($new->date)));
$tpl->compile( 'content' );
$tpl->clear();
?>