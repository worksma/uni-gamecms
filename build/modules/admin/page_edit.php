<?php
if(!is_admin()){
	show_error_page('not_adm');
}

if ($_GET['id']!="") {
	$id = clean($_GET['id'],"int");
} else {
	show_error_page('not_settings');
}

$STH = $pdo->query("SELECT * FROM `pages` WHERE `id`='$id'"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$page_info = $STH->fetch();
if(empty($page_info->id)){
	show_error_page();
}
$STH = $pdo->query("SELECT * FROM `pages__content` WHERE `page_id`='$id'"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$page_content = $STH->fetch();
if(empty($page_content->id)){
	show_error_page();
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
	$PI->to_nav('admin_page_editor', 0, 0),
	$PI->to_nav('admin_page_edit', 1, 0)
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
	if($page_info->class == $row[$i]['id']) {
		$classes .= '<option value="'.$row[$i]['id'].'" selected>'.$row[$i]['name'].'</option>';
	} else {
		$classes .= '<option value="'.$row[$i]['id'].'">'.$row[$i]['name'].'</option>';
	}
}

$url_ = explode("/", $page_info->url);

$tpl->load_template('page_edit.tpl');
$tpl->set("{token}", $token);
$tpl->set("{classes}", $classes);
$tpl->set("{id}", $page_info->id);
$tpl->set("{class}", $page_info->class);
$tpl->set("{url}", $url_[count($url_)-1]);
$tpl->set("{title}", $page_info->title);
$tpl->set("{description}", $page_info->description);
$tpl->set("{keywords}", $page_info->keywords);
$tpl->set("{image}", $page_info->image);
$tpl->set("{robots}", $page_info->robots);
$tpl->set("{privacy}", $page_info->privacy);
$tpl->set("{active}", $page_info->active);
$tpl->set("{content}", '<?php echo $page_content->content; ?>');
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->compile( 'content' );
$tpl->clear();
?>