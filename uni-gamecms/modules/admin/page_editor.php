<?php
if(!is_admin()){
	show_error_page('not_adm');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{image}", $page->image);
$tpl->set("{other}", '<script src="{site_host}modules/editors/ckeditor/ckeditor.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('top.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{site_name}", $conf->name);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('menu.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$nav = array(
	$PI->to_nav('admin', 0, 0),
	$PI->to_nav('admin_page_editor', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$classes = '';
$STH = $pdo->query("SELECT `id`, `name` from `pages__classes`");
$STH->execute();
$row = $STH->fetchAll();
$count = count($row);

$classes = ''; 
for($i = 0; $i < $count; $i++){
	if($row[$i]['name'] == '') {
		$row[$i]['name'] = $messages['Initial'];
	}
	$classes .= '<option value="'.$row[$i]['id'].'">'.$row[$i]['name'].'</option>';
}

$tpl->load_template('page_editor.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{token}", $token);
$tpl->set("{classes}", $classes);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>