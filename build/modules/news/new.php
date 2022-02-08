<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$id = clean($id,"int");
} else {
	show_error_page('not_settings');
}

$STH = $pdo->query("SELECT * FROM news WHERE id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$new = $STH->fetch();
if(empty($new->id)){
	show_error_page();
}

$i = 0;
if($_SESSION['news'] != '') {
	$data = explode(";", $_SESSION['news']);
	for ($j=0; $j < count($data); $j++) {
		if($data[$j] == $id){
			$i++;
		}
	}
}
if($i == 0) {
	$_SESSION['news'] = $_SESSION['news'].$id.';';
	$STH = $pdo->prepare("UPDATE news SET views=:views WHERE id='$id' LIMIT 1");  
	$STH->execute(array(':views' => $new->views+1));
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $new->new_name);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $PI->compile_img_str($new->img));
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $new->short_text);
$tpl->set("{keywords}", $PI->compile_keywords($new->short_text));
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
	$PI->to_nav('news', 0, 0),
	$PI->to_nav('news_new', 1, 0, $new->new_name)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$STH = $pdo->query("SELECT `name` FROM `news__classes` WHERE `id`='$new->class' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$class = $STH->fetch();

$STH = $pdo->query("SELECT `login` ,`id` FROM `users` WHERE `id`='$new->author' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$author = $STH->fetch();

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/news/new.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{id}", $new->id);
$tpl->set("{name}", $new->new_name);
$tpl->set("{date}", expand_date($new->date,2));
$tpl->set("{class_id}", $new->class);
$tpl->set("{class}", $class->name);
$tpl->set("{img}", $new->img);
$tpl->set("{text}", $new->text);
$tpl->set("{author_id}", $author->id);
$tpl->set("{author_login}", $author->login);
$tpl->set("{views}", $new->views);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template); 
$tpl->compile( 'content' );
$tpl->clear();
?>