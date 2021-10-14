<?php
if(!is_auth()){
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
$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = $tpl->get_nav(
	[
		$PI->to_nav('main', 0, 0),
		$PI->to_nav('complaints', 0, 0),
		$PI->to_nav('complaints_add', 1, 0)
	],
	'elements/nav_li.tpl'
);

include_once "inc/authorized.php";

$STH = $pdo->query("SELECT `id`, `name`, `type` from `servers` order by `trim`");
$STH->execute();
$row = $STH->fetchAll();
$count = count($row);

$servers = '';
for($i = 0; $i < $count; $i++){
	$servers .= '<option value="'.$row[$i]['id'].'">'.$row[$i]['name'].'</option>';
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/complaints/add.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{servers}", $servers);
$tpl->compile( 'content' );
$tpl->clear();
?>