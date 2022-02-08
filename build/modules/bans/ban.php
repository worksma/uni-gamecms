<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if (isset($_GET['id'])) {
	$id = clean($_GET['id'],"int");
} else {
	show_error_page('not_settings');
}

$STH = $pdo->query("SELECT bans.*,users.login,servers.name,servers.id AS server_id FROM bans LEFT JOIN users ON bans.author = users.id LEFT JOIN servers ON bans.server = servers.id WHERE bans.id='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$ban = $STH->fetch();
if(empty($ban->id)){
	show_error_page();
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $PI->compile_str($page->title, $ban->nick));
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $PI->compile_str($page->description, $ban->nick));
$tpl->set("{keywords}", $PI->compile_str($page->keywords, $ban->nick));
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
	$PI->to_nav('bans', 0, 0),
	$PI->to_nav('bans_ban', 1, 0, $PI->compile_str($page->title, $ban->nick))
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

if($ban->img != '0') {
	$data = explode(";", $ban->img);
	$count = count($data);
	$imgs = '<div id="imgs">';
	for ($i=0; $i < $count; $i++) {
		if(!empty($data[$i])){
			$imgs .= '<a class="thumbnail" data-lightbox="1" href="../'.$data[$i].'"><img class="thumbnail-img" src="../'.$data[$i].'"></img></a>';
		}
	}
	$imgs .= '</div>';
} else {
	$imgs = 0;
}

if($ban->status == 0){
	$status = $messages['Not_reviewed'];
	$color = "warning";
}
if($ban->status == 1){
	$status = $messages['Unbaned'];
	$color = "success";
}
if($ban->status == 2){
	$status = $messages['Do_not_unbaned'];
	$color = "danger";
}
if(empty($ban->demo)){
	$demo = 0;
} else {
	$demo = $ban->demo;
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/bans/ban.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{id}", $ban->id);
$tpl->set("{bid}", $ban->bid);
$tpl->set("{nick}", $ban->nick);
$tpl->set("{status}", $status);
$tpl->set("{color}", $color);
$tpl->set("{date}", expand_date($ban->date,1));
$tpl->set("{imgs}", $imgs);
$tpl->set("{text}", $ban->text);
$tpl->set("{demo}", $demo);
$tpl->set("{reason}", $ban->reason);
$tpl->set("{server}", $ban->name);
$tpl->set("{server_id}", $ban->server_id);
$tpl->set("{author_id}", $ban->author);
$tpl->set("{author_login}", $ban->login);
if($ban->status != 0){
	$closed = $ban->closed;
	$STH = $pdo->query("SELECT id,login,rights FROM users WHERE id='$closed'"); $STH->setFetchMode(PDO::FETCH_OBJ);  
	$row = $STH->fetch();  
	$tpl->set("{closed}", $closed);
	$tpl->set("{closed_a}", $row->login);
	$tpl->set("{closed_gp_name}", $users_groups[$row->rights]['name']);
	$tpl->set("{closed_gp_color}", $users_groups[$row->rights]['color']);
} else {
	$tpl->set("{closed}", 0);
	$tpl->set("{closed_a}", '');
	$tpl->set("{closed_gp_name}", '');
	$tpl->set("{closed_gp_color}", '');
}
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template); 
$tpl->compile( 'content' );
$tpl->clear();
?>